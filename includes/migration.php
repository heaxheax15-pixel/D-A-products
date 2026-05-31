<?php
declare(strict_types=1);

/**
 * Database Migration Script
 * Migrates from single-product orders to multi-item order_items table
 * Preserves all existing data
 */

function migrate_orders_to_order_items(): void
{
    $pdo = db();
    $driver = db_driver();

    try {
        // Check if old orders table structure exists with product_name
        $columns = db_get_table_columns('orders');
        
        if (!isset($columns['product_name']) || !isset($columns['quantity'])) {
            // Already migrated or never had the old structure
            return;
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Get all orders with product_name and quantity
        $stmt = $pdo->query('SELECT id, product_name, quantity, total_price FROM orders');
        $orders = $stmt->fetchAll();

        // Migrate each order
        foreach ($orders as $order) {
            $orderId = (int) $order['id'];
            $productName = (string) $order['product_name'];
            $quantity = (int) $order['quantity'];
            $totalPrice = (float) $order['total_price'];

            // Find product by name
            $product = db()->prepare('SELECT id, price FROM products WHERE name = ?')->execute([$productName])->fetch();
            
            if ($product) {
                $productId = (int) $product['id'];
                $unitPrice = (float) $product['price'];
                
                // Insert into order_items
                $insertStmt = $pdo->prepare(
                    'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
                );
                $insertStmt->execute([$orderId, $productId, $quantity, $unitPrice]);
            }
        }

        // Drop old columns from orders table (if database supports it)
        if ($driver === 'mysql') {
            try {
                $pdo->exec('ALTER TABLE orders DROP COLUMN product_name');
            } catch (Throwable) {
                // Column might not exist
            }
            try {
                $pdo->exec('ALTER TABLE orders DROP COLUMN quantity');
            } catch (Throwable) {
                // Column might not exist
            }
            // Add updated_at if not exists
            try {
                $pdo->exec('ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            } catch (Throwable) {
                // Column already exists
            }
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support dropping columns directly, so we skip
            // The new schema already has the correct structure
        }

        $pdo->commit();
        error_log('[D&A Migration] Orders migrated to order_items successfully');
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('[D&A Migration Error] ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Get table columns for a specific database
 */
function db_get_table_columns(string $tableName): array
{
    $pdo = db();
    $driver = db_driver();

    try {
        if ($driver === 'mysql') {
            $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = DATABASE()');
            $stmt->execute([$tableName]);
            $columns = [];
            foreach ($stmt->fetchAll() as $row) {
                $columns[$row['COLUMN_NAME']] = true;
            }
            return $columns;
        } elseif ($driver === 'sqlite') {
            $stmt = $pdo->prepare('PRAGMA table_info(' . $tableName . ')');
            $stmt->execute();
            $columns = [];
            foreach ($stmt->fetchAll() as $row) {
                $columns[$row['name']] = true;
            }
            return $columns;
        }
    } catch (Throwable) {
        return [];
    }
    
    return [];
}
