<?php

require_once __DIR__ . '/bootstrap.php';
loadEnvFile(__DIR__ . '/../.env');

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_NAME', env('DB_NAME', 'farmmarket'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('APP_DEBUG', envBool('APP_DEBUG', false));
define('APP_AUTO_MIGRATE', envBool('APP_AUTO_MIGRATE', false));

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
    if (APP_AUTO_MIGRATE) {
        // Connect to host, create DB if missing, then import schema when necessary
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        $pdo->exec('USE `' . DB_NAME . '`');

        if (!tableExists($pdo, 'products')) {
            importDatabaseSchema($pdo, __DIR__ . '/../database.sql');
        }
    } else {
        // In normal (production) mode do not auto-provision; connect directly to the configured database
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    // Log full error server-side, but show a generic message to the client unless APP_DEBUG is enabled
    error_log('[DB DEBUG] Connexion échouée: ' . $e->getMessage());
    error_log('[Database] ' . $e->getMessage());
    if (defined('APP_DEBUG') && APP_DEBUG) {
        // In debug/dev environments we still want to see the error
        http_response_code(500);
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
    http_response_code(503);
    die('Le service est momentanément indisponible, réessayez plus tard.');
}
