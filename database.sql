-- D&A Product v2 – قاعدة البيانات الكاملة
-- mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS da_honey_shop
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE da_honey_shop;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(255) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  customer_address TEXT NOT NULL,
  notes TEXT NULL,
  product_name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  total_price DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(30) NOT NULL,
  receipt_path VARCHAR(500) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_phone (customer_phone),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ترقية من v1 (نفّذ يدوياً إن وُجد الجدول قديماً):
-- ALTER TABLE orders ADD COLUMN receipt_path VARCHAR(500) NULL AFTER payment_method;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(500) NOT NULL DEFAULT 'images/pro4.webp',
  category VARCHAR(30) NOT NULL DEFAULT 'flowers',
  is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_category (category),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (name, slug, description, price, image, category, is_bestseller, is_featured, sort_order) VALUES
('عسل سدر جبلي', 'sidr-mountain', 'عسل سدر جبلي أصيل من مرتفعات الجنوب، غني النكهة وقوامه متوازن.', 120.00, 'images/product-sidr.webp', 'sidr', 1, 1, 1),
('عسل زهور برية', 'wildflower', 'مزيج من رحيق الزهور البرية، لون ذهبي فاتح ونكهة متوازنة.', 85.00, 'images/product-wildflower.webp', 'flowers', 0, 0, 2),
('عسل أكاسيا', 'acacia', 'عسل أكاسيا خفيف القوام، مثالي للشاي والمخبوزات.', 95.00, 'images/product-acacia.webp', 'flowers', 0, 0, 3),
('عسل براح طبيعي', 'natural-comb', 'قطع عسل الشهد الطبيعي مع العسل السائل – تجربة أصيلة.', 150.00, 'images/product-comb.webp', 'comb', 0, 0, 4)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO settings (setting_key, setting_value) VALUES
('bank_name', 'البنك الأهلي السعودي'),
('bank_holder', 'D&A Product'),
('bank_iban', 'SA00 0000 0000 0000 0000 0000'),
('contact_whatsapp', '966500000000'),
('whatsapp_greeting', 'مرحباً، أريد الاستفسار عن العسل'),
('contact_instagram', 'https://instagram.com/da_product'),
('contact_tiktok', 'https://tiktok.com/@da_product')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
