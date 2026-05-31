<?php
declare(strict_types=1);

/**
 * Shopping Cart Management (Session-based, no registration required)
 */

/**
 * Initialize cart in session if needed
 */
function cart_init(): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add product to cart (or increase quantity if already in cart)
 */
function add_to_cart(int $product_id, int $quantity = 1): bool
{
    cart_init();
    
    if ($product_id <= 0 || $quantity <= 0 || $quantity > 99) {
        return false;
    }

    // Get product to validate it exists
    $product = get_product_by_id($product_id);
    if (!$product) {
        return false;
    }

    // Check if already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $newQty = (int) $_SESSION['cart'][$product_id]['quantity'] + $quantity;
        if ($newQty > 99) {
            $newQty = 99;
        }
        $_SESSION['cart'][$product_id]['quantity'] = $newQty;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'name' => $product['name'],
            'price' => $product['price'],
        ];
    }

    return true;
}

/**
 * Remove product from cart
 */
function remove_from_cart(int $product_id): bool
{
    cart_init();
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }

    return false;
}

/**
 * Update quantity for a product in cart
 */
function update_cart_quantity(int $product_id, int $quantity): bool
{
    cart_init();
    
    if (!isset($_SESSION['cart'][$product_id])) {
        return false;
    }

    if ($quantity <= 0) {
        return remove_from_cart($product_id);
    }

    if ($quantity > 99) {
        $quantity = 99;
    }

    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    return true;
}

/**
 * Get entire cart
 */
function get_cart(): array
{
    cart_init();
    return array_values($_SESSION['cart']);
}

/**
 * Get cart count (number of items)
 */
function get_cart_count(): int
{
    cart_init();
    return count($_SESSION['cart']);
}

/**
 * Get cart total quantity (sum of all quantities)
 */
function get_cart_total_quantity(): int
{
    cart_init();
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += (int) $item['quantity'];
    }
    return $total;
}

/**
 * Get cart subtotal (before taxes/shipping)
 */
function get_cart_subtotal(): float
{
    cart_init();
    $total = 0.0;
    foreach ($_SESSION['cart'] as $item) {
        $total += (float) $item['price'] * (int) $item['quantity'];
    }
    return round($total, 2);
}

/**
 * Get cart total (alias for subtotal for now)
 */
function get_cart_total(): float
{
    return get_cart_subtotal();
}

/**
 * Clear entire cart
 */
function clear_cart(): void
{
    $_SESSION['cart'] = [];
}

/**
 * Check if product is in cart
 */
function is_in_cart(int $product_id): bool
{
    cart_init();
    return isset($_SESSION['cart'][$product_id]);
}

/**
 * Get quantity of product in cart
 */
function get_cart_item_quantity(int $product_id): int
{
    cart_init();
    return (int) ($_SESSION['cart'][$product_id]['quantity'] ?? 0);
}

/**
 * Validate cart before checkout (check inventory)
 */
function validate_cart_inventory(): array
{
    $errors = [];
    $cart = get_cart();

    foreach ($cart as $item) {
        $product_id = (int) $item['product_id'];
        $requested_qty = (int) $item['quantity'];

        $product = get_product_by_id($product_id);
        if (!$product) {
            $errors[] = sprintf('المنتج "%s" لم يعد متاحاً', $item['name']);
            continue;
        }

        $available = (int) $product['quantity_available'];
        if ($available < $requested_qty) {
            $errors[] = sprintf(
                'الكمية المطلوبة من "%s" غير متوفرة. المتوفر: %d فقط',
                $item['name'],
                $available
            );
        }
    }

    return $errors;
}
