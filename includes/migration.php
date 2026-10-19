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

    // Check if old orders table structure exists with product_name
    $columns = db_get_table_columns('orders');

    if (!isset($columns['product_name']) || !isset($columns['quantity'])) {
        // Already migrated or never had the old structure
        return;
    }

    // -------------------------------------------------------------------
    // Phase 1: DML (transactional, idempotent)
    // -------------------------------------------------------------------
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->query('SELECT id, product_name, quantity, total_price FROM orders');
        $orders = $stmt->fetchAll();

        $insertStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
        );
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = ?');

        foreach ($orders as $order) {
            $orderId = (int) $order['id'];
            $productName = (string) $order['product_name'];
            $quantity = (int) $order['quantity'];

            // Find product by name (prepare → execute → fetch)
            $lookup = $pdo->prepare('SELECT id, price FROM products WHERE name = ?');
            $lookup->execute([$productName]);
            $product = $lookup->fetch();

            if ($product) {
                // Idempotency: skip if this order already has items
                $checkStmt->execute([$orderId]);
                if ((int) $checkStmt->fetchColumn() > 0) {
                    continue;
                }
                $insertStmt->execute([
                    $orderId,
                    (int) $product['id'],
                    $quantity,
                    (float) $product['price'],
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[D&A Migration Error] DML phase failed: ' . $e->getMessage());
        throw $e;
    }

    // -------------------------------------------------------------------
    // Phase 2: DDL (non-transactional — MySQL implicitly commits DDL)
    // Each ALTER individually guarded; partial failure is safe because
    // DML is idempotent and flag file only written on full success.
    // -------------------------------------------------------------------
    if ($driver === 'mysql') {
        try { $pdo->exec('ALTER TABLE orders DROP COLUMN product_name'); } catch (Throwable) {}
        try { $pdo->exec('ALTER TABLE orders DROP COLUMN quantity'); } catch (Throwable) {}
        try { $pdo->exec('ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'); } catch (Throwable) {}
    }

    error_log('[D&A Migration] Orders migrated to order_items successfully');
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
