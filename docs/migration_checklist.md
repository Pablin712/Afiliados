# Laravel Migration Checklist - Affiliate System

## 1) Pre-migration checklist
- [ ] Confirm MySQL 8+ and database created.
- [ ] Configure `.env`: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- [ ] Confirm Spatie Permission package is installed.
- [ ] Freeze schema decisions from `docs/data_dictionary.md`.

## 2) Migration strategy
Recommended approach:
1. Adjust existing `users` table.
2. Create independent catalogs.
3. Create transactional tables.
4. Add cross-references requiring already-created tables.
5. Seed roles, permissions, and membership types.

## 3) File creation plan (artisan)

### 3.1 Users adjustment
- [ ] Create migration: add `sponsor_id`, `commission_balance` in `users`.
- [ ] Add self-FK for `sponsor_id`.
- [ ] Ensure data strategy for existing users before setting NOT NULL.

Suggested command:
- `php artisan make:migration add_sponsor_and_commission_to_users_table --table=users`

### 3.2 Membership catalog
- [ ] Create `membership_types` table.

Suggested command:
- `php artisan make:migration create_membership_types_table`

### 3.3 Financial base
- [ ] Create `banks` table.
- [ ] Create `payments` table.
- [ ] Create `transactions` table.
- [ ] Create `profits` table.
- [ ] In `profits`, include `period_month` and `state` as (`pending`,`made`).

Suggested commands:
- `php artisan make:migration create_banks_table`
- `php artisan make:migration create_payments_table`
- `php artisan make:migration create_transactions_table`
- `php artisan make:migration create_profits_table`

### 3.4 Membership lifecycle
- [ ] Create `memberships` table.
- [ ] Add `user_id` unique.
- [ ] Add FK to `membership_types`.
- [ ] Add nullable FK `last_payment_id` to `payments`.

Suggested command:
- `php artisan make:migration create_memberships_table`

### 3.5 Audit
- [ ] Create `actions` table.
- [ ] Add indexes for (`user_id`, `module`, `action`, `created_at`, `ip_address`).

Suggested command:
- `php artisan make:migration create_actions_table`

## 4) Foreign keys validation checklist
- [ ] `users.sponsor_id -> users.id` (restrict delete).
- [ ] `memberships.user_id -> users.id` (cascade delete).
- [ ] `memberships.membership_type_id -> membership_types.id` (restrict delete).
- [ ] `memberships.last_payment_id -> payments.id` (set null delete).
- [ ] `payments.user_id -> users.id` (restrict delete).
- [ ] `payments.bank_id -> banks.id` (restrict delete).
- [ ] `payments.reviewed_by -> users.id` (set null delete).
- [ ] `transactions.bank_id -> banks.id` (restrict delete).
- [ ] `profits.user_id -> users.id` (restrict delete).
- [ ] `profits.paid_by -> users.id` (set null delete).
- [ ] `actions.user_id -> users.id` (set null delete).

## 5) Spatie security seed checklist

### 5.1 Roles
- [ ] `admin`
- [ ] `user`

### 5.2 Permissions pattern per module
For each module `{module}` create:
- [ ] `view {module}`
- [ ] `create {module}`
- [ ] `edit {module}`
- [ ] `delete {module}`
- [ ] `manage {module}`
- [ ] `report {module}`

Initial modules:
- [ ] users
- [ ] memberships
- [ ] membership_types
- [ ] banks
- [ ] transactions
- [ ] payments
- [ ] profits
- [ ] actions

### 5.3 Assignment
- [ ] Assign all permissions to `admin`.
- [ ] Assign limited self-service permissions to `user`.

## 6) Business seed checklist
- [ ] Seed `membership_types`:
  - [ ] free
  - [ ] customer
  - [ ] beginner
  - [ ] explorer
  - [ ] professional
  - [ ] elite
- [ ] Create first admin user.
- [ ] Assign role `admin` to first admin user.
- [ ] Set `admin.sponsor_id = admin.id`.

## 7) Post-migration verification
- [ ] Run migrations on empty DB: `php artisan migrate:fresh`
- [ ] Run seeders: `php artisan db:seed`
- [ ] Confirm admin can authenticate.
- [ ] Confirm user registration path enforces sponsor.
- [ ] Confirm one membership per user.
- [ ] Confirm payment approval flow can update membership validity.
- [ ] Confirm `profits` monthly data (`period_month`) is queryable by admin.
- [ ] Confirm `profits.state` transitions from `pending` to `made`.
- [ ] Confirm audit writes records for views/actions.

## 8) Rollback plan
- [ ] Validate rollback in dev: `php artisan migrate:rollback`
- [ ] Validate full reset: `php artisan migrate:fresh --seed`

## 9) Open decisions before coding final logic
- [ ] `payments.number` unique strategy (global or per bank).
- [ ] Membership renewal extension rule (`expires_at` from current expiration or approval date).
- [ ] Profit settlement cadence and trigger.
- [ ] Audit exclusion list (static assets, health checks, background jobs).
