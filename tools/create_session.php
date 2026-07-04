<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

$sid = $argv[1] ?? 'simsession';
$save = ini_get('session.save_path') ?: sys_get_temp_dir();
if (!is_dir($save)) mkdir($save, 0777, true);
session_save_path($save);
session_id($sid);
session_start();
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Client Test',
    'email' => 'test@example.com',
    'role' => 'client',
    'phone' => '97000000',
    'address' => 'Adresse Test'
];
$_SESSION['cart'] = [
    [
        'product' => ['id'=>1,'name'=>'Produit test','price'=>1000],
        'quantity' => 1
    ]
];
session_write_close();
echo "Created session $sid in $save\n";
