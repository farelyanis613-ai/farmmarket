<?php
// Import database schema
try {
    $pdo = new PDO('mysql:host=127.0.0.1;charset=utf8', 'root', '');
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
