<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

// Migration: Add delivery_address column to orders table

require_once __DIR__ . '/config/database.php';

try {
    if (!columnExists($pdo, 'orders', 'delivery_address')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN delivery_address TEXT AFTER delivery_fee');
        echo "✓ Column 'delivery_address' added to 'orders' table";
    } else {
        echo "✓ Column 'delivery_address' already exists in 'orders' table";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}

function columnExists($pdo, $table, $column)
{
    $stmt = $pdo->prepare('SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return $stmt->rowCount() > 0;
}
?>
