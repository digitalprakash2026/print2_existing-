# Website + Admin Panel Improvement Audit

Date: 2026-04-25

## Highest-priority fixes

1. **Remove emergency/debug scripts from public web root immediately**
   - `print/fix_admin_password.php` can reset admin accounts with a known query key and reveals default credentials.
   - `print/debug.php` exposes environment/layout details and can test DB connectivity from browser.

2. **Move secrets out of code and rotate compromised credentials**
   - DB defaults are hardcoded in `rcs_app/config/config.php` (host/user/password), which is risky if code is leaked.
   - Use `.env` only and rotate existing DB/admin keys/passwords.

3. **Enforce CSRF checks for all state-changing API/admin endpoints**
   - CSRF tokens are generated but `Auth::verifyCsrf()` is not enforced by routers.
   - Add global middleware in `/api` and `/admin/api` for non-GET methods.

4. **Fix broken admin actions/routes**
   - Orders page posts status updates to `/admin/orders/{id}/status`, but implemented API route is `/admin/api/orders/{id}/status`.
   - Dashboard invoice links to `/invoice/{order_id}` (customer scope), should use `/admin/invoice/{order_id}` for admin workflow.

5. **Fix admin settings security + functionality**
   - `/admin/settings/save` accepts POST without CSRF verification.
   - `new_admin_password` is shown in form but currently saved as a regular setting instead of updating `admin_users.password` hash.

## Product/UX improvements

- Add guest checkout and OTP login to reduce drop-offs.
- Add delivery estimator + SLA badges at product and checkout stages.
- Improve cart resilience with retry/error states and optimistic UI rollback.
- Add reorder flow from My Orders and saved product configurations.
- Add richer search/filter/sort on `/products` and category pages.

## Admin panel improvements

- Build an explicit **Analytics page** or remove dead nav link (`/admin/analytics`) to avoid confusion.
- Add role-based permissions (owner, ops, support) and action-level authorization.
- Add bulk status updates + print queue view + Kanban board for order operations.
- Add media manager for artwork with preview/thumbnails and malware scanning hooks.
- Add refund/cancellation workflows with audit reason codes.

## Reliability/architecture improvements

- Replace `generateOrderId()` count-based strategy with collision-safe sequence/UUID + display alias.
- Add DB constraints and transactions around coupon usage race conditions.
- Convert outbound integrations (email/sheets) to queue jobs with retries/dead-letter handling.
- Add health endpoint and structured logs (JSON + correlation IDs).

## Performance improvements

- Minify/bundle CSS/JS and add content hashing for cache busting.
- Add server/browser caching headers for static assets.
- Lazy-load non-critical JS and defer admin dashboards/charts scripts.
- Optimize DB reads for admin dashboard with pre-aggregated/materialized stats if scale grows.

## SEO + marketing improvements

- Add canonical tags, Open Graph/Twitter metadata, and per-product schema.org markup.
- Add XML sitemap + robots.txt + 301 strategy for slug changes.
- Add conversion funnels (view product → add to cart → checkout started → paid) with analytics events.
- Add abandoned-cart reminder automation (email/WhatsApp with consent).

## Security hardening checklist

- Remove public maintenance tools (`debug.php`, password reset scripts) after one-time use.
- Add rate limiting for admin/user auth and sensitive endpoints.
- Add CSP, HSTS, Referrer-Policy, Permissions-Policy headers.
- Validate upload file signatures and optionally virus-scan before making files available.
- Tighten session settings (secure cookies only behind HTTPS, regenerate on privilege changes).

