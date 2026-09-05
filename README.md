# お問い合わせフォーム（確認テスト）

COACHTECH確認テスト課題「新お問い合わせフォーム」の実装リポジトリです。公開のお問い合わせフォーム（入力・確認・送信）と、認証必須の管理画面（一覧・検索・詳細・CSVエクスポート・タグ管理）、およびAPIを実装します。

> 課題の詳細仕様は `docs/福井 達平さん_確認テスト_新お問い合わせフォーム_要件シート.xlsx` を参照してください。

## 技術スタック

| 項目 | バージョン |
| --- | --- |
| PHP | 8.2 |
| Laravel | 10.x |
| MySQL | 8.0 |
| フロントエンド | Tailwind CSS 3.4 / Alpine.js / Vite |
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

詳細な手順は `docs/03_環境構築手順.txt`（要件シートより抽出）を参照してください。

## テスト・カバレッジ

```bash
./vendor/bin/sail artisan test --coverage
```

カバレッジ目標: 70%以上（使用ドライバ指定なし、PCOVを使用）

## ER図

テーブル構成・リレーションは [`docs/er-diagram.md`](docs/er-diagram.md) を参照してください。

## 開発フロー

Issue単位（1 Issue = 1 branch = 1 PR）でPhase0〜Phase9まで段階的に実装します。Phase構成は `docs/construction_plan.md` および GitHub Issues を参照してください。

| Phase | 内容 |
| --- | --- |
| Phase0 | 環境構築・DB設計・シーディング |
| Phase1 | モデル・リレーション定義 |
| Phase2 | 公開お問い合わせフォーム機能 |
| Phase3 | 管理者認証（Fortify） |
| Phase4 | 管理画面（一覧・詳細） |
| Phase5 | タグ管理CRUD |
| Phase6 | コード品質・README最終化 |
| Phase7 | CSVエクスポート機能 |
| Phase8 | REST API |
| Phase9 | 全体リグレッション・提出準備 |

## ディレクトリ構成（補足）

- `docs/` — 要件シート抽出資料、ER図、構築手順メモ
