<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../core/Controller.php';

function listProducts()
{
    $controller = new Controller();
    $productModel = new ProductModel();
    $categoryModel = new CategoryModel();
    $category = trim($_GET['category'] ?? '');

    if ($category !== '') {
        $products = $productModel->findByCategory($category);
    } else {
        $products = $productModel->getAll();
    }

    $categories = $categoryModel->all();
    $categoryLinks = array_map(function ($cat) use ($category) {
        $label = $cat['name'] ?? '';
        return [
            'label' => $label,
            'url' => 'index.php?action=products&category=' . urlencode($label),
            'active' => (strcasecmp($category, $label) === 0),
        ];
    }, $categories);

    $controller->render('products/list.php', [
        'products' => $products,
        'categoryFilter' => $category,
        'categoryLinks' => $categoryLinks,
    ]);
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
