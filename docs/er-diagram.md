# ER図

お問い合わせフォーム確認テストのテーブル構成です。`categories` / `tags` / `contacts` / `contact_tag` はカスタム実装分（Phase0-5で作成）、`users` は管理者認証用にLaravel/Fortifyが提供する標準テーブルです。

```mermaid
erDiagram
    categories ||--o{ contacts : "1つのカテゴリに複数のお問い合わせ"
    contacts ||--o{ contact_tag : "1つのお問い合わせに複数のタグ付け"
    tags ||--o{ contact_tag : "1つのタグは複数のお問い合わせに付与可能"

    categories {
        bigint id PK
        string content
        timestamp created_at
        timestamp updated_at
    }

    tags {
        bigint id PK
        string name UK "unique, max 50"
        timestamp created_at
        timestamp updated_at
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
        timestamp created_at
        timestamp updated_at
    }

    contact_tag {
        bigint id PK
        bigint contact_id FK
        bigint tag_id FK
        timestamp created_at
        timestamp updated_at
    }

    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string remember_token
        timestamp created_at
        timestamp updated_at
    }
```

## リレーション補足

- `contacts.category_id` → `categories.id`（`ON DELETE CASCADE`）
- `contact_tag.contact_id` → `contacts.id`（`ON DELETE CASCADE`）
- `contact_tag.tag_id` → `tags.id`（`ON DELETE CASCADE`）
- `contact_tag` は `contacts` と `tags` の多対多を仲介する中間テーブルで、`(contact_id, tag_id)` に複合ユニーク制約あり（同じお問い合わせに同じタグを重複付与できない）
- `users` は管理画面（`/admin`, `/login` など）の認証にのみ使用し、`contacts` 等とは直接のリレーションを持たない
