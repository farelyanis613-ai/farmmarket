<?php
require_once __DIR__ . '/../config/database.php';
$stmt = $pdo->query("SHOW COLUMNS FROM orders");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . "\t" . $c['Type'] . "\n";
}
