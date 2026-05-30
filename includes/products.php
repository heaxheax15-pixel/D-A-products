<?php
declare(strict_types=1);

function default_products_seed(): array
{
    return [
        ['id' => 1, 'name' => 'عسل سدر جبلي', 'slug' => 'sidr-mountain', 'description' => 'عسل سدر جبلي أصيل من مرتفعات الجنوب، غني النكهة وقوامه متوازن. مثالي للاستخدام اليومي ولتعزيز المناعة.', 'price' => 500.00, 'image' => 'images/product-sidr.webp', 'category' => 'sidr', 'is_bestseller' => 1, 'is_featured' => 1, 'is_active' => 1, 'rating' => 5],
        ['id' => 2, 'name' => 'عسل زهرة البرتقالة', 'slug' => 'wildflower', 'description' => 'مزيج رائع من رحيق الزهور البرية، لون ذهبي فاتح ونكهة متوازنة تناسب جميع أفراد العائلة.', 'price' => 350.00, 'image' => 'images/product-wildflower.webp', 'category' => 'flowers', 'is_bestseller' => 0, 'is_featured' => 0, 'is_active' => 1, 'rating' => 5],
        ['id' => 3, 'name' => 'عسل طلح جبلي', 'slug' => 'talh-mountain', 'description' => 'عسل طلح جبلي نادر، نكهة قوية ولون كهرماني داكن — من أجود أنواع العسل المحلي.', 'price' => 450.00, 'image' => 'images/product-acacia.webp', 'category' => 'talh', 'is_bestseller' => 0, 'is_featured' => 0, 'is_active' => 1, 'rating' => 5],
        ['id' => 4, 'name' => 'عسل أكاسيا', 'slug' => 'acacia', 'description' => 'عسل أكاسيا خفيف القوام، يذوب بسرعة ولا يتبلور بسهولة. الخيار الأمثل لمحبي الشاي والمخبوزات.', 'price' => 380.00, 'image' => 'images/product-acacia.webp', 'category' => 'flowers', 'is_bestseller' => 0, 'is_featured' => 0, 'is_active' => 1, 'rating' => 4],
        ['id' => 5, 'name' => 'عسل براح طبيعي', 'slug' => 'natural-comb', 'description' => 'قطع عسل الشهد الطبيعي مع العسل السائل – تجربة أصيلة وطبيعية 100%.', 'price' => 600.00, 'image' => 'images/product-comb.webp', 'category' => 'comb', 'is_bestseller' => 0, 'is_featured' => 0, 'is_active' => 1, 'rating' => 5],
    ];
}

/** يضمن عمود is_active في جداول MySQL القديمة حتى لا تفشل واجهة المتجر. */
function products_ensure_schema(PDO $pdo): void
{
    static $checked = false;
    if ($checked || db_driver() === 'sqlite') {
        return;
    }
    $checked = true;
    try {
        $pdo->query('SELECT is_active FROM products LIMIT 1');
    } catch (Throwable $e) {
        try {
            $pdo->exec('ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1');
        } catch (Throwable $alter) {
            error_log('[D&A] products schema: ' . $alter->getMessage());
        }
    }
}

function products_filter_active(array $rows, bool $activeOnly): array
{
    if (!$activeOnly) {
        return $rows;
    }
    return array_values(array_filter($rows, static function (array $p): bool {
        if (!array_key_exists('is_active', $p)) {
            return true;
        }
        return (int) $p['is_active'] === 1;
    }));
}

function get_products(bool $activeOnly = true): array
{
    try {
        $pdo = db();
        products_ensure_schema($pdo);

        $sql = 'SELECT * FROM products ORDER BY sort_order ASC, id ASC';
        $rows = $pdo->query($sql)->fetchAll();

        if (count($rows) === 0 && db_driver() === 'sqlite') {
            return default_products_seed();
        }

        return products_filter_active($rows, $activeOnly);
    } catch (Throwable $e) {
        error_log('[D&A] products: ' . $e->getMessage());
        return [];
    }
}

function get_product_by_id(int $id): ?array
{
    foreach (get_products(false) as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function get_product_by_name(string $name): ?array
{
    foreach (get_products() as $p) {
        if ($p['name'] === $name) {
            return $p;
        }
    }
    return null;
}

function get_featured_product(): ?array
{
    foreach (get_products() as $p) {
        if (!empty($p['is_featured'])) {
            return $p;
        }
    }
    $all = get_products();
    return $all[0] ?? null;
}

function product_image_url(array $product): string
{
    $img = $product['image'] ?? '';
    if ($img !== '' && strpos($img, 'uploads/') === 0) {
        return $img;
    }
    return $img ?: 'images/pro4.webp';
}