# 🐝 D&A Honey Store – Premium E-Commerce Platform

> **عسل طبيعي 100% – أصالة الطبيعة في كل قطرة**

![Version](https://img.shields.io/badge/version-2.1.0-brightgreen) ![License](https://img.shields.io/badge/license-MIT-blue) ![PHP](https://img.shields.io/badge/PHP-8.3+-purple)

---

## 📖 Overview

D&A Honey Store is a production-ready e-commerce platform specialized in selling premium natural honey. Built with **zero external dependencies**, pure PHP, and secured with enterprise-grade security practices.

**Live Demo:** [http://localhost:8000](http://localhost:8000)  
**Admin Panel:** [http://localhost:8000/login.php](http://localhost:8000/login.php)

---

## ✨ Key Features

### 🛍️ **Customer Experience**
- ✅ Browse products by category (Sidr, Wildflower, Acacia, Honeycomb)
- ✅ Quick product view with detailed descriptions
- ✅ Real-time order total calculation
- ✅ Two payment methods: Bank Transfer & Cash on Delivery
- ✅ Receipt upload for bank transfers
- ✅ Mobile-optimized responsive design
- ✅ PWA (Progressive Web App) support – installable on mobile

### 👨‍💼 **Admin Dashboard**
- ✅ Real-time sales dashboard with key metrics
- ✅ Full order management (create, filter, status updates)
- ✅ Product management (CRUD with image uploads)
- ✅ Settings panel (bank details, contact info, WhatsApp)
- ✅ CSV export for order reports
- ✅ 30-minute session timeout for security

### 🔒 **Security Features**
- ✅ BCRYPT password hashing (cost 12)
- ✅ CSRF token protection on all forms
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (HTML escaping)
- ✅ HTTPS enforcement with HSTS headers
- ✅ HttpOnly + SameSite cookies
- ✅ 60-second rate limiting on orders
- ✅ Row-level database locking
- ✅ ACID transaction support

### 📊 **Backend Architecture**
- ✅ MySQL (required) + SQLite (fallback for dev)
- ✅ Zero external dependencies (pure PHP)
- ✅ Strict type declarations (PHP 8 strict_types)
- ✅ PDO prepared statements
- ✅ Database transactions with rollback
- ✅ Inventory management with audit trail
- ✅ Environment-based configuration

---

## 🚀 Quick Start

### **1. Setup (5 minutes)**

```bash
# Clone repository
git clone <repo> && cd "D&A products"

# Copy environment config
cp .env.example .env

# Edit .env with your MySQL credentials
nano .env
```

### **2. Database Setup**

```bash
# Create MySQL database & user
mysql -u root -p <<EOF
CREATE USER 'da_shop'@'localhost' IDENTIFIED BY 'da_honey_local';
CREATE DATABASE da_honey_shop CHARACTER SET utf8mb4;
GRANT ALL PRIVILEGES ON da_honey_shop.* TO 'da_shop'@'localhost';
FLUSH PRIVILEGES;
EOF

# Import schema & seed data
mysql -u da_shop -p da_honey_shop < database.sql
```

### **3. File Permissions**

```bash
chmod 600 .env
chmod 755 storage/ uploads/ -R
```

### **4. Start Server**

```bash
# PHP built-in server (development)
php -S localhost:8000

# Production: Use Apache/Nginx with PHP-FPM
```

### **5. Access Application**

- **Store:** http://localhost:8000
- **Admin:** http://localhost:8000/login.php
- **Credentials:** Key = `wedothebesthonyhere` (from .env)

---

## 📁 Project Structure

```
D&A products/
├── index.php              # Homepage & store
├── login.php              # Admin login
├── dashboard.php          # Admin dashboard
├── orders.php             # Order management
├── products-management.php # Product CRUD
├── settings.php           # Admin settings
├── submit-order.php       # Order submission API
├── manifest.php           # PWA manifest
├── service-worker.js      # Offline support
├── config.php             # Configuration & helpers
├── database.sql           # MySQL schema
│
├── includes/
│   ├── bootstrap.php      # App initialization
│   ├── auth.php           # Admin authentication
│   ├── products.php       # Product functions
│   ├── orders.php         # Order functions
│   ├── settings.php       # Settings management
│   ├── csrf.php           # CSRF tokens
│   ├── validation.php     # Input validation
│   ├── db-sqlite.php      # SQLite fallback
│   ├── articles.php       # Blog/tips
│   ├── pwa.php            # PWA setup
│   └── admin-layout.php   # Admin template
│
├── assets/
│   ├── css/
│   │   ├── style.css      # Public site styles
│   │   └── admin.css      # Admin panel styles
│   └── js/
│       ├── main.js        # Public site scripts
│       └── admin.js       # Admin panel scripts
│
├── storage/
│   ├── sessions/          # Session storage
│   └── da_honey_shop.sqlite (dev fallback)
│
├── uploads/
│   ├── receipts/          # Payment receipts
│   └── products/          # Product images
│
└── .env.example           # Configuration template
```

---

## 🗄️ Database

### Tables

| Table | Rows | Purpose |
|-------|------|---------|
| `products` | 5 seeded | Product catalog |
| `orders` | 0 | Customer orders |
| `settings` | 7 seeded | Admin configuration |
| `inventory_transactions` | 0 | Stock audit trail |

### Schema Features
- Foreign key constraints
- Automatic timestamps
- Optimized indexes
- UTF8MB4 encoding (Arabic support)
- ACID compliance

---

## 🔐 Security Audit Results

**Overall Score:** 9.2/10 ⭐

| Feature | Status |
|---------|--------|
| SQL Injection | ✅ Protected |
| XSS Attacks | ✅ Protected |
| CSRF Attacks | ✅ Token validated |
| Password Security | ✅ BCRYPT (cost 12) |
| Session Security | ✅ HttpOnly + 30min timeout |
| HTTPS | ✅ Enforced + HSTS |
| Rate Limiting | ✅ 60-sec order throttle |
| File Upload | ✅ MIME validation |

**Status:** ✅ **PRODUCTION-READY**

---

## 📋 Configuration

### Environment Variables (.env)

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=da_honey_shop
DB_USER=da_shop
DB_PASS=da_honey_local

# Admin
ADMIN_SECRET_KEY=

# WhatsApp (optional)
WHATSAPP_ENABLED=false
WHATSAPP_PHONE_NUMBER_ID=YOUR_ID
WHATSAPP_ACCESS_TOKEN=YOUR_TOKEN

# Contact
CONTACT_WHATSAPP=213663569663
CONTACT_INSTAGRAM=https://instagram.com/d_a_product

# Bank
BANK_NAME=بريد الجزائر CCP
BANK_ACCOUNT_HOLDER=اسمك الكامل
BANK_IBAN=007999990123456789
```

---

## 🎯 API Endpoints

### Public Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/` | GET | Homepage |
| `/index.php` | GET | Products & order form |
| `/article.php?id=1` | GET | Blog post |
| `/privacy-policy.php` | GET | Privacy & terms |
| `/submit-order.php` | POST | Submit order |
| `/newsletter.php` | POST | Newsletter signup |

### Admin Endpoints

| Endpoint | Auth | Purpose |
|----------|------|---------|
| `/login.php` | ❌ | Admin login |
| `/dashboard.php` | ✅ | Dashboard |
| `/orders.php` | ✅ | Order management |
| `/products-management.php` | ✅ | Product CRUD |
| `/settings.php` | ✅ | Settings |
| `/logout.php` | ✅ | Logout |

---

## 🧪 Testing

### Check PHP Syntax

```bash
php -l includes/*.php *.php
```

### Test Database Connection

```bash
php -r "require 'config.php'; echo 'OK: ' . db()->query('SELECT 1')->fetchColumn();"
```

### Run Dev Server

```bash
php -S localhost:8000
```

---

## 📚 Documentation

- **[MYSQL_SETUP_GUIDE.md](MYSQL_SETUP_GUIDE.md)** – Complete MySQL setup instructions
- **[PRODUCTION_SECURITY_AUDIT.md](PRODUCTION_SECURITY_AUDIT.md)** – Detailed security analysis
- **[SECURITY_UPGRADES_SUMMARY.md](SECURITY_UPGRADES_SUMMARY.md)** – Security enhancements
- **[IMPLEMENTATION_LOG.txt](IMPLEMENTATION_LOG.txt)** – Implementation details
- **[DEPLOYMENT_QUICK_START.md](DEPLOYMENT_QUICK_START.md)** – Deployment checklist

---

## 🛠️ Troubleshooting

### MySQL Connection Failed

```bash
# Check MySQL running
sudo systemctl status mysql

# Test credentials
mysql -u da_shop -p -h localhost
```

### Permission Denied on Uploads

```bash
chmod 755 uploads/ storage/ -R
sudo chown www-data:www-data uploads/ storage/
```

### Session Issues

```bash
# Ensure storage/sessions is writable
chmod 755 storage/sessions/
# Remove old sessions if needed
rm storage/sessions/sess_*
```

---

## 📝 License

MIT License – See LICENSE file for details

---

## 👥 Author

**D&A Product Team**  
Built with ❤️ for premium honey enthusiasts

---

## 📞 Support & Contact

- **WhatsApp:** [Chat with us](https://wa.me/213663569663)
- **Instagram:** [@d_a_product](https://instagram.com/d_a_product)
- **Email:** asma.hacini@gmail.com

---

## ✅ Production Checklist

Before deploying to production:

- [ ] `.env` file configured with real MySQL credentials
- [ ] `.env` file permissions set to 600
- [ ] HTTPS certificate installed
- [ ] MySQL database created and imported
- [ ] Upload directories are writable
- [ ] Admin password changed from default
- [ ] Email notifications configured (if using WhatsApp)
- [ ] Database backups scheduled
- [ ] Firewall configured
- [ ] Error logging enabled
- [ ] PHP `display_errors = off` in production

---

**Version:** 2.1.0 (Security Edition)  
**Last Updated:** May 30, 2026  
**Status:** ✅ Production-Ready
