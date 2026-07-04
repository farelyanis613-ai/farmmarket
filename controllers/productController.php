<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../core/Controller.php';

function listProducts()
{
    $controller = new Controller();
    $productModel = new ProductModel();
    $category = trim($_GET['category'] ?? '');

    if ($category !== '') {
        $products = $productModel->findByCategory($category);
    } else {
        $products = $productModel->getAll();
    }

    $controller->render('products/list.php', ['products' => $products, 'categoryFilter' => $category]);
}

function viewProduct($id)
{
    $controller = new Controller();
    $productModel = new ProductModel();
    $product = $productModel->find($id);

    if (!$product) {
        header('HTTP/1.0 404 Not Found');
        echo 'Produit introuvable.';
        exit;
    }

    $controller->render('products/view.php', ['product' => $product]);
}
