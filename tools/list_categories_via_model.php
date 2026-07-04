<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/CategoryModel.php';
$m = new CategoryModel();
$cats = $m->all();
foreach ($cats as $c) {
    echo $c['id'] . ':' . $c['name'] . PHP_EOL;
}
?>
