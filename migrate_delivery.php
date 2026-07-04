<?php

// Database migration script for delivery and farmer support

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=farmmarket;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add farmer_id to products
    $columns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('farmer_id', $columns)) {
        $pdo->exec('ALTER TABLE products ADD COLUMN farmer_id INT AFTER category_id');
        $pdo->exec('ALTER TABLE products ADD FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE');
        echo "✓ Colonne farmer_id ajoutée à products\n";
    }
    
    // Add delivery_id to orders
    if (!in_array('delivery_id', $columns = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN))) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN delivery_id INT');
        $pdo->exec('ALTER TABLE orders ADD FOREIGN KEY (delivery_id) REFERENCES users(id) ON DELETE SET NULL');
        echo "✓ Colonne delivery_id ajoutée à orders\n";
    }

    // Add failed_reason to orders
    if (!in_array('failed_reason', $columns)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN failed_reason TEXT NULL AFTER status');
        echo "✓ Colonne failed_reason ajoutée à orders\n";
    }
    
    // Create delivery_status_history table
    $tables = $pdo->query("SHOW TABLES LIKE 'delivery_status_history'")->fetchAll();
    if (empty($tables)) {
        $pdo->exec('CREATE TABLE delivery_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            status VARCHAR(50) NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )');
        echo "✓ Table delivery_status_history créée\n";
    }
    
} catch (PDOException $e) {
    echo "Info: " . $e->getMessage() . "\n";
}
