<?php
$host = '127.0.0.1'; // Teste d'abord avec ça, puis avec 'host.docker.internal'
$db   = 'farmmarket'; // Remplace par le nom de ta base de données
$user = 'root';        // Ton utilisateur MySQL
$pass = '';            // Ton mot de passe MySQL
$port = '3306';

try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ CONNEXION RÉUSSIE ! Le problème vient de la configuration de ton conteneur FrankenPHP.";
} catch (PDOException $e) {
    echo "❌ ÉCHEC : " . $e->getMessage();
}