# DER Final - Affiliate System

## 1. Entity-Relationship Diagram (Mermaid)

```mermaid
erDiagram
    USERS ||--o| MEMBERSHIPS : has
    MEMBERSHIP_TYPES ||--o{ MEMBERSHIPS : defines
    USERS ||--o{ USERS : sponsors

    USERS ||--o{ PAYMENTS : makes
    USERS ||--o{ USER_BANKS : owns
    BANKS ||--o{ TRANSACTIONS : records
    TRANSACTIONS ||--o| PAYMENTS : income_for
    TRANSACTIONS ||--o| PROFITS : expense_for

    USERS ||--o{ PROFITS : receives
    USER_BANKS ||--o{ PROFITS : payout_target
    USERS ||--o{ ACTIONS : performs

    PAYMENTS ||--o{ MEMBERSHIPS : last_payment_reference
    USERS ||--o{ PAYMENTS : reviews_as_admin
    USERS ||--o{ PROFITS : pays_as_admin
```

## 2. Cardinality summary
- `users` 1 --- 0..1 `memberships`
- `membership_types` 1 --- N `memberships`
- `users` 1 --- N `users` (referral by `sponsor_id`)
- `users` 1 --- N `payments`
- `users` 1 --- N `user_banks`
- `banks` 1 --- N `transactions`
- `transactions` 1 --- 0..1 `payments`
- `transactions` 1 --- 0..1 `profits`
- `users` 1 --- N `profits`
- `user_banks` 1 --- N `profits`
- `users` 1 --- N `actions`

## 3. Implementation notes
- `admin` exists in `users` with role `admin` and does not have row in `memberships`.
- All `user` role accounts must have exactly one row in `memberships`.
- `membership_types.name` must be UNIQUE at database level.
- `memberships.user_id` must be UNIQUE at database level (1:1 with `users`).
- `memberships.last_payment_id` references latest effective payment (nullable).
- `payments.transaction_id` links approved payment to an income transaction.
- `profits.transaction_id` links executed payout to an expense transaction.
- `profits.user_bank_id` defines target beneficiary account of the user.
- `actions` is append-only for audit integrity.
- `profits.state` uses `pending` and `made` in this phase.
- `profits.period_month` (first day of month, e.g. `2026-03-01`) supports monthly payout visibility for admin.
