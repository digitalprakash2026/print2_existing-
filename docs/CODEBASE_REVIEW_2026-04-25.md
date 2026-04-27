# Codebase Review & Improvement Backlog (2026-04-25)

## Scope reviewed
- Reviewed repository inventory (`rg --files`): **51 files** total.
- Ran PHP syntax validation on all `*.php` files (`php -l` loop): **no parse errors**.

## Top-priority improvements (recommended order)

### 1) Remove hardcoded secret defaults from config
**Why:** `config/config.php` currently includes concrete default DB credentials and app values. This is risky for security and deployment drift.

**Suggested changes:**
- Replace sensitive fallback defaults with empty values and fail-fast startup checks.
- Add `.env.example` with required keys and safe placeholders.
- Add runtime guard: if required env keys are missing in non-local env, abort with clear error.

**Files:**
- `config/config.php`

---

### 2) Strengthen admin authentication policy
**Why:** Admin auth currently accepts legacy plaintext/MD5/SHA1 variants during verification to support old dumps. This weakens security posture.

**Suggested changes:**
- Restrict acceptance to modern password hashes only.
- Move legacy migration behind a one-time CLI migration script (offline), not runtime fallback.
- Add admin login rate-limiting and account/IP lockout similar to user login.

**Files:**
- `app/src/Auth/Auth.php`
- `admin/router.php`

---

### 3) Add centralized request validation for APIs
**Why:** Request validation/sanitization is currently route-local and repetitive, especially in `admin/router.php` and `app/api/router.php`.

**Suggested changes:**
- Introduce small validator helpers (required fields, enum validation, numeric ranges, normalized strings).
- Return consistent error payload shape and HTTP codes across all endpoints.
- Add stricter schema checks for product/image upload payloads.

**Files:**
- `admin/router.php`
- `app/api/router.php`
- `app/src/Catalog/ProductCatalog.php`

---

### 4) Split oversized routers into module handlers
**Why:** Router files are large and carry many responsibilities (auth, products, orders, settings, uploads).

**Suggested changes:**
- Break admin API into module-specific handlers (orders, products, qualities, coupons, settings).
- Keep `admin/router.php` and `app/api/router.php` as dispatch-only entrypoints.
- Move repeated DB operations into service classes in `app/src`.

**Files:**
- `admin/router.php`
- `app/api/router.php`
- `app/src/*`

---

### 5) Improve transactionality around order/payment flows
**Why:** Checkout/order/payment operations span multiple queries/classes and should be guarded with explicit transaction boundaries.

**Suggested changes:**
- Use explicit DB transactions for create-order + items + status history updates.
- Add idempotency key handling for payment callback/webhook processing.
- Persist structured payment/audit events for reconciliation.

**Files:**
- `app/src/Orders/OrderManager.php`
- `app/src/Payment/Razorpay.php`
- `app/src/Orders/AdminAudit.php`

---

### 6) Front-end maintainability: decompose large CSS/JS bundles
**Why:** `assets/css/app.css` and `assets/js/app.js` are monolithic and mix unrelated concerns.

**Suggested changes:**
- Split CSS by domain (layout, components, pages, admin).
- Split JS by feature (drawer, cart, checkout, product page, admin panels).
- Add lightweight lint/format tooling (ESLint + Prettier; Stylelint optional).

**Files:**
- `assets/css/app.css`
- `assets/js/app.js`

---

### 7) Add automated quality gates
**Why:** No committed test/lint baseline visible in repo.

**Suggested changes:**
- Add basic CI workflow: PHP lint, coding standards, and smoke tests.
- Add unit tests for pricing/cart/order calculations and coupon logic.
- Add API contract smoke tests for key endpoints.

**Files:**
- New: CI workflow file(s)
- `app/src/Cart/Pricing.php`
- `app/src/Cart/Cart.php`
- `app/src/Orders/OrderManager.php`

---

### 8) Documentation and operations hardening
**Why:** Setup and migration details exist, but operational runbooks and environment expectations can be clearer.

**Suggested changes:**
- Add root `README.md` with architecture map and local run instructions.
- Add deployment checklist (env vars, writable paths, DB migrations, cron jobs, payment keys).
- Add incident troubleshooting section for payment/email/chatbot integrations.

**Files:**
- `docs/CHATBOT_SETUP.md`
- `database/sql/*.sql`
- New: `README.md`, `docs/DEPLOYMENT_CHECKLIST.md`

---

## Lower-priority cleanup
- Remove or lock down debug/maintenance utilities after use (`includes/debug.php`, `includes/fix_admin_password.php`).
- Standardize naming and responsibility boundaries across templates/partials.
- Introduce stronger typing and docblocks in service classes for long-term maintainability.

## Suggested implementation roadmap
1. **Security baseline:** secrets, auth hardening, login throttling.
2. **Stability baseline:** validation layer + transaction boundaries.
3. **Maintainability:** router decomposition + CSS/JS modularization.
4. **Reliability:** tests + CI + deployment docs.
