# SMM Panel — Social Media Marketing Platform

> A production-ready SMM (Social Media Marketing) panel where users purchase social media growth services, manage wallet balances, and track orders — with a full admin backend for analytics, provider integration, support, and automated fulfillment.

Built as a full-stack PHP application with no framework, featuring Stripe payments, JustAnotherPanel API integration, real-time in-app notifications, HTML email alerts, and a premium light-theme UI designed for clarity and speed.

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Stripe](https://img.shields.io/badge/Stripe-Checkout-635BFF?style=flat&logo=stripe&logoColor=white)](https://stripe.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Folder Structure](#folder-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Default Credentials](#default-credentials)
- [Order Flow](#order-flow)
- [Screenshots](#screenshots)
- [Database Schema](#database-schema)
- [Notification System](#notification-system)
- [License](#license)
- [Author](#author)

---

## Features

### User Experience
- **User registration and login** with bcrypt password hashing and secure session management
- **User dashboard** with wallet balance, order statistics, and recent activity
- **Services browsing** with live search and category filters (Instagram, YouTube, TikTok, and more)
- **Order placement** with validation, automatic balance deduction, and instant provider forwarding
- **Order history** with status tracking — pending, processing, in progress, completed, partial, failed
- **Add funds** via Stripe Checkout (test and live mode supported)
- **Support ticket system** — create tickets, view threaded conversations, receive admin replies
- **Real-time notification system** with bell icon dropdown, unread badge, and dedicated notifications page
- **Email notifications** via Gmail SMTP (PHPMailer) for welcome, orders, payments, and support replies
- **Help, Contact, Terms, and Privacy** public pages with FAQ accordion and contact form

### Admin Panel
- **Analytics dashboard** with stat cards, orders-per-day bar chart, revenue line chart (Chart.js), and recent orders table
- **Full CRUD** for users, services, orders, payments, and API providers
- **Manual order status management** with email alerts on completion or failure
- **Support ticket management** — reply as admin, close/reopen tickets
- **Provider configuration** — API keys, connection testing, service ID mapping
- **Admin notifications** and email alerts for new users, orders, payments, and tickets
- **Profit tracking** — charge vs. provider cost calculated per order

### Automation & Integrations
- **JustAnotherPanel API integration** (SMM API v2) for automated order fulfillment and status syncing
- **Cron job** for forwarding pending orders and polling provider statuses
- **Automatic refunds** on failed orders with user notification (in-app + email)
- **Stripe webhook** handler for reliable payment confirmation

### Design
- **Premium light theme UI** inspired by Linear / Notion
- **Inter font**, indigo accent color (`#4F46E5`), soft gray backgrounds
- Fully responsive Bootstrap 5 layout across landing page, user panel, and admin sidebar

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML, Bootstrap 5, Bootstrap Icons, Chart.js, Inter (Google Fonts) |
| **Backend** | PHP 8+ (no framework) |
| **Database** | MySQL 5.7+ / MariaDB |
| **Payments** | Stripe Checkout API + Webhooks |
| **Email** | PHPMailer + Gmail SMTP (TLS) |
| **Provider API** | JustAnotherPanel v2 (standard SMM API) |
| **Server** | Apache (XAMPP) |

---

## Folder Structure

```
smm-panel/
│
├── admin/                          # Admin panel
│   ├── includes/
│   │   ├── auth.php                # Admin authentication guard
│   │   ├── header.php              # White sidebar layout
│   │   ├── footer.php              # Admin footer
│   │   └── service_form_fields.php # Reusable service form partial
│   ├── index.php                   # Analytics dashboard (Chart.js)
│   ├── users.php                   # User management & balance adjustments
│   ├── services.php                # Service CRUD with provider mapping
│   ├── orders.php                  # Order list & status updates
│   ├── payments.php                # Transaction history & revenue stats
│   ├── providers.php               # API provider configuration
│   ├── tickets.php                 # Support ticket list
│   ├── ticket_view.php             # Admin ticket thread & replies
│   └── notifications.php           # Admin notification center
│
├── api/
│   └── provider.php                # SMM API v2 client, order forwarding, status sync, refunds
│
├── assets/
│   ├── css/style.css               # Global design system & light theme
│   └── js/main.js                  # Order modal, charts, UI interactions
│
├── cron/
│   └── process_orders.php          # Forward pending orders & sync provider statuses
│
├── includes/
│   ├── config.php                  # DB, Stripe, email, and site settings
│   ├── db.php                      # PDO database connection
│   ├── auth.php                    # Sessions, login helpers, formatMoney()
│   ├── notifications.php           # In-app notify() and notifyAdmins() helpers
│   ├── notification_dropdown.php   # Navbar bell dropdown component
│   ├── mailer.php                  # sendEmail() and event trigger functions
│   ├── email_templates.php         # HTML email templates (10 events)
│   ├── public_header.php           # Public pages navbar (Help, Contact, Legal)
│   ├── public_footer.php           # Public pages footer
│   ├── header.php                  # Logged-in user navbar
│   ├── footer.php                  # User panel footer
│   └── phpmailer/                  # PHPMailer library (manual install)
│       └── src/
│           ├── PHPMailer.php
│           ├── SMTP.php
│           └── Exception.php
│
├── payment/
│   ├── stripe.php                  # Stripe API helper (Checkout sessions)
│   ├── stripe_success.php          # Payment success redirect handler
│   └── stripe_webhook.php          # Stripe webhook endpoint
│
├── user/
│   ├── dashboard.php               # User home — balance, stats, recent orders
│   ├── services.php                # Browse services with search & filters
│   ├── place_order.php             # Order processing handler (POST)
│   ├── orders.php                  # Order history
│   ├── add_funds.php               # Stripe wallet top-up
│   ├── tickets.php                 # Create & list support tickets
│   ├── ticket_view.php             # Ticket conversation thread
│   └── notifications.php           # Full notifications page
│
├── database.sql                    # Full schema, seed data, default admin
├── index.php                       # Landing page
├── login.php                       # User login
├── register.php                    # User registration
├── logout.php                      # Session destroy
├── help.php                        # Help Center with FAQ accordion
├── contact.php                     # Contact form (saves to contacts table)
├── terms.php                       # Terms of Service
├── privacy.php                     # Privacy Policy
├── notification_read.php           # Mark notification read & redirect
├── .htaccess                       # Apache security rules
└── README.md
```

---

## Installation

### Prerequisites

- **PHP** 8.0+ with extensions: `pdo_mysql`, `curl`, `json`, `openssl`
- **MySQL** 5.7+ or MariaDB 10.3+
- **Apache** with `mod_rewrite` enabled
- **[XAMPP](https://www.apachefriends.org/)** recommended for local development

### Step 1 — Clone or download the project

```bash
git clone https://github.com/yourusername/smm-panel.git
cd smm-panel
```

Or copy the project folder into your web server root:

```
C:\xampp\htdocs\smm-panel\          # Windows (XAMPP)
/var/www/html/smm-panel/            # Linux
```

### Step 2 — Set up XAMPP

1. Install XAMPP and start **Apache** and **MySQL** from the control panel
2. Confirm PHP is available: `C:\xampp\php\php.exe -v`

### Step 3 — Import the database

**Option A — phpMyAdmin:**
1. Open `http://localhost/phpmyadmin`
2. Click **Import** → select `database.sql` → **Go**

**Option B — MySQL CLI:**

```bash
mysql -u root -p < database.sql
```

This creates the `smm_panel` database with all tables, sample services, and a default admin account.

### Step 4 — Configure the application

Edit `includes/config.php` with your database, Stripe, email, and site URL settings. See the [Configuration](#configuration) section below for full details.

### Step 5 — Run on localhost

Visit:

```
http://localhost/smm-panel/
```

Log in as admin (see [Default Credentials](#default-credentials)) and configure your API provider.

### Step 6 — Set up the cron job (recommended)

The cron script forwards pending orders to the provider and syncs order statuses.

**Linux crontab (every 5 minutes):**

```bash
*/5 * * * * php /var/www/html/smm-panel/cron/process_orders.php
```

**Windows Task Scheduler:**

| Field | Value |
|-------|-------|
| Program | `C:\xampp\php\php.exe` |
| Arguments | `C:\xampp\htdocs\smm-panel\cron\process_orders.php` |

**Manual test:**

```bash
php cron/process_orders.php
```

---

## Configuration

All settings live in `includes/config.php`.

### Database Connection

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smm_panel');
define('DB_USER', 'root');
define('DB_PASS', '');              // Your MySQL password
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'SMM Panel');
define('SITE_URL', 'http://localhost/smm-panel');  // Must match your public URL
```

### Stripe API Keys

1. Create an account at [https://dashboard.stripe.com](https://dashboard.stripe.com)
2. Use **Test mode** for development, **Live mode** for production
3. Copy keys from **Developers → API keys**

```php
define('STRIPE_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxx');
define('STRIPE_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxx');
define('STRIPE_WEBHOOK_SECRET', 'whsec_xxxxxxxxxxxxxxxx');
define('STRIPE_CURRENCY', 'usd');
```

**Webhook setup (recommended for production):**

1. Go to **Stripe Dashboard → Developers → Webhooks**
2. Add endpoint: `https://yourdomain.com/payment/stripe_webhook.php`
3. Select event: `checkout.session.completed`
4. Copy the signing secret into `STRIPE_WEBHOOK_SECRET`

### Gmail SMTP (Email Notifications)

1. Enable [2-Step Verification](https://myaccount.google.com/security) on your Google account
2. Generate an [App Password](https://myaccount.google.com/apppasswords)
3. Update config:

```php
define('MAIL_ENABLED', true);
define('MAIL_FROM', 'your@gmail.com');
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx');   // 16-character app password
define('MAIL_FROM_NAME', 'SMM Panel');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');
```

> Set `MAIL_ENABLED` to `false` to disable all outgoing emails. Ensure admin users have real email addresses in the database so admin alerts are delivered.

### JustAnotherPanel API Key

The panel uses the standard **SMM API v2** format, compatible with JustAnotherPanel and similar providers.

**Via Admin Panel (recommended):**

1. Log in as admin → **Manage Providers**
2. Edit or add a provider:
   - **Name:** `JustAnotherPanel`
   - **API URL:** `https://justanotherpanel.com/api/v2`
   - **API Key:** Your key from the JustAnotherPanel dashboard
   - **Status:** `Active`
3. Click **Test** to verify the connection

**Map services to provider IDs:**

1. Go to **Admin → Manage Services**
2. For each service, set the **Provider** and **Provider Service ID** (from the JAP service catalog)

Orders with valid provider mapping are auto-forwarded on placement and via the cron job.

---

## Default Credentials

### Admin Login

| Field | Value |
|-------|-------|
| **URL** | `http://localhost/smm-panel/login.php` |
| **Username** | `admin` |
| **Password** | `admin123` |

> Change the admin password immediately after first login in any non-local environment.

### Stripe Test Card

| Field | Value |
|-------|-------|
| **Card number** | `4242 4242 4242 4242` |
| **Expiry** | Any future date |
| **CVC** | Any 3 digits |
| **ZIP** | Any 5 digits |

Funds are credited to the user's wallet instantly after successful payment.

---

## Order Flow

```
┌──────────────┐     ┌───────────────┐     ┌─────────────────┐
│  User adds   │────▶│ User browses  │────▶│  User places    │
│   funds via  │     │   services    │     │     order       │
│   Stripe     │     │  (search/filter)│    │                 │
└──────────────┘     └───────────────┘     └────────┬────────┘
                                                     │
              Balance validated & deducted             ▼
              Order saved as "pending"          ┌─────────────┐
                                                │ Auto-forward│
                                                │ to JAP API  │
                                                └──────┬──────┘
                                                       │
              ┌────────────────────────────────────────┘
              ▼
       ┌─────────────┐     ┌──────────────┐     ┌─────────────┐
       │ Cron polls  │────▶│   Provider   │────▶│   Order     │
       │  statuses   │     │   processes  │     │  completed  │
       └─────────────┘     └──────────────┘     └─────────────┘
              │
              ▼ (on failure)
       Balance refunded automatically
       User notified (in-app + email)
```

**Step-by-step:**

1. **Register & log in** — User creates an account and receives a welcome email
2. **Add funds** — User tops up wallet via Stripe Checkout; balance updates instantly
3. **Browse services** — User searches/filters the catalog and selects a service
4. **Place order** — User enters link + quantity; system validates balance and deducts charge
5. **Auto-forward** — Order is sent to JustAnotherPanel API immediately (`api/provider.php`)
6. **Cron sync** — `cron/process_orders.php` forwards any pending orders and polls status updates
7. **Completion** — User receives in-app notification and email when order completes
8. **Failure handling** — Failed orders trigger automatic wallet refund + failure email

**Profit formula:** `profit = charge − provider_cost` (when cost rate is configured on the service)

---

## Screenshots

> Add screenshots to a `/screenshots` folder and replace the placeholders below.

| Page | Preview |
|------|---------|
| Landing Page | `![Landing Page](screenshots/landing.png)` |
| User Dashboard | `![User Dashboard](screenshots/dashboard.png)` |
| Services Catalog | `![Services](screenshots/services.png)` |
| Admin Analytics | `![Admin Analytics](screenshots/admin-analytics.png)` |
| Support Tickets | `![Support Tickets](screenshots/tickets.png)` |
| Notifications | `![Notifications](screenshots/notifications.png)` |
| Help Center | `![Help Center](screenshots/help.png)` |

---

## Database Schema

| Table | Purpose |
|-------|---------|
| `users` | Accounts, wallet balance, roles (user/admin) |
| `services` | SMM services with pricing, provider mapping, categories |
| `orders` | Order records, status, charge, cost, profit |
| `transactions` | Deposits, order charges, refunds |
| `providers` | External API credentials (JustAnotherPanel) |
| `tickets` | Support tickets |
| `ticket_replies` | Ticket conversation threads |
| `notifications` | In-app notification system |
| `contacts` | Contact form submissions |

---

## Notification System

### In-App Notifications

| Event | Recipient |
|-------|-----------|
| User registers | User (welcome) + Admins |
| Order placed | User + Admins |
| Order completed | User |
| Order failed (refunded) | User |
| Funds added (Stripe) | User + Admins |
| New support ticket | Admins |
| Admin replies to ticket | User |

```php
require_once __DIR__ . '/includes/notifications.php';

notify($userId, 'Title', 'Message', 'success', SITE_URL . '/user/orders.php');
notifyAdmins('New order', 'Order #123 placed.', 'info', SITE_URL . '/admin/orders.php');
```

### Email Notifications (PHPMailer)

| Event | Recipient |
|-------|-----------|
| Welcome email | User |
| Order confirmed | User |
| Order completed | User |
| Order failed (+ refund note) | User |
| Wallet topped up | User |
| Admin replied to ticket | User |
| New user registered | Admins |
| New order received | Admins |
| New support ticket | Admins |
| Payment received | Admins |

All email calls are wrapped in `try/catch` — if SMTP fails, the main action still completes and the error is logged.

---

## License

This project is open-source software licensed under the **MIT License**.

```
MIT License

Copyright (c) 2026 Tanushree Kalla

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## Author

**Built by Tanushree Kalla** — developed as an internship project demonstrating full-stack PHP development, payment integration, third-party API automation, and production-grade UI design.

For questions or issues, open a GitHub issue or use the built-in **Support Tickets** system in the panel.
