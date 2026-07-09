<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

require_once __DIR__ . '/config/database.php';

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $table . '` LIKE ?');
    $stmt->execute([$column]);
    return $stmt->fetch() !== false;
}

try {
    if (!columnExists($pdo, 'users', 'image')) {
        echo "Ajout de la colonne image à la table users...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER address");
        echo "Colonne image ajoutée.\n";
    } else {
        echo "La colonne image existe déjà dans users.\n";
    }
    echo "Migration terminée.\n";
} catch (PDOException $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
