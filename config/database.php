<?php

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'farmmarket');
define('DB_USER', 'root');
define('DB_PASS', '');

function importDatabaseSchema(PDO $pdo, string $sqlFile)
{
    if (!file_exists($sqlFile)) {
        return;
    }

    $sql = file_get_contents($sqlFile);
    $statements = array_filter(array_map('trim', preg_split('/;\s*\r?\n/', $sql)));

    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }

        $pdo->exec($statement);
    }
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$tableName]);
    return $stmt->fetchColumn() !== false;
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    if (!tableExists($pdo, 'products')) {
        importDatabaseSchema($pdo, __DIR__ . '/../database.sql');
    }
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
