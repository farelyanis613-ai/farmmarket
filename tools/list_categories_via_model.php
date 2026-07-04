<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/CategoryModel.php';
$m = new CategoryModel();
$cats = $m->all();
foreach ($cats as $c) {
    echo $c['id'] . ':' . $c['name'] . PHP_EOL;
}
?>
