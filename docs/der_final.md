# DER Final - Affiliate System

## 1. Entity-Relationship Diagram (Mermaid)

```mermaid
erDiagram
    USERS ||--o| MEMBERSHIPS : has
    MEMBERSHIP_TYPES ||--o{ MEMBERSHIPS : defines
    USERS ||--o{ USERS : sponsors

    USERS ||--o{ PAYMENTS : makes
    BANKS ||--o{ PAYMENTS : receives
    BANKS ||--o{ TRANSACTIONS : records

    USERS ||--o{ PROFITS : receives
    USERS ||--o{ ACTIONS : performs

    PAYMENTS ||--o{ MEMBERSHIPS : last_payment_reference
    USERS ||--o{ PAYMENTS : reviews_as_admin
    USERS ||--o{ PROFITS : pays_as_admin

    USERS {
      BIGINT id PK
      BIGINT sponsor_id FK
      DECIMAL commission_balance
      VARCHAR email
      VARCHAR password
      TIMESTAMP created_at
      TIMESTAMP updated_at
    }

    MEMBERSHIP_TYPES {
      BIGINT id PK
      VARCHAR name
      INT affiliates_required
      DECIMAL cost
      DECIMAL profit
      TIMESTAMP created_at
      TIMESTAMP updated_at
    }

    MEMBERSHIPS {
      BIGINT id PK
      BIGINT user_id FK
      BIGINT membership_type_id FK
      ENUM status
      DATETIME started_at
      DATETIME expires_at
      BIGINT last_payment_id FK NULL
      TIMESTAMP created_at
      TIMESTAMP updated_at
    }

    BANKS {
      BIGINT id PK
      VARCHAR name
      VARCHAR owner
      VARCHAR identification
      VARCHAR number
      DECIMAL amount
      TEXT detail
      VARCHAR photo
      TIMESTAMP created_at
      TIMESTAMP updated_at
    }

    TRANSACTIONS {
      BIGINT id PK
      BIGINT bank_id FK
      ENUM type
      DECIMAL amount_previous
      DECIMAL amount
      DECIMAL amount_now
      BOOLEAN is_annulled
      TEXT detail
      TIMESTAMP created_at
    }

    PAYMENTS {
      BIGINT id PK
      BIGINT user_id FK
      BIGINT bank_id FK
      VARCHAR number
      VARCHAR photo
      DECIMAL amount
      ENUM state
      BIGINT reviewed_by FK NULL
      DATETIME reviewed_at NULL
      TIMESTAMP created_at
      TIMESTAMP updated_at
    }

    PROFITS {
      BIGINT id PK
      BIGINT user_id FK
      DATE period_month
      DECIMAL amount
      ENUM state
      TEXT detail
      BIGINT paid_by FK NULL
      DATETIME paid_at NULL
      TIMESTAMP created_at
      TIMESTAMP updated_at
    }

    ACTIONS {
      BIGINT id PK
      BIGINT user_id FK NULL
      VARCHAR module
      VARCHAR action
      VARCHAR method
      VARCHAR route
      VARCHAR url
      VARCHAR ip_address
      VARCHAR user_agent
      JSON payload NULL
      JSON old_values NULL
      JSON new_values NULL
      TIMESTAMP created_at
    }
```

## 2. Cardinality summary
- `users` 1 --- 0..1 `memberships`
- `membership_types` 1 --- N `memberships`
- `users` 1 --- N `users` (referral by `sponsor_id`)
- `users` 1 --- N `payments`
- `banks` 1 --- N `payments`
- `banks` 1 --- N `transactions`
- `users` 1 --- N `profits`
- `users` 1 --- N `actions`

## 3. Implementation notes
- `admin` exists in `users` with role `admin` and does not have row in `memberships`.
- All `user` role accounts must have exactly one row in `memberships`.
- `membership_types.name` must be UNIQUE at database level.
- `memberships.user_id` must be UNIQUE at database level (1:1 with `users`).
- `memberships.last_payment_id` references latest effective payment (nullable).
- `actions` is append-only for audit integrity.
- `profits.state` uses `pending` and `made` in this phase.
- `profits.period_month` (first day of month, e.g. `2026-03-01`) supports monthly payout visibility for admin.
