# 3 High-Impact Improvements for Web App (Frontend + Admin + Backend)

## 1) Smart Quote + Auto-Feasibility Checker (Frontend + Admin)

### Problem solved
Customers often ask: "Ye order possible hai? Kitna time लगेगा? Final price kya बनेगा?" 
Currently pricing exists, but there is no guided "instant feasibility + timeline" flow.

### Proposed feature
Add a **Guided Quote Builder** on product pages:
- Step-by-step product configuration (size, material, finish, quantity, design type).
- Real-time price + GST + delivery ETA band (e.g., 24h, 48h, 3–5 days).
- Preflight validations before checkout (file present/missing, low DPI warnings, unsupported size combos).
- Customer can save quote and share on WhatsApp.

In admin panel:
- Quote inbox with status (`new`, `replied`, `converted`, `lost`).
- One-click convert quote to order draft.
- Track quote→order conversion by source/category.

### Why this is high ROI
- Reduces repetitive support calls/chats.
- Better lead capture for customers not ready to pay instantly.
- Employees can respond faster with structured quote data.

### Suggested implementation touchpoints
- Frontend: `templates/product.php`, `assets/js/app.js` (guided UI and live estimate rendering).
- Existing pricing backend can be reused: `app/api/router.php` (`/api/price/calculate`) and `app/src/Cart/Pricing.php`.
- Admin: extend `templates/admin/orders.php` patterns for a new `quotes` screen and APIs in `admin/router.php`.

---

## 2) Production Workflow Board + SLA Alerts (Admin Panel)

### Problem solved
Order status updates exist, but operations teams still need a faster execution view for daily production.

### Proposed feature
Create a **Kanban-style production board** in admin:
- Columns: `Received` → `Design Review` → `Printing` → `Finishing` → `Dispatch`.
- Drag/drop or quick-action status transitions.
- SLA timers (e.g., if an order stays in one stage for too long, auto-highlight red).
- Filter by due date, city, payment status, and priority.

Automation add-ons:
- Optional auto-WhatsApp template triggers on key stage changes.
- Internal "next action" checklist per order (proof pending, file issue, payment pending, dispatch pending).

### Why this is high ROI
- Faster daily decision making for owner/manager.
- Bottlenecks become visible instantly.
- Improves on-time delivery performance and customer communication.

### Suggested implementation touchpoints
- Current order/status foundation already exists in `templates/admin/orders.php` and `/admin/api/orders/*` routes in `admin/router.php`.
- Extend dashboard KPIs (`templates/admin/dashboard.php`) with stage-wise aging metrics and SLA breach counts.
- Log actions via existing audit helper patterns (`app/src/Orders/AdminAudit.php`).

---

## 3) "Reorder in 1 Click" + Customer Job Library (Frontend + Admin)

### Problem solved
Repeat customers (cards, flyers, packaging) place similar orders again and again, but re-entry takes time for both customer and staff.

### Proposed feature
Add **Reorder & Job Templates**:
- Customer "My Orders" page: `Reorder` button to clone previous config directly to cart.
- Save frequent jobs as templates (e.g., "Visiting Card – 1000 qty – matte").
- Optional versioned artwork library: choose old artwork, upload replacement, or request minor edits.

Admin side:
- View customer templates and top repeat jobs.
- Quick duplicate from admin for phone/WhatsApp assisted ordering.
- Auto-suggest upsell bundles during reorder (lamination, premium paper, faster shipping).

### Why this is high ROI
- Major reduction in order placement time.
- Increases repeat purchase frequency.
- Staff workload reduces for duplicate manual entry.

### Suggested implementation touchpoints
- Customer history already exists in `templates/my-orders.php` and order APIs in `app/api/router.php`.
- Cart add flow can be reused via `/api/cart/add` with template payload mapping.
- Admin analytics can surface repeat patterns in `templates/admin/analytics.php` and `templates/admin/customers.php`.

---

## Priority order to implement
1. **Production Workflow Board + SLA Alerts** (immediate ops efficiency).
2. **Reorder in 1 Click + Job Library** (repeat revenue + staff speed).
3. **Smart Quote + Feasibility Checker** (lead conversion + pre-sales efficiency).

