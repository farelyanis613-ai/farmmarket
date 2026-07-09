<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=farmmarket;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if columns already exist before adding
    $columns = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('delivery_type', $columns)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN delivery_type ENUM("home", "shop") DEFAULT "home"');
        echo "✓ Colonne delivery_type ajoutée\n";
    }
    
    if (!in_array('delivery_fee', $columns)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0');
        echo "✓ Colonne delivery_fee ajoutée\n";
    }
    
    if (!in_array('delivery_time', $columns)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN delivery_time DATETIME');
        echo "✓ Colonne delivery_time ajoutée\n";
    }
    
    if (!in_array('delivery_person_id', $columns)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN delivery_person_id INT');
        $pdo->exec('ALTER TABLE orders ADD FOREIGN KEY (delivery_person_id) REFERENCES users(id) ON DELETE SET NULL');
        echo "✓ Colonne delivery_person_id ajoutée\n";
    }
    
    echo "✓ Migration complétée avec succès!\n";
} catch (PDOException $e) {
    echo "Info migration: " . $e->getMessage() . "\n";
}
?>
