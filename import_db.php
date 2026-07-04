<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

// Import database schema using DB credentials from .env via bootstrap
require_once __DIR__ . '/config/bootstrap.php';
loadEnvFile(__DIR__ . '/.env');

$dbHost = env('DB_HOST', '127.0.0.1');
$dbName = env('DB_NAME', 'farmmarket');
$dbUser = env('DB_USER', 'root');
$dbPass = env('DB_PASS', '');

// Import database schema
try {
    $pdo = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8', $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $statements = array_filter(array_map('trim', preg_split('/;\s*\r?\n/', $sql)));
    
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
        echo "✓ " . substr($statement, 0, 80) . "...\n";
    }
    
    echo "\n✓ Base de données importée avec succès!\n";
} catch (PDOException $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
