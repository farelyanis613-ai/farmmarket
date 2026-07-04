<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/CartModel.php';

$cart = [
    [
        'product' => ['id' => 4, 'price' => 1000],
        'quantity' => 1
    ]
];

$orderModel = new OrderModel();
$orderId = $orderModel->createOrder(1, $cart, 'home', 2000, 'Adresse Test');
if ($orderId === false) {
    echo "createOrder failed: " . $orderModel->getLastError() . PHP_EOL;
} else {
    echo "createOrder succeeded: orderId=$orderId\n";
}
