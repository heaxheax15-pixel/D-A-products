<?php
declare(strict_types=1);

/**
 * Low Stock Alert Utilities
 */

/**
 * Get low stock threshold from settings (default: 10)
 */
function get_low_stock_threshold(): int
{
    $threshold = setting('low_stock_threshold');
    if ($threshold === null) {
        return 10;
    }
    return max(1, (int) $threshold);
}

/**
 * Get list of products with low stock
 */
function get_low_stock_products(): array
{
    try {
        $threshold = get_low_stock_threshold();
        $stmt = db()->prepare('
            SELECT id, name, slug, price, quantity_available
            FROM products
            WHERE is_active = 1 AND quantity_available <= ?
            ORDER BY quantity_available ASC
        ');
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Check if product is low stock
 */
function is_low_stock(int $product_id): bool
{
    try {
        $threshold = get_low_stock_threshold();
        $stmt = db()->prepare('SELECT quantity_available FROM products WHERE id = ?');
        $stmt->execute([$product_id]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        return (int) $row['quantity_available'] <= $threshold;
    } catch (Throwable) {
        return false;
    }
}

/**
 * Get count of low stock products
 */
function count_low_stock_products(): int
{
    $products = get_low_stock_products();
    return count($products);
}
