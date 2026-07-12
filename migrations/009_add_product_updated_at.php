<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

require_once __DIR__ . '/../config/database.php';

try {
    $columns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('updated_at', $columns, true)) {
        $pdo->exec('ALTER TABLE products ADD COLUMN updated_at DATETIME NULL AFTER created_at');
        echo "✓ Colonne updated_at ajoutée à products\n";
    } else {
        echo "✓ Colonne updated_at existe déjà dans products\n";
    }
} catch (PDOException $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}
