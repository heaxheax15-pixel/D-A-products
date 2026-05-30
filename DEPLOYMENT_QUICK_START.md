# 🚀 Quick Start Deployment Guide

> **Status:** ✅ All 5 security upgrades ready for production
> **Version:** v2.1.0 (Security Edition)
> **Last Updated:** May 29, 2026

## 1️⃣ Environment Configuration (5 minutes)

### Step 1: Create .env file
```bash
cp .env.example .env
```

### Step 2: Edit with your credentials
```bash
nano .env
# Edit these sections:
# - DB_HOST, DB_USER, DB_PASS
# - ADMIN_SECRET_KEY
# - WHATSAPP credentials (if using)
# - BANK details
# - CONTACT information
```

### Step 3: Secure .env (CRITICAL!)
```bash
chmod 600 .env
echo ".env" >> .gitignore
git add .gitignore && git commit -m "chore: prevent .env exposure"
```

---

## 2️⃣ Database Migration (5 minutes)

### MySQL Deployment:
```bash
# Method 1: Using SQL file
mysql -u root -p da_honey_shop < database.sql

# Method 2: Manual SQL
mysql -u root -p da_honey_shop << EOF
ALTER TABLE products ADD COLUMN quantity_available INT NOT NULL DEFAULT 0 AFTER sort_order;
CREATE TABLE inventory_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  order_id INT NULL,
  quantity_change INT NOT NULL,
  transaction_type VARCHAR(30) DEFAULT 'order',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_product (product_id),
  INDEX idx_order (order_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);
EOF
```

### SQLite (automatic on first use):
```bash
# No action needed - schema updates automatically via db-sqlite.php
```

---

## 3️⃣ HTTPS Configuration (10 minutes)

### Nginx Configuration:
```nginx
# /etc/nginx/sites-available/yourdomain.com

# HTTP → HTTPS redirect
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/yourdomain/public_html;
    index index.php;

    ssl_certificate /path/to/cert.crt;
    ssl_certificate_key /path/to/key.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### Apache Configuration:
```apache
# Enable SSL module
a2enmod ssl
a2enmod rewrite

# Create VirtualHost config
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/yourdomain/public_html

    SSLEngine on
    SSLCertificateFile /path/to/cert.crt
    SSLCertificateKeyFile /path/to/key.key

    <Directory /var/www/yourdomain>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Verify Configuration:
```bash
# Reload web server
sudo systemctl reload nginx  # or 'apache2' for Apache

# Test HTTPS redirect
curl -I http://yourdomain.com
# Should return: HTTP/1.1 301 Moved Permanently
# Location: https://yourdomain.com

# Verify HSTS header
curl -I https://yourdomain.com | grep -i "strict"
# Should return: Strict-Transport-Security: max-age=31536000...
```

---

## 4️⃣ File Permissions (2 minutes)

```bash
# Secure session directory
chmod 700 storage/sessions/
chmod 700 storage/

# Ensure .env is readable only by PHP
chmod 600 .env

# Ensure upload directories are writable
chmod 755 uploads/receipts/
chmod 755 uploads/products/

# Verify PHP can read/write
ls -la storage/ uploads/
```

---

## 5️⃣ Verification & Testing (10 minutes)

### Syntax Check:
```bash
php -l config.php
php -l includes/auth.php
php -l includes/bootstrap.php
php -l submit-order.php
# All should return: No syntax errors detected
```

### Database Connection:
```bash
php -r "define('DA_APP', true); require 'includes/bootstrap.php'; 
        echo 'DB Driver: ' . db_driver() . '\n'; 
        echo 'Connection: OK\n';"
```

### Admin Panel Test:
1. Navigate to: `https://yourdomain.com/login.php`
2. Enter `ADMIN_SECRET_KEY` from `.env`
3. Click "دخول" (Login)
4. ✅ Should redirect to dashboard
5. Password is automatically hashed on first login

### Session Timeout Test:
1. Login to admin panel
2. Leave the page idle for 30+ minutes
3. Refresh the page
4. ✅ Should redirect to login with message: "انتهت صلاحية جلستك بسبب عدم النشاط"

### Rate Limiting Test:
1. Submit an order
2. Immediately try to submit another order
3. ✅ Should see: "يرجى الانتظار X ثواني قبل تقديم طلب جديد."
4. Wait 60 seconds
5. ✅ Order submission should work

### Inventory Test:
1. Check product quantity: `SELECT quantity_available FROM products WHERE id = 1;`
2. Submit an order (qty: 5)
3. Check again: `SELECT quantity_available FROM products WHERE id = 1;`
4. ✅ Quantity should be decreased by 5

---

## 6️⃣ Monitoring & Logs

### Check Application Logs:
```bash
# PHP error log
tail -f /var/log/php-errors.log

# Web server access log
tail -f /var/log/nginx/access.log  # Nginx
tail -f /var/log/apache2/access.log  # Apache

# Application-specific logs (if enabled)
grep "\[D&A\]" /var/log/php-errors.log
```

### Audit Trail:
```bash
# View inventory transactions
mysql da_honey_shop -u root -p -e "SELECT * FROM inventory_transactions ORDER BY created_at DESC LIMIT 10;"

# View admin session logs (via application)
# Check $GLOBALS['da_settings'] or session table if implemented
```

---

## 🆘 Troubleshooting

### Issue: HTTPS redirect creates infinite loop
**Solution:** 
- Check that SSL certificate is valid
- Verify proxy headers are correct
- For reverse proxy: ensure `X-Forwarded-Proto: https` is set

### Issue: Admin login always fails
**Solution:**
- Verify `.env` file exists and is readable
- Confirm `ADMIN_SECRET_KEY` is correct
- Check database `settings` table has `admin_password_hash` row
- On first login, password is hashed automatically

### Issue: Orders rejected "الكمية غير متوفرة"
**Solution:**
- Verify `products.quantity_available` column exists
- Run: `ALTER TABLE products ADD COLUMN quantity_available INT NOT NULL DEFAULT 0;`
- Set initial inventory: `UPDATE products SET quantity_available = 100;`

### Issue: Session timeout not working
**Solution:**
- Verify `includes/bootstrap.php` is included on admin pages
- Check that admin pages are in the list (line ~98 of bootstrap.php)
- Clear session files: `rm -f storage/sessions/sess_*`
- Restart PHP-FPM

### Issue: Rate limiting doesn't work
**Solution:**
- Check `$_SESSION['last_order_time']` is being set
- Verify session is not being destroyed between requests
- Clear old session files if corrupted

---

## 📊 Security Checklist

Before going live:
- [ ] HTTPS configured and working
- [ ] HSTS header present (check curl -I)
- [ ] `.env` file created with production credentials
- [ ] `.env` added to `.gitignore`
- [ ] `.env` file permissions: 600
- [ ] Database migrated with `quantity_available` column
- [ ] Admin panel login tested (first-time password hashing)
- [ ] Session timeout tested (30 minutes)
- [ ] Rate limiting tested (60 seconds)
- [ ] Order inventory decrement verified
- [ ] All PHP files syntax checked
- [ ] Error logging configured
- [ ] Database backups automated

---

## 📞 Support

For issues or questions:
1. Check logs in `/var/log/`
2. Review error messages in browser console
3. Test database connection: `php -r "require 'config.php'; db();"`
4. Verify file permissions: `ls -la`

---

**Deployment Ready!** 🚀
