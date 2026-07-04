<?php

require_once __DIR__ . '/models/config/database.php';

// Migration pour ajouter les colonnes latitude et longitude à la table orders.
// Ces colonnes stockeront les coordonnées GPS extraites de l'API Google Maps Geocoding.

$columns = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('latitude', $columns, true)) {
    $pdo->exec('ALTER TABLE orders ADD COLUMN latitude DECIMAL(10,8) NULL AFTER delivery_address');
    echo "✓ Colonne latitude ajoutée à orders\n";
} else {
    echo "✓ Colonne latitude existe déjà\n";
}

if (!in_array('longitude', $columns, true)) {
    $pdo->exec('ALTER TABLE orders ADD COLUMN longitude DECIMAL(10,8) NULL AFTER latitude');
    echo "✓ Colonne longitude ajoutée à orders\n";
} else {
    echo "✓ Colonne longitude existe déjà\n";
}
