# 🗄️ MySQL Setup & Deployment Guide

**Project:** D&A Honey Store v2.1.0  
**Database:** MySQL 5.7+ (Required)  
**Status:** ✅ Production-Ready

---

## 📋 Prerequisites

- MySQL Server 5.7+ or MySQL 8.0+
- PHP 8.0+ with `php-mysql` extension
- Web server (Apache/Nginx) with PHP support
- SSH or terminal access to server

---

## 🚀 Quick Setup (5 minutes)

### Step 1: Create MySQL User & Database

```bash
mysql -u root -p
```

Then execute:

```sql
CREATE USER 'da_shop'@'localhost' IDENTIFIED BY 'da_honey_local';
CREATE DATABASE da_honey_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON da_honey_shop.* TO 'da_shop'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Import Database Schema

```bash
cd /path/to/D&A\ products
mysql -u da_shop -p da_honey_shop < database.sql
# Enter password: da_honey_local
```

### Step 3: Configure .env File

```bash
cp .env.example .env
nano .env
```

Update these fields for your MySQL setup:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=da_honey_shop
DB_USER=da_shop
DB_PASS=da_honey_local
DB_CHARSET=utf8mb4

# Admin Authentication
ADMIN_SECRET_KEY=A&DBOUTIQUE21

# WhatsApp Integration (optional)
WHATSAPP_ENABLED=false
WHATSAPP_PHONE_NUMBER_ID=YOUR_PHONE_NUMBER_ID
WHATSAPP_ACCESS_TOKEN=YOUR_PERMANENT_ACCESS_TOKEN

# Contact Information
CONTACT_WHATSAPP=213663569663
CONTACT_INSTAGRAM=https://instagram.com/d_a_product
CONTACT_TIKTOK=https://tiktok.com/@asma_hasin

# Bank Information
BANK_NAME=بريد الجزائر CCP
BANK_ACCOUNT_HOLDER=اسمك الكامل
BANK_IBAN=007999990123456789

# Site Configuration
SITE_NAME=D&A Product
SITE_TAGLINE=عسل طبيعي 100% – أصالة في كل قطرة
```

### Step 4: Set Permissions

```bash
chmod 600 .env
chmod 755 storage/
chmod 755 storage/sessions/
chmod 755 uploads/
chmod 755 uploads/receipts/
chmod 755 uploads/products/
```

### Step 5: Verify Connection

```bash
php -r "require_once 'config.php'; echo 'Connected: ' . db()->query('SELECT 1')->fetchColumn();"
```

Expected output: `Connected: 1`

---

## ✅ Verification Checklist

After setup, verify each step:

- [ ] MySQL database created
- [ ] Database user created with proper privileges
- [ ] `database.sql` imported successfully
- [ ] `.env` file configured with correct credentials
- [ ] `.env` file permissions set to 600
- [ ] Upload directories exist and are writable
- [ ] PHP can connect to MySQL (verification command above)

---

## 🔒 Security Best Practices

### 1. Change Admin Password

```php
// First login uses ADMIN_SECRET_KEY from .env
// Update in .env:
ADMIN_SECRET_KEY=MySecurePassword123!@#
```

### 2. Use HTTPS in Production

The application automatically redirects to HTTPS. Ensure:
- SSL certificate is installed
- `enforce_https()` is working (check bootstrap.php)
- `.htaccess` has proper headers

### 3. Protect .env File

```bash
# Make sure .env is not accessible via web
# Add to .htaccess or Nginx config:
<FilesMatch "\.env">
    Require all denied
</FilesMatch>
```

### 4. Regular Backups

```bash
# Daily MySQL backup
mysqldump -u da_shop -p da_honey_shop > backup_$(date +%Y%m%d).sql

# Restore from backup
mysql -u da_shop -p da_honey_shop < backup_20260530.sql
```

---

## 📊 Database Schema Overview

### Tables

| Table | Purpose |
|-------|---------|
| `orders` | Customer orders (name, phone, address, payment) |
| `products` | Product catalog (name, price, category, images) |
| `settings` | Configuration (bank, contact, social media) |
| `inventory_transactions` | Audit trail for stock changes |

### Key Indexes

- `orders.status` - Fast filtering by order status
- `orders.customer_phone` - Search by phone number
- `products.category` - Product filtering
- `products.is_active` - Show/hide products

---

## 🛠️ Common Tasks

### Update Product Prices

```sql
UPDATE products SET price = 450.00 WHERE slug = 'sidr-mountain';
```

### Change Bank Details

Admin Panel → Settings → Update IBAN/Account Holder

Or directly via SQL:

```sql
UPDATE settings SET setting_value = 'new_iban_number' 
WHERE setting_key = 'bank_iban';
```

### Export Orders

```sql
SELECT id, customer_name, customer_phone, product_name, 
       total_price, status, created_at 
FROM orders 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
INTO OUTFILE '/tmp/orders_export.csv'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' 
LINES TERMINATED BY '\n';
```

---

## 🚨 Troubleshooting

### Error: "Database connection failed"

**Cause:** MySQL credentials incorrect or server not running

**Solution:**
```bash
# Check MySQL is running
sudo systemctl status mysql

# Verify credentials in .env
mysql -u da_shop -p -h localhost -e "SELECT 1"

# Check PHP MySQL extension
php -m | grep -i mysql
```

### Error: "Table doesn't exist"

**Cause:** `database.sql` not imported

**Solution:**
```bash
mysql -u da_shop -p da_honey_shop < database.sql
```

### Error: "Permission denied" on uploads

**Cause:** Directory permissions incorrect

**Solution:**
```bash
chmod 755 uploads/
chmod 755 storage/
sudo chown www-data:www-data uploads/ storage/ -R
```

---

## 🔄 Upgrade from SQLite

If migrating from SQLite:

```bash
# Export SQLite data
sqlite3 storage/da_honey_shop.sqlite ".dump" > sqlite_dump.sql

# Create MySQL tables
mysql -u da_shop -p da_honey_shop < database.sql

# Import data (manual mapping may be needed)
```

---

## 📞 Support

For issues or questions:
- Check PRODUCTION_SECURITY_AUDIT.md for security details
- Review IMPLEMENTATION_LOG.txt for all changes
- Run `php -l *.php` to check for syntax errors

---

## ✨ Features Enabled with MySQL

✅ Full inventory management with stock tracking  
✅ ACID transaction support for orders  
✅ Audit trail for inventory changes  
✅ Row-level locking for concurrent orders  
✅ Advanced indexes for performance  
✅ Prepared statements for security  

---

**Last Updated:** May 30, 2026  
**Status:** ✅ Production-Ready
