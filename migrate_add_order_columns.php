<?php
require_once __DIR__ . '/config/database.php';

$needed = [
    "delivery_type VARCHAR(50) NOT NULL DEFAULT 'home'",
    "delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0",
    "delivery_address TEXT NULL",
    "latitude VARCHAR(64) NULL",
    "longitude VARCHAR(64) NULL",
    "delivery_person_id INT NULL",
];

function columnExists($pdo, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE ?");
    $stmt->execute([$column]);
    return ($stmt->fetch() !== false);
}

try {
    foreach ($needed as $definition) {
        // extract column name before first space
        $parts = preg_split('/\s+/', trim($definition));
        $col = $parts[0];
        $stmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE ?");
        $stmt->execute([$col]);
        if ($stmt->fetch() === false) {
            echo "Ajout de la colonne $col...\n";
            $pdo->exec("ALTER TABLE orders ADD COLUMN $definition");
        } else {
            echo "Colonne $col déjà présente, saut.\n";
        }
    }

    // Ensure foreign key for delivery_person_id exists
    $fkCheck = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'delivery_person_id'")->fetchColumn();
    if (!$fkCheck) {
        echo "Ajout de la contrainte FK delivery_person_id...\n";
        // Add FK with a generated name
        $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_delivery_person FOREIGN KEY (delivery_person_id) REFERENCES users(id) ON DELETE SET NULL");
    } else {
        echo "Contrainte FK delivery_person_id déjà présente.\n";
    }

    echo "Migration terminée.\n";
} catch (PDOException $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
