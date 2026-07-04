<?php
require __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY id');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "=== Categories trouvées ===\n";
    foreach ($categories as $cat) {
        echo "  ID: {$cat['id']}, Name: {$cat['name']}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
