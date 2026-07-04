<?php
// Migrate roles
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=farmmarket;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec('ALTER TABLE users MODIFY role ENUM("client", "farmer", "delivery", "admin") NOT NULL DEFAULT "client"');
    echo "✓ Rôles mis à jour\n";
    
    // Add farmer field to users for farm info
    $columns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('farm_name', $columns)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN farm_name VARCHAR(255)');
        echo "✓ Colonne farm_name ajoutée\n";
    }
    if (!in_array('phone', $columns)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN phone VARCHAR(20)');
        echo "✓ Colonne phone ajoutée\n";
    }
    if (!in_array('address', $columns)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN address TEXT');
        echo "✓ Colonne address ajoutée\n";
    }
    
    // Create the single farmer account if it does not exist
    $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $check->execute(['eleveur@gmail.com']);
    if (!$check->fetchColumn()) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute(['GOKOUN Renaud', 'eleveur@gmail.com', '$2y$10$9kUhw8QMHaMe8ZHENtXVrOM0fe8kev402Dtro2iUDr/ril3aGuWTy', 'farmer']);
        echo "✓ Utilisateur éleveur ajouté\n";
    }
    
} catch (PDOException $e) {
    echo "Info: " . $e->getMessage() . "\n";
}
