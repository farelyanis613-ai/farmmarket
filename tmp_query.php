<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=farmmarket;charset=utf8','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$row = $pdo->query('SELECT id, name FROM products ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
echo $row ? $row['id'] . ':' . $row['name'] : 'NO_PRODUCTS';
