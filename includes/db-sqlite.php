<?php
declare(strict_types=1);

function db_sqlite_path(): string
{
    return APP_ROOT . '/storage/da_honey_shop.sqlite';
}

function db_sqlite_connect(): PDO
{
    $path = db_sqlite_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    db_sqlite_bootstrap($pdo);
    return $pdo;
}

function db_sqlite_bootstrap(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            customer_address TEXT NOT NULL,
            notes TEXT NULL,
            product_name TEXT NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            total_price REAL NOT NULL,
            payment_method TEXT NOT NULL,
            receipt_path TEXT NULL,
            status TEXT NOT NULL DEFAULT \'pending\',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            image TEXT NOT NULL DEFAULT \'images/pro4.webp\',
            category TEXT NOT NULL DEFAULT \'flowers\',
            is_bestseller INTEGER NOT NULL DEFAULT 0,
            is_featured INTEGER NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            sort_order INTEGER NOT NULL DEFAULT 0,
            quantity_available INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            setting_key TEXT PRIMARY KEY,
            setting_value TEXT NOT NULL
        )'
    );
// Migration: quantity_available
    try {
        $pdo->exec('ALTER TABLE products ADD COLUMN quantity_available INTEGER NOT NULL DEFAULT 0');
    } catch (Throwable) {
        // العمود موجود مسبقاً
    }
    $count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $products = [
        ['عسل سدر جبلي', 'sidr-mountain', 'عسل سدر جبلي أصيل من مرتفعات الجنوب، غني النكهة وقوامه متوازن. مثالي للاستخدام اليومي ولتعزيز المناعة.', 500.00, 'images/product-sidr.webp', 'sidr', 1, 1, 1],
        ['عسل زهرة البرتقالة', 'wildflower', 'مزيج رائع من رحيق الزهور البرية، لون ذهبي فاتح ونكهة متوازنة تناسب جميع أفراد العائلة.', 350.00, 'images/product-wildflower.webp', 'flowers', 0, 0, 2],
        ['عسل طلح جبلي', 'talh-mountain', 'عسل طلح جبلي نادر، نكهة قوية ولون كهرماني داكن — من أجود أنواع العسل المحلي.', 450.00, 'images/product-acacia.webp', 'talh', 0, 0, 3],
        ['عسل أكاسيا', 'acacia', 'عسل أكاسيا خفيف القوام، يذوب بسرعة ولا يتبلور بسهولة. الخيار الأمثل لمحبي الشاي والمخبوزات.', 380.00, 'images/product-acacia.webp', 'flowers', 0, 0, 4],
        ['عسل براح طبيعي', 'natural-comb', 'قطع عسل الشهد الطبيعي مع العسل السائل – تجربة أصيلة وطبيعية 100%.', 600.00, 'images/product-comb.webp', 'comb', 0, 0, 5],
    ];
    $stmt = $pdo->prepare(
        'INSERT INTO products (name, slug, description, price, image, category, is_bestseller, is_featured, sort_order)
         VALUES (?,?,?,?,?,?,?,?,?)'
    );
    foreach ($products as $p) {
        $stmt->execute($p);
    }

    $settings = [
        ['bank_name', 'بريد الجزائر CCP'],
        ['bank_holder', 'اسمك الكامل'],
        ['bank_iban', '007999990123456789'],
        ['contact_whatsapp', '213663569663'],
        ['whatsapp_greeting', 'مرحباً، أريد الاستفسار عن العسل'],
        ['contact_instagram', 'https://instagram.com/d_a_product'],
        ['contact_tiktok', 'https://tiktok.com/@asma.hacini'],
    ];
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($settings as $s) {
        $stmt->execute($s);
    }
}
