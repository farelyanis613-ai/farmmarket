<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../core/Controller.php';

function dashboard()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        echo 'Accès administrateur requis.';
        exit;
    }

    $controller = new Controller();
    $productModel = new ProductModel();
    $products = $productModel->getAll();

    $controller->render('admin/dashboard.php', ['products' => $products]);
}
