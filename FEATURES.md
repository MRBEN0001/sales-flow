# Sales Flow — Product Features

Sales Flow is a multi-tenant cloud POS and inventory system for pharmacies and retail shops. Each shop gets its own secure workspace, sales tools, stock control, and subscription billing.

Pricing and trial length are controlled in `config/subscription.php` (and can be overridden by environment variables where noted).

---

## 1. Pricing & plans

- Free trial for new shops (default **30 days**)
- **Monthly** subscription (NGN) — amount set in config
- **Yearly** subscription with configurable discount (default **40%** off full-year monthly price)
- **One-off / negotiable** plan via support (email or WhatsApp — not automated checkout)
- Paystack live/test payment checkout for monthly and yearly

*(Current config example: monthly ₦100 for testing; production is usually set higher, e.g. ₦7,500/month.)*

---

## 2. Central website (marketing & onboarding)

### Home
- Product landing page with clear CTAs
- Create shop (free trial), see plans, shop login
- Feature highlights (scan-to-sell, stock, cloud isolation)
- Floating WhatsApp chat support

### Shop registration
- Create a new shop with:
  - Shop name (UPPERCASE) → auto shop address/subdomain
  - Admin name, email (with confirmation), phone, password
- Unique checks across shops: email, phone, shop address
- Nigerian phone normalization (`0…` → `234…`)
- Device fingerprint check to limit free-trial abuse
- On success:
  - Isolated shop database created
  - Trial started
  - Welcome email to shop owner
  - Notification email to company support
  - Success page with shop login link

### Existing device / returning shop
- If the same device already has a shop, show that shop’s status instead of starting another free trial
- Links to subscribe or continue

### Central shop login
- Enter shop address → redirect to that shop’s login page

### Plans & subscribe
- View monthly, yearly, and one-off options
- Subscribe with Paystack (shop address + owner email)
- Payment success page with plan and next expiry

---

## 3. Multi-tenant shop isolation

- Each shop has its own subdomain and **separate database**
- Shop data (products, sales, users, settings) is fully isolated
- Cloud-hosted per-shop workspaces
- Support admin account can be seeded into every shop for maintenance

---

## 4. Subscription, trial & access control

- Shop statuses: **trial**, **active**, **expired**, **cancelled**
- Automatic trial/subscription expiry sync
- Expired shops are blocked from the app until they pay (or are activated)
- Pay from:
  - Central plans/subscribe pages
  - In-shop “subscription expired” page
- Payment records stored with reference, amount, channel, and access period
- Renewals extend the current paid period
- Payment history kept even if a shop is deleted/recreated
- Paystack webhook + callback verification
- Auto-restore of successful Paystack payments when local history is missing

---

## 5. Trial abuse prevention (device fingerprint)

- Browser/device fingerprint on register and login
- Cross-subdomain device cookie so shop subdomain and central site match
- One free trial per device (extra shops require subscribe / existing shop flow)
- Support/admin email logins are excluded from device linking

---

## 6. Shop login & user roles

- Per-shop login on the shop subdomain
- **Admin (level 1):** full access — products, categories, sections, users, settings, purchases, etc.
- **Cashier (level 2):** sales, products (limited), profile
- User profile: name, password, photo
- Logout

---

## 7. Dashboard

### Admin dashboard
- Counts: categories, products, members, suppliers
- Sales totals and month-to-date income overview
- Chart: sales vs purchases vs expenses (where data exists)

### Cashier dashboard
- Welcome screen
- Quick link to **New sale**

---

## 8. Categories

- Create, edit, delete product categories
- Products organized by category

---

## 9. Sections (sales / stock areas)

- Create and manage sections (e.g. counters, store areas)
- Assign products to sections
- Filter sales and reports by section

---

## 10. Products & inventory

- Full product CRUD (admin); cashiers can list, add stock, and add products
- Product fields include:
  - Name, product code, barcode
  - Category, section, brand
  - Buy price, sell price, discount %
  - Stock quantity
  - Expiry date
- Stock alerts and filters:
  - Out of stock
  - Low stock
  - Expired
  - Soon to expire
- Add stock increments
- Bulk select & delete (admin)
- Print barcode labels (PDF)
- **Take-stock PDF** inventory report
- Stock auto-deducted when a sale is completed

---

## 11. Point of sale (sales)

- **Scan to sell** with barcode scanner support
- Fast checkout cart
- Product picker modal
- Line item quantity, price, and discount
- Cart-level discount
- Amount received and change calculation
- Optional customer name and phone
- Optional member selection
- Automatic receipt number
- Save / complete sale
- **Resume pending or previous sales**
- Delete sale (admin)
- Printable receipts:
  - Small receipt
  - Large receipt
  (controlled by shop settings)
- Sales list with filters and details (date, section, products, customer, receipt, totals, cashier)

---

## 12. Sales reports

- Daily sales report (+ PDF)
- Daily customer/room-style sales report (+ PDF)
- Weekly sales PDF
- Monthly sales PDF
- Section filtering on reports

---

## 13. Members (customer loyalty)

- Member CRUD (code, name, phone, address)
- Select member during checkout
- Print membership cards (PDF)
- *(Feature is implemented; sidebar entry may be hidden depending on layout config.)*

---

## 14. Suppliers

- Supplier CRUD (name, phone, address)
- Used with purchasing
- *(Implemented; sidebar may be hidden.)*

---

## 15. Purchases / procurement

- Create purchases against suppliers
- Purchase line items, discounts, totals
- Stock increases on purchase
- Purchase history and detail views
- *(Implemented; sidebar may be hidden.)*

---

## 16. Expenses

- Record shop expenses (description, amount, date)
- Expense list management
- *(Implemented; sidebar may be hidden.)*

---

## 17. Income / profit report

- Date-range income report (sales − purchases − expenses)
- PDF export
- *(Implemented; sidebar may be hidden.)*

---

## 18. Users (staff)

- Admin can create/manage cashiers
- Name, email, password
- Role separation (admin vs cashier)

---

## 19. Shop settings

- Company name, description, phone, address
- Logo upload
- Membership card image
- Default discount
- Receipt type (small / large)
- Used on receipts and PDF letterheads

---

## 20. Emails

- **Shop created** — owner gets shop details, login link, trial info
- **Shop created (company copy)** — support/company notification
- **Subscription activated** — owner confirmation after payment
- **Subscription activated (company copy)** — support/company notification

---

## 21. WhatsApp support

- Floating “Chat support” button on central and shop pages
- Configurable support number
- Pre-filled help message
- Used for general help and one-off plan inquiries

---

## 22. System admin (Dev dashboard)

Secure admin area at `/dev` (separate login):

- List all shops with search (name, address, email, phone)
- Stats: total / trial / active / expired
- Per shop: status, plan, next expiry, owner contacts
- **History** timeline:
  - Free trial start
  - Every payment (Paystack or manual)
  - Amounts, status, channel, reference, access period
  - Newest payment on top
  - Nigeria (WAT) time in 12-hour format
- **Manual Activate** any shop (monthly or yearly) for payment recovery
- Open shop login
- Permanently delete shop + drop its database
- Paystack sync for missing successful payments

---

## 23. Security & operations highlights

- CSRF protection on forms
- Password hashing
- Role-based route protection
- Tenant session isolation
- Paystack signature verification on webhooks
- Manual activation audited in payment history (`channel = manual`)

---

## Quick feature summary (customer-facing)

- Scan to sell / barcode checkout  
- Fast checkout with discounts  
- Printable receipts  
- Resume pending sales  
- Out-of-stock tracking  
- Expiry tracking  
- Sales sections  
- Barcode labels  
- Take-stock PDF reports  
- Daily / weekly / monthly sales reports  
- Cloud-hosted shops with isolated data  
- Free trial, then monthly or yearly Paystack subscription  
- WhatsApp support  

---

*Last updated from the Sales Flow codebase. To change prices or trial length, edit `config/subscription.php`.*
