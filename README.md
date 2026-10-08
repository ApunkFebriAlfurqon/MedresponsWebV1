# RapidAid Ambulance Service
## Installation Guide

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- XAMPP / LARAGON / WAMP (local) or any web server

---

### Step 1 — Import Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create new database: `rapidaid_db`
3. Click **Import** → choose `database.sql` → Click **Go**

---

### Step 2 — Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'rapidaid_db');
define('APP_URL', 'http://localhost/rapidaid');
```

---

### Step 3 — Copy to Web Server
Copy the entire `rapidaid` folder to:
- **XAMPP**: `C:/xampp/htdocs/rapidaid`
- **LARAGON**: `C:/laragon/www/rapidaid`
- **WAMP**: `C:/wamp64/www/rapidaid`

---

### Step 4 — Set Permissions
Make sure `uploads/` directory is writable:
- Linux/Mac: `chmod -R 755 uploads/`

---

### Step 5 — Access the Application
| Page | URL |
|------|-----|
| Landing Page | http://localhost/rapidaid/ |
| User Login | http://localhost/rapidaid/auth/login.php |
| User Register | http://localhost/rapidaid/auth/register.php |
| Admin Login | http://localhost/rapidaid/auth/admin-login.php |
| Admin Dashboard | http://localhost/rapidaid/admin/dashboard.php |

---

### Default Credentials

**Admin Account:**
- Email: `admin@rapidaid.id`
- Password: `password`

**Demo User Accounts:**
- Email: `budi@example.com` / Password: `password`
- Email: `siti@example.com` / Password: `password`
- Email: `ahmad@example.com` / Password: `password`

> Note: Default passwords use Laravel's bcrypt hash for `password`. If login fails, register a new account or reset the password hash in the database to: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

---

### Folder Structure
```
rapidaid/
├── admin/              # Admin panel pages
│   ├── dashboard.php
│   ├── users.php
│   ├── ambulances.php
│   ├── drivers.php
│   ├── hospitals.php
│   ├── requests.php
│   ├── payments.php
│   ├── subscriptions.php
│   ├── reports.php
│   ├── _sidebar.php
│   └── _topbar.php
├── api/                # AJAX/API endpoints
├── assets/
│   ├── css/main.css    # Main stylesheet
│   └── js/main.js      # Main JavaScript
├── auth/               # Authentication pages
│   ├── login.php
│   ├── register.php
│   ├── admin-login.php
│   ├── forgot-password.php
│   └── logout.php
├── config/
│   └── database.php    # DB config & PDO class
├── includes/
│   └── helpers.php     # Helper functions
├── uploads/            # User-uploaded files
├── user/               # User dashboard pages
│   ├── dashboard.php
│   ├── request-ambulance.php
│   ├── track-request.php
│   ├── emergency-history.php
│   ├── subscriptions.php
│   ├── payment.php
│   ├── payments.php
│   ├── notifications.php
│   ├── profile.php
│   ├── _sidebar.php
│   └── _topbar.php
├── index.php           # Landing page
├── database.sql        # Full database schema + sample data
└── README.md           # This file
```

---

### Features
- ✅ Modern landing page (glassmorphism, dark/light mode, animations)
- ✅ User authentication (register, login, forgot password)
- ✅ Admin authentication
- ✅ User dashboard with subscription & request status
- ✅ Emergency ambulance request with live map (Leaflet.js)
- ✅ Pickup address lookup from coordinates (OpenStreetMap Nominatim API)
- ✅ Real-time ambulance tracking simulation
- ✅ Emergency history with search & export
- ✅ Subscription plans (Basic, Premium, Family)
- ✅ Payment system with proof upload
- ✅ Admin dashboard with Chart.js analytics
- ✅ Admin CRUD: Users, Ambulances, Drivers, Hospitals
- ✅ Admin emergency request assignment
- ✅ Admin payment confirmation
- ✅ Revenue & request reports with charts
- ✅ Notification system
- ✅ Dark mode / Light mode
- ✅ Fully responsive (Desktop, Tablet, Mobile)
- ✅ SOS button on landing page
- ✅ Export to CSV
- ✅ Print / Invoice support

### Tech Stack
- **Frontend**: HTML5, CSS3 (Glassmorphism, CSS Variables, Animations), JavaScript (ES6+)
- **Backend**: PHP 7.4+ (PDO, OOP)
- **Database**: MySQL 5.7+
- **Maps**: Leaflet.js + OpenStreetMap
- **Geocoding API**: OpenStreetMap Nominatim (reverse geocoding)
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Plus Jakarta Sans + Syne)

### Reverse Geocoding API
On the ambulance request page, **Use My Current Location** or a map click sends the selected coordinates to the RapidAid PHP endpoint, which requests a readable address and location details from OpenStreetMap Nominatim and returns JSON. JavaScript displays the returned area, city, region, postal code, country, and coordinates in the form. The page indicates loading, success, and error states, and offers a retry action after a failed lookup. The lookup is user-triggered; if it fails, the coordinates remain available as the pickup address.

For deployment, configure `APP_URL` in `config/database.php` to the real public application URL. The endpoint uses it to identify RapidAid in requests to Nominatim and applies a one-request-per-second throttle for this app on a single server. Coordinates are shared with the external geocoding provider when lookup is requested; review the provider's current usage policy before production or multi-server deployment.
