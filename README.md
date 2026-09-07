# お問い合わせフォーム（確認テスト）

COACHTECH確認テスト課題「新お問い合わせフォーム」の実装リポジトリです。公開のお問い合わせフォーム（入力・確認・送信）と、認証必須の管理画面（一覧・検索・詳細・CSVエクスポート・タグ管理）、およびAPIを実装します。

## 実装済み機能

- **公開お問い合わせフォーム**: 入力 → 確認 → 送信 → サンクスページ表示
- **管理者認証**: Fortifyによる新規登録・ログイン・ログアウト、ログイン試行のレート制限
- **管理画面**: お問い合わせ一覧（キーワード・性別・カテゴリ・日付での絞り込み、ページネーション）、詳細表示、削除
- **タグ管理**: 登録・編集・削除、重複名バリデーション

CSVエクスポート・APIは今後実装予定です。

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

```bash
# コンテナ起動
./vendor/bin/sail up -d

# 依存関係インストール（初回のみ）
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# .env 準備・アプリキー生成
cp .env.example .env
./vendor/bin/sail artisan key:generate

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

## ER図

テーブル構成・リレーションは [`docs/er-diagram.md`](docs/er-diagram.md) を参照してください。

## 作成者

福井達平
