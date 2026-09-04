#!/usr/bin/env bash
#
# GitHub Issue 一括発行スクリプト（issue-creation-procedure.md の Phase0〜9 / 全48件）
#
# 使い方:
#   chmod +x create-issues.sh
#   DRY_RUN=1 ./create-issues.sh          # 実行せず内容だけ確認（推奨: まずこれ）
#   ./create-issues.sh                     # 実際に48件発行
#   START=25 ./create-issues.sh            # 途中で失敗した場合、25件目から再開
#   ./create-issues.sh owner/other-repo    # リポジトリを差し替える場合
#
# 事前条件:
#   gh auth status が Logged in であること
#
set -euo pipefail

REPO="${1:-hukuitappei/contact-form-project}"
START="${START:-1}"
DRY_RUN="${DRY_RUN:-0}"
TOTAL=48

count=0

create() {
  count=$((count + 1))

  if [ "$count" -lt "$START" ]; then
    printf '[%02d/%d] skip: %s\n' "$count" "$TOTAL" "$1"
    return 0
  fi

  printf '[%02d/%d] %s\n' "$count" "$TOTAL" "$1"

  if [ "$DRY_RUN" = "1" ]; then
    printf '        body: %s\n' "$2"
    return 0
  fi

  gh issue create -R "$REPO" --title "$1" --body "$2"
  sleep 1  # GitHub の secondary rate limit 回避
}

echo "target repo: $REPO"
[ "$DRY_RUN" = "1" ] && echo "*** DRY RUN (Issueは作成されません) ***"
echo

# ---------------------------------------------------------------- Phase 0: 基盤構築
create '[Phase0-1] Laravel+Sail環境構築' \
  'Docker明示バージョン指定でLaravel10.xプロジェクトを作成し、Sailを導入する。.envのDB接続情報（DB_HOST=mysql等）を設定し、artisan key:generateを実行する。日本語化はFormRequestのmessages()とlang/ja（認証系）で行い、laravel-lang/*系の外部翻訳パッケージは導入しない（サプライチェーン攻撃でマルウェア配布に悪用された経緯があるため）。'

create '[Phase0-2] フロントエンド環境構築' \
  'Vite / Tailwind CSS(^3.4.0) / Alpine.jsを導入する。提供Bladeリポジトリ（Preparedblade-ConfirmationTest-ContactForm、基本機能ブランチ）のresourcesディレクトリを丸ごと差し替える。'

create '[Phase0-3] phpMyAdmin追加・Sail起動確認' \
  'compose.yamlにphpmyadminサービスを追加し、sail up -dで全サービスが正常起動することを確認する。'

create '[Phase0-4] カバレッジ計測環境確認' \
  'sail artisan test --coverageが動作する状態か検証する。動かない場合はPCOV/Xdebugの導入・SAIL_XDEBUG_MODE等の設定を追加する。'

create '[Phase0-5] DBマイグレーション作成' \
  'categories/tags/contacts/contact_tagのマイグレーションを作成する。FK制約（ON DELETE CASCADE）、contact_tagのUNIQUE(contact_id, tag_id)、tags.nameのUNIQUEを含める。テーブル仕様書シートと1カラムずつ一致させる。'

create '[Phase0-6] ER図作成・README雛形作成' \
  '設計したテーブル構造のER図（Mermaidまたは画像）を作成する。README.mdの雛形（プロジェクト名・概要・使用技術・作成者などの見出し）を用意する。'

create '[Phase0-7] UserSeeder/CategorySeeder/TagSeeder実装' \
  'UserSeeder（test@example.com / password、Hash::make使用）、CategorySeeder（固定5件）、TagSeeder（固定5件）を実装する。'

create '[Phase0-8] ContactSeeder実装' \
  'Faker(ja_JP)で20件のダミーお問い合わせデータを投入する。カテゴリはランダム選択、各Contactにタグ1〜3件をattachする。'

create '[Phase0-9] DatabaseSeeder統合・投入確認' \
  'DatabaseSeederのrun()で全Seederを順番に呼び出す。sail artisan migrate:fresh --seedで一連の投入が成功することを確認する。'

# ---------------------------------------------------------------- Phase 1: モデル
create '[Phase1-10] Category/Tag/Contactモデル・リレーション定義' \
  'Category::contacts()（hasMany）、Contact::category()（belongsTo）、Contact::tags()（belongsToMany）、Tag::contacts()（belongsToMany）を実装する。'

create '[Phase1-11] モデル単体テスト' \
  '各リレーションが正しく取得できることを検証する単体テストを実装する。'

# ---------------------------------------------------------------- Phase 2: 公開フォーム画面
create '[Phase2-12] StoreContactRequest実装' \
  'お問い合わせ入力のバリデーションルールと日本語エラーメッセージ(a〜j)を実装する。first_name=姓／last_name=名の対応に注意する。'

create '[Phase2-13] ContactController@index実装' \
  '入力ページを表示する。Category::all()/Tag::all()をBladeへ渡す。「修正」ボタンからのクエリパラメータによる入力復元に対応する。'

create '[Phase2-14] ContactController@confirm実装' \
  'POST /contacts/confirmでStoreContactRequestによるバリデーション後、確認ページを表示する。カテゴリ名・タグ名を文字列で表示する。'

create '[Phase2-15] ContactController@store実装' \
  'POST /contactsでcontactsテーブルへ保存し、タグをcontact_tagへattachする。/thanksへリダイレクトする。'

create '[Phase2-16] ContactController@thanks実装' \
  'GET /thanksでサンクスページ（送信完了メッセージ＋HOMEリンク）を表示する。'

create '[Phase2-17] 公開フォームFeatureテスト（表示系）' \
  '入力ページ・サンクスページが正常に表示され、categories/tagsがビュー変数として渡されることを検証する。'

create '[Phase2-18] 公開フォームFeatureテスト（送信・エラー系）' \
  '確認ページ遷移、送信によるレコード保存とタグ紐付け、バリデーションエラー時の挙動を検証する。'

# ---------------------------------------------------------------- Phase 3: 管理者認証
create '[Phase3-19] Fortify導入・設定' \
  'Fortifyを導入し、登録・ログインのビューを紐付ける。ログインのレート制限5回/分を設定する。'

create '[Phase3-20] 管理者登録バリデーション調整' \
  'CreateNewUserアクションで日本語エラーメッセージ(a〜f)を実装する。'

create '[Phase3-21] 認証Featureテスト' \
  '登録・ログイン・ログアウト・レート制限の挙動を検証するFeatureテストを実装する。'

# ---------------------------------------------------------------- Phase 4: 管理画面・お問い合わせ管理
create '[Phase4-22] IndexContactRequest実装' \
  '管理画面検索用バリデーション（keyword/gender:in:0,1,2,3/category_id/date）を実装する。'

create '[Phase4-23] AdminController@index実装' \
  '検索・7件ページネーション・リセットを実装する。未認証時は/loginへリダイレクトする認証ガードを設定する。'

create '[Phase4-24] AdminController@show実装' \
  'お問い合わせ詳細ページを表示する。性別・カテゴリ・タグを文字列表示する。'

create '[Phase4-25] AdminController@destroy実装' \
  'お問い合わせを削除し、/adminへリダイレクトする。'

create '[Phase4-26] 管理画面一覧・詳細Featureテスト' \
  '検索・ページネーション・詳細表示・削除・認証ガードを検証するFeatureテストを実装する。'

# ---------------------------------------------------------------- Phase 5: タグ管理
create '[Phase5-27] StoreTagRequest/UpdateTagRequest実装' \
  'タグ名の必須・50字以内・ユニーク制約（更新時は自身の現在名を許可）を実装する。'

create '[Phase5-28] TagController@store実装' \
  'POST /admin/tagsでタグを新規作成し、/adminへリダイレクトする。'

create '[Phase5-29] TagController@edit/@update実装' \
  'タグ編集ページの表示（GET /admin/tags/{tag}/edit）と更新（PUT /admin/tags/{tag}）を実装する。'

create '[Phase5-30] TagController@destroy実装' \
  'タグを削除する。contact_tagの関連レコードがカスケード削除されることを確認する。'

create '[Phase5-31] タグ単体テスト' \
  'タグ名の重複チェック・文字数制限のバリデーションを検証する単体テストを実装する。'

create '[Phase5-32] タグCRUD Featureテスト' \
  'タグの作成・編集・削除と、未認証ユーザーが操作できないことを検証するFeatureテストを実装する。'

# ---------------------------------------------------------------- Phase 6: 基本要件仕上げ
create '[Phase6-33] Pint整形・コード品質レビュー' \
  'sail bin pintで整形し、sail bin pint --testで No fixable issues were found を確認する。命名規則・コントローラー責務を自己レビューする。'

create '[Phase6-34] README最終化' \
  '概要・ER図・環境構築手順・使用技術・APIエンドポイント一覧・開発環境URL・作成者をREADME.mdに記載する。'

# ---------------------------------------------------------------- Phase 7: CSVエクスポート（応用）
create '[Phase7-35] ExportContactRequest実装' \
  'CSVエクスポートの検索条件バリデーション（keyword/gender:in:0,1,2,3/category_id/date）を実装する。'

create '[Phase7-36] ContactController@export実装' \
  'GET /contacts/exportでBOM付きCSVを生成する。列順はID/氏名/性別/メール/電話/住所/建物/カテゴリ/内容/作成日時。未指定時は新着順。authミドルウェアで保護する。'

create '[Phase7-37] エクスポート単体/機能テスト' \
  'バリデーション（不正な性別・存在しないカテゴリIDの拒否）と、ログイン済み管理者のみDL可能であることを検証する。'

# ---------------------------------------------------------------- Phase 8: 公開API v1（応用）
create '[Phase8-38] APIリソース実装' \
  'ContactResource/CategoryResource/TagResourceを実装する。'

create '[Phase8-39] 一覧API実装' \
  'GET /api/v1/contactsを実装する。Api\V1\IndexContactRequestでgender:in:1,2,3、per_page可変（デフォルト20、最大100）に対応する。'

create '[Phase8-40] 詳細API実装' \
  'GET /api/v1/contacts/{contact}を実装する。存在しないIDの場合はHandler.phpでカスタム404を返却する。'

create '[Phase8-41] Api\V1\Store/UpdateContactRequest実装' \
  'API用の作成・更新バリデーション（Web版のa〜jとは別建てのg〜jエラーメッセージ: g=電話番号形式不正/h=性別値不正/i=カテゴリ不存在/j=タグ不存在）を実装する。'

create '[Phase8-42] 作成API実装' \
  'POST /api/v1/contactsで201 Createdを返却する。tag_idsの紐付けを行う。'

create '[Phase8-43] 更新API実装' \
  'PUT /api/v1/contacts/{contact}で200 OKを返却する。tag_ids()->sync()でタグを同期する。'

create '[Phase8-44] 削除API実装' \
  'DELETE /api/v1/contacts/{contact}で204 No Contentを返却する。'

create '[Phase8-45] API単体テスト' \
  '検索・作成・更新バリデーションの単体テストを実装する。'

create '[Phase8-46] API機能テスト' \
  '一覧/詳細/作成/更新/削除の各エンドポイントのFeatureテストを実装する。'

# ---------------------------------------------------------------- Phase 9: 最終確認
create '[Phase9-47] 全体リグレッション・カバレッジ最終確認' \
  'sail artisan testで全テストPASSを確認する。sail artisan test --coverageでカバレッジ70%超を確認する。'

create '[Phase9-48] README最終更新・提出準備最終チェック' \
  'README全項目の最終確認、要件シート各項目との突き合わせ、提出準備を行う。'

echo
echo "done: $count 件を処理しました（DRY_RUN=$DRY_RUN）"
