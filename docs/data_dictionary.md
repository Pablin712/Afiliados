# Data Dictionary - Affiliate System

## 1. Scope and assumptions
- Database engine: MySQL 8+
- Charset/Collation: `utf8mb4` / `utf8mb4_unicode_ci`
- Timestamp strategy: `created_at`, `updated_at` (Laravel standard)
- Soft delete: only where explicitly indicated
- Roles and permissions: Spatie package (`roles`, `permissions`, `model_has_roles`, etc.)

## 2. Authorization model (fixed)
- Roles: `admin`, `user`
- Membership is NOT a role.
- Only users with role `user` have record in `memberships` (1:1).

## 3. Tables specification

---

## 3.1 `users` (existing + adjustments)
Purpose: System identities and referral tree.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| name | VARCHAR(255) | NO | - | - | |
| email | VARCHAR(255) | NO | - | UNIQUE | |
| email_verified_at | TIMESTAMP | YES | NULL | - | |
| password | VARCHAR(255) | NO | - | - | |
| remember_token | VARCHAR(100) | YES | NULL | - | |
| sponsor_id | BIGINT UNSIGNED | NO | - | INDEX | FK -> `users.id` |
| commission_balance | DECIMAL(12,2) | NO | 0.00 | - | Accumulated balance |
| created_at | TIMESTAMP | YES | NULL | - | |
| updated_at | TIMESTAMP | YES | NULL | - | |

Business constraints:
- Every row must have `sponsor_id`.
- Admin row uses self-reference (`sponsor_id = id`).

---

## 3.2 `membership_types`
Purpose: Membership catalog/rules.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| name | VARCHAR(50) | NO | - | UNIQUE | `free`,`customer`,`beginner`,`explorer`,`professional`,`elite` |
| affiliates_required | INT UNSIGNED | NO | 0 | - | Min direct active affiliates |
| cost | DECIMAL(12,2) | NO | 0.00 | - | Catalog cost |
| profit | DECIMAL(12,2) | NO | 0.00 | - | Monthly/periodic expected profit |
| created_at | TIMESTAMP | YES | NULL | - | |
| updated_at | TIMESTAMP | YES | NULL | - | |

Seed reference values:
- free (0, 0.00, 0.00)
- customer (0, 97.00, 0.00)
- beginner (3, 0.00, 0.00)
- explorer (10, 0.00, 100.00)
- professional (20, 0.00, 200.00)
- elite (30, 0.00, 300.00)

Note:
- First registration payment ($147) is a business rule from `payments`, not necessarily the `customer.cost` value.

---

## 3.3 `memberships`
Purpose: Membership lifecycle per `user` (1:1).

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| user_id | BIGINT UNSIGNED | NO | - | UNIQUE | FK -> `users.id` |
| membership_type_id | BIGINT UNSIGNED | NO | - | INDEX | FK -> `membership_types.id` |
| status | ENUM('active','free','expired','pending_payment') | NO | 'pending_payment' | INDEX | Current state |
| started_at | DATETIME | YES | NULL | - | |
| expires_at | DATETIME | YES | NULL | INDEX | |
| last_payment_id | BIGINT UNSIGNED | YES | NULL | INDEX | FK -> `payments.id` |
| created_at | TIMESTAMP | YES | NULL | - | |
| updated_at | TIMESTAMP | YES | NULL | - | |

Business constraints:
- One membership per user (`UNIQUE user_id`).
- No membership record for admin.

---

## 3.4 `banks`
Purpose: Admin-owned payment destinations and balances.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| name | VARCHAR(120) | NO | - | INDEX | Bank/payment method name |
| owner | VARCHAR(150) | NO | - | - | Owner label |
| identification | VARCHAR(50) | NO | - | INDEX | Document/ID |
| number | VARCHAR(80) | NO | - | INDEX | Account/phone/reference |
| amount | DECIMAL(14,2) | NO | 0.00 | - | Current balance |
| detail | TEXT | YES | NULL | - | |
| photo | VARCHAR(255) | YES | NULL | - | Stored path |
| created_at | TIMESTAMP | YES | NULL | - | |
| updated_at | TIMESTAMP | YES | NULL | - | |

---

## 3.5 `transactions`
Purpose: Ledger movements per bank.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| bank_id | BIGINT UNSIGNED | NO | - | INDEX | FK -> `banks.id` |
| type | ENUM('income','expense') | NO | - | INDEX | |
| amount_previous | DECIMAL(14,2) | NO | 0.00 | - | |
| amount | DECIMAL(14,2) | NO | 0.00 | - | Movement amount |
| amount_now | DECIMAL(14,2) | NO | 0.00 | - | Resulting balance |
| detail | TEXT | YES | NULL | - | |
| is_annulled | TINYINT(1) | NO | 0 | INDEX | 0/1 |
| created_at | TIMESTAMP | YES | NULL | INDEX | |

---

## 3.6 `payments`
Purpose: User proofs/payments for registration/renewal.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| user_id | BIGINT UNSIGNED | NO | - | INDEX | FK -> `users.id` |
| bank_id | BIGINT UNSIGNED | NO | - | INDEX | FK -> `banks.id` |
| number | VARCHAR(120) | NO | - | INDEX | Operation/reference number |
| photo | VARCHAR(255) | YES | NULL | - | Voucher path |
| amount | DECIMAL(12,2) | NO | 0.00 | - | |
| state | ENUM('approved','rejected','pending') | NO | 'pending' | INDEX | |
| reviewed_by | BIGINT UNSIGNED | YES | NULL | INDEX | FK -> `users.id` (admin) |
| reviewed_at | DATETIME | YES | NULL | INDEX | |
| created_at | TIMESTAMP | YES | NULL | INDEX | |
| updated_at | TIMESTAMP | YES | NULL | - | |

Business constraints:
- Only admin can transition `state` from `pending` to `approved/rejected`.
- On `approved`, membership validity is recalculated/extended.

---

## 3.7 `profits`
Purpose: Profit/commission payouts to users.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| user_id | BIGINT UNSIGNED | NO | - | INDEX | FK -> `users.id` |
| period_month | DATE | NO | - | INDEX | First day of payout month (YYYY-MM-01) |
| amount | DECIMAL(12,2) | NO | 0.00 | - | |
| state | ENUM('pending','made') | NO | 'pending' | INDEX | |
| detail | TEXT | YES | NULL | - | |
| paid_by | BIGINT UNSIGNED | YES | NULL | INDEX | FK -> `users.id` (admin) |
| paid_at | DATETIME | YES | NULL | INDEX | |
| created_at | TIMESTAMP | YES | NULL | INDEX | |
| updated_at | TIMESTAMP | YES | NULL | - | |

Business constraints:
- `pending`: commission calculated and awaiting payment.
- `made`: commission already paid.
- The detailed commission formula is pending definition in a later phase.

---

## 3.8 `actions` (audit)
Purpose: Full traceability for views and write actions.

| Column | Type | Null | Default | Index | Notes |
|---|---|---|---|---|---|
| id | BIGINT UNSIGNED | NO | AI | PK | |
| user_id | BIGINT UNSIGNED | YES | NULL | INDEX | FK -> `users.id` |
| module | VARCHAR(80) | NO | - | INDEX | e.g. users, memberships |
| action | VARCHAR(80) | NO | - | INDEX | e.g. view_index, create, approve_payment |
| method | VARCHAR(10) | NO | - | INDEX | GET/POST/PUT/PATCH/DELETE |
| route | VARCHAR(180) | YES | NULL | INDEX | Named route if available |
| url | VARCHAR(500) | YES | NULL | - | Requested URL |
| ip_address | VARCHAR(45) | YES | NULL | INDEX | IPv4/IPv6 |
| user_agent | VARCHAR(500) | YES | NULL | - | |
| payload | JSON | YES | NULL | - | Request input snapshot |
| old_values | JSON | YES | NULL | - | Before-change data |
| new_values | JSON | YES | NULL | - | After-change data |
| created_at | TIMESTAMP | YES | NULL | INDEX | |

Retention recommendation:
- Keep at least 12 months online; archive older records periodically.

---

## 4. Foreign keys (summary)
- `users.sponsor_id` -> `users.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- `memberships.user_id` -> `users.id` (ON UPDATE CASCADE, ON DELETE CASCADE)
- `memberships.membership_type_id` -> `membership_types.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- `memberships.last_payment_id` -> `payments.id` (ON UPDATE CASCADE, ON DELETE SET NULL)
- `transactions.bank_id` -> `banks.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- `payments.user_id` -> `users.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- `payments.bank_id` -> `banks.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- `payments.reviewed_by` -> `users.id` (ON UPDATE CASCADE, ON DELETE SET NULL)
- `profits.user_id` -> `users.id` (ON UPDATE CASCADE, ON DELETE RESTRICT)
- `profits.paid_by` -> `users.id` (ON UPDATE CASCADE, ON DELETE SET NULL)
- `actions.user_id` -> `users.id` (ON UPDATE CASCADE, ON DELETE SET NULL)

## 5. Essential permissions matrix template
For each module `{module}` create these permissions:
- `view {module}`
- `create {module}`
- `edit {module}`
- `delete {module}`
- `manage {module}`
- `report {module}`

Suggested initial modules:
- users
- memberships
- membership_types
- banks
- transactions
- payments
- profits
- actions

## 6. Migration order (recommended)
1. users (adjustment migration for `sponsor_id`, `commission_balance`)
2. membership_types
3. banks
4. payments
5. memberships
6. transactions
7. profits
8. actions
9. spatie permission migrations and seeders (roles/permissions assignment)

## 7. Pending decisions before coding
1. Whether `payments.number` must be globally unique or unique per `bank_id`.
2. Exact rule for extending `expires_at` when user pays before expiration:
   - Extend from current `expires_at`, or
   - Restart from approval date.
3. Profit settlement periodicity (manual/automatic, monthly cutoff date).
4. Audit exclusions (if health-check/static assets should be ignored).
