<?php
declare(strict_types=1);

function default_products_seed(): array
{
    return [
        ['id' => 1, 'name' => 'عسل سدر جبلي', 'slug' => 'sidr-mountain', 'description' => 'عسل سدر جبلي أصيل من مرتفعات الجنوب، غني النكهة وقوامه متوازن. مثالي للاستخدام اليومي ولتعزيز المناعة.', 'price' => 120.00, 'image' => 'images/product-sidr.webp', 'category' => 'sidr', 'is_bestseller' => 1, 'is_featured' => 1, 'rating' => 5],
        ['id' => 2, 'name' => 'عسل زهور برية', 'slug' => 'wildflower', 'description' => 'مزيج رائع من رحيق الزهور البرية، لون ذهبي فاتح ونكهة متوازنة تناسب جميع أفراد العائلة.', 'price' => 85.00, 'image' => 'images/product-wildflower.webp', 'category' => 'flowers', 'is_bestseller' => 0, 'is_featured' => 0, 'rating' => 5],
        ['id' => 3, 'name' => 'عسل طلح جبلي', 'slug' => 'talh-mountain', 'description' => 'عسل طلح جبلي نادر، نكهة قوية ولون كهرماني داكن — من أجود أنواع العسل المحلي.', 'price' => 110.00, 'image' => 'images/product-acacia.webp', 'category' => 'talh', 'is_bestseller' => 0, 'is_featured' => 0, 'rating' => 5],
        ['id' => 5, 'name' => 'عسل أكاسيا', 'slug' => 'acacia', 'description' => 'عسل أكاسيا خفيف القوام، يذوب بسرعة ولا يتبلور بسهولة. الخيار الأمثل لمحبي الشاي والمخبوزات.', 'price' => 95.00, 'image' => 'images/product-acacia.webp', 'category' => 'flowers', 'is_bestseller' => 0, 'is_featured' => 0, 'rating' => 4],
        ['id' => 4, 'name' => 'عسل براح طبيعي', 'slug' => 'natural-comb', 'description' => 'قطع عسل الشهد الطبيعي مع العسل السائل – تجربة أصيلة كما في المنحل، غنية بالإنزيمات.', 'price' => 150.00, 'image' => 'images/product-comb.webp', 'category' => 'comb', 'is_bestseller' => 0, 'is_featured' => 0, 'rating' => 5],
    ];
}

function get_products(bool $activeOnly = true): array
{
    try {
        $sql = 'SELECT * FROM products';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $rows = db()->query($sql)->fetchAll();
        if ($rows) {
            return $rows;
        }
    } catch (Throwable $e) {
        error_log('[D&A] products: ' . $e->getMessage());
    }
    return default_products_seed();
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
