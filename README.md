# お問い合わせフォーム（確認テスト）

COACHTECH確認テスト課題「新お問い合わせフォーム」の実装リポジトリです。公開のお問い合わせフォーム（入力・確認・送信）と、認証必須の管理画面（一覧・検索・詳細・CSVエクスポート・タグ管理）、および公開API（v1）を実装しています。

## 実装済み機能

- **公開お問い合わせフォーム**: 入力 → 確認 → 送信 → サンクスページ表示
- **管理者認証**: Fortifyによる新規登録・ログイン・ログアウト、ログイン試行のレート制限
- **管理画面**: お問い合わせ一覧（キーワード・性別・カテゴリ・日付での絞り込み、ページネーション）、詳細表示、削除
- **タグ管理**: 登録・編集・削除、重複名バリデーション
- **CSVエクスポート**: 管理画面の検索条件に応じたBOM付きCSVダウンロード
- **公開API（v1）**: お問い合わせの一覧・詳細取得・作成・更新・削除（詳細は下記「APIエンドポイント一覧」を参照）

## 技術スタック

| 項目 | バージョン |
| --- | --- |
| PHP | 8.2 |
| Laravel | 10.x |
| MySQL | 8.0 |
| フロントエンド | Tailwind CSS 3.4 / Alpine.js / Vite |
| Webサーバー | Nginx |
| 開発環境 | Laravel Sail (Docker) |
| テスト | PHPUnit / Pest（カバレッジ: PCOV） |

## 環境構築

前提: Docker Desktop（WSL2バックエンド）を起動しておいてください。

```bash
# .env 準備
cp .env.example .env

# 依存関係インストール（初回のみ・vendor/が無い状態で実行）
# ローカルにcomposerが無くてもDockerコンテナ経由でインストールできます
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php83-composer:latest composer install --ignore-platform-reqs

# コンテナ起動（初回インストール完了後、vendor/bin/sailが使えるようになります）
./vendor/bin/sail up -d

# アプリキー生成
./vendor/bin/sail artisan key:generate

# フロントエンド依存関係インストール
./vendor/bin/sail npm install

# マイグレーション実行
./vendor/bin/sail artisan migrate

# シーディング
./vendor/bin/sail artisan db:seed

# フロントエンドビルド（開発中はwatch）
./vendor/bin/sail npm run dev
```

- アプリ: http://localhost
- phpMyAdmin: http://localhost:8080

## テスト・カバレッジ

```bash
./vendor/bin/sail artisan test --coverage
```

カバレッジ目標: 70%以上（使用ドライバ指定なし、PCOVを使用）

## APIエンドポイント一覧

すべて `/api/v1` 配下（レスポンスはJSON）。

| メソッド | パス | 概要 |
| --- | --- | --- |
| GET | /api/v1/contacts | お問い合わせ一覧取得（keyword・gender・category_id・dateで絞り込み、ページネーション対応） |
| GET | /api/v1/contacts/{id} | お問い合わせ詳細取得（category・tagsを含む） |
| POST | /api/v1/contacts | お問い合わせ新規作成 |
| PUT | /api/v1/contacts/{id} | お問い合わせ更新（タグは指定内容で同期） |
| DELETE | /api/v1/contacts/{id} | お問い合わせ削除 |

バリデーションエラー時は422、存在しないIDを指定した場合は404（JSON形式のエラーメッセージ）を返します。

## ER図

テーブル構成・リレーションは [`docs/er-diagram.md`](docs/er-diagram.md) を参照してください。

## 作成者

福井達平
