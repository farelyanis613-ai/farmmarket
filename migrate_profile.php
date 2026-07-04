<?php
// Migration pour ajouter phone et address aux users

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=farmmarket;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add phone and address to users
    $columns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('phone', $columns)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN phone VARCHAR(20)');
        echo "✓ Colonne phone ajoutée\n";
    }
    
    if (!in_array('address', $columns)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN address TEXT');
        echo "✓ Colonne address ajoutée\n";
    }
    
    // Ensure status column exists in orders with proper values
    $columns = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('status', $columns)) {
        $pdo->exec('UPDATE orders SET status = "pending" WHERE status = "En attente"');
        echo "✓ Statuts normalisés\n";
    }
    
    echo "✓ Migration complétée\n";
    
} catch (PDOException $e) {
    echo "Info: " . $e->getMessage() . "\n";
}
