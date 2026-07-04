<?php
require_once __DIR__ . '/../config/database.php';
$stmt = $pdo->query('SELECT id, name FROM products');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . "\t" . $r['name'] . "\n";
}
