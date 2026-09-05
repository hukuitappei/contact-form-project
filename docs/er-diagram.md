# ER図

お問い合わせフォーム確認テストのテーブル構成です。`categories` / `tags` / `contacts` / `contact_tag` はカスタム実装分（Phase0-5で作成）、`users` は管理者認証用にLaravel/Fortifyが提供する標準テーブルです。

## 概要図

テーブル同士の関係をざっくり把握するための図です。役割ごとに色分けしています。

```mermaid
flowchart LR
    classDef master fill:#e0f2fe,stroke:#0284c7,color:#0c4a6e
    classDef core fill:#fef3c7,stroke:#d97706,color:#78350f
    classDef auth fill:#f3e8ff,stroke:#9333ea,color:#581c87

    CAT["カテゴリ<br/>categories"]:::master
    TAG["タグ<br/>tags"]:::master
    C["お問い合わせ<br/>contacts"]:::core
    CT["お問い合わせ×タグ<br/>contact_tag（中間テーブル）"]:::core
    U["管理者<br/>users"]:::auth

    CAT -->|"1つのカテゴリに<br/>複数のお問い合わせ"| C
    C -->|"1件のお問い合わせに<br/>複数のタグ付け"| CT
    TAG -->|"1つのタグは<br/>複数のお問い合わせに付与可"| CT
    U -.->|"管理画面ログインのみ<br/>（DB上の直接リレーションなし）"| C

    subgraph 凡例
        L1["マスタ系"]:::master
        L2["中心データ・中間テーブル"]:::core
        L3["認証系"]:::auth
    end
```

## 詳細ER図

型・制約を含めた技術参照用の図です（`created_at` / `updated_at` は全テーブル共通のため省略）。

```mermaid
erDiagram
    categories ||--o{ contacts : has
    contacts ||--o{ contact_tag : has
    tags ||--o{ contact_tag : "tagged in"

    categories {
        bigint id PK
        string content
    }

    tags {
        bigint id PK
        string name UK "max 50"
    }

    contacts {
        bigint id PK
        bigint category_id FK
        string first_name
        string last_name
        tinyint gender
        string email
        string tel "max 11"
        string address
        string building "nullable"
        string detail "max 120"
    }

    contact_tag {
        bigint id PK
        bigint contact_id FK
        bigint tag_id FK
    }

    users {
        bigint id PK
        string name
        string email UK
        string password
    }
```

## リレーション補足

- `contacts.category_id` → `categories.id`（`ON DELETE CASCADE`）
- `contact_tag.contact_id` → `contacts.id`（`ON DELETE CASCADE`）
- `contact_tag.tag_id` → `tags.id`（`ON DELETE CASCADE`）
- `contact_tag` は `contacts` と `tags` の多対多を仲介する中間テーブルで、`(contact_id, tag_id)` に複合ユニーク制約あり（同じお問い合わせに同じタグを重複付与できない）
- `users` は管理画面（`/admin`, `/login` など）の認証にのみ使用し、`contacts` 等とは直接のリレーションを持たない
