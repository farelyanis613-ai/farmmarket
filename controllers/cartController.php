<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../core/Controller.php';

const PROMO_CODES = [
    'FARM10' => 0.10,
    'FARM20' => 0.20
];

function getCart()
{
    return $_SESSION['cart'] ?? [];
}

function normalizeCartItems(array $cart): array
{
    $items = [];

    foreach ($cart as $productId => $item) {
        $product = $item['product'] ?? [];
        $quantity = max(0, intval($item['quantity'] ?? 0));

        if ($quantity <= 0 || empty($product)) {
            continue;
        }

        $items[] = [
            'product_id' => intval($productId),
            'product_name' => $product['name'] ?? $product['title'] ?? '',
            'price' => intval($product['price'] ?? 0),
            'quantity' => $quantity,
            'category' => $product['category'] ?? '',
            'farmer_name' => $product['farmer_name'] ?? '',
        ];
    }

    return $items;
}

function viewCart()
{
    $controller = new Controller();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
        applyPromo();
        return;
    }

    if (isset($_GET['sub'])) {
        switch ($_GET['sub']) {
            case 'update':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    updateCart();
                    return;
                }
                break;
            case 'remove':
                removeFromCart();
                return;
        }
    }

    if (isset($_GET['action2']) && $_GET['action2'] === 'clear') {
        clearCart();
        return;
    }

    $cart = getCart();
    $cartItems = normalizeCartItems($cart);

    $subtotal = array_sum(array_map(function ($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));

    $deliveryType = $_SESSION['cart_delivery'] ?? 'home';
    $deliveryFee = getDeliveryFee($deliveryType);

    $promoCode = $_SESSION['promo_code'] ?? '';
    $promoValid = $_SESSION['promo_valid'] ?? false;
    $promoMessage = $_SESSION['promo_message'] ?? '';
    $promoDiscount = 0;

    if ($promoCode && isset(PROMO_CODES[$promoCode])) {
        $promoDiscount = round($subtotal * PROMO_CODES[$promoCode]);
        $promoValid = true;
    }

    $totalPrice = max(0, $subtotal + $deliveryFee - $promoDiscount);

    $controller->render(
        'cart/view.php',
        [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'deliveryType' => $deliveryType,
            'promoCode' => $promoCode,
            'promoDiscount' => $promoDiscount,
            'promoValid' => $promoValid,
            'promoMessage' => $promoMessage,
            'totalPrice' => $totalPrice,
        ]
    );
}

function addToCart()
{
    $controller = new Controller();

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=cart');
        return;
    }

    $productId = intval($_POST['product_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    $productModel = new ProductModel();
    $product = $productModel->find($productId);

    if ($product) {
        $cart = getCart();
        $currentCartQty = isset($cart[$productId]) ? max(0, intval($cart[$productId]['quantity'] ?? 0)) : 0;
        $availableStock = max(0, intval($product['stock'] ?? 0));
        $maxAddable = max(0, $availableStock - $currentCartQty);

        if ($maxAddable <= 0) {
            $_SESSION['error'] = 'Désolé, ce produit n’est plus disponible.';
            $controller->redirect('index.php?action=cart');
            return;
        }

        $quantity = min($quantity, $maxAddable);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        }

        $_SESSION['cart'] = $cart;
    }

    $controller->redirect('index.php?action=cart');
}

function updateCart()
{
    $controller = new Controller();

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=cart');
        return;
    }

    $productId = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['quantity'] ?? 1);

    $cart = getCart();

    if (isset($cart[$productId])) {

        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $qty;
        }

        $_SESSION['cart'] = $cart;
    }

    $controller->redirect('index.php?action=cart');
}

function removeFromCart()
{
    $controller = new Controller();

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=cart');
        return;
    }

    $productId = intval($_POST['product_id'] ?? 0);

    $cart = getCart();

    if (isset($cart[$productId])) {
        unset($cart[$productId]);
    }

    $_SESSION['cart'] = $cart;

    $controller->redirect('index.php?action=cart');
}

function clearCart()
{
    $controller = new Controller();

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=cart');
        return;
    }

    $_SESSION['cart'] = [];

    unset($_SESSION['promo_code']);
    unset($_SESSION['cart_delivery']);

    $controller->redirect('index.php?action=cart');
}

function applyPromo()
{
    $controller = new Controller();

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=cart');
        return;
    }

    $code = strtoupper(
        trim($_POST['promo_code'] ?? '')
    );

    if (isset(PROMO_CODES[$code])) {
        $_SESSION['promo_code'] = $code;
    } else {
        unset($_SESSION['promo_code']);
    }

    if (!empty($_POST['delivery_type'])) {

        $_SESSION['cart_delivery'] =
            ($_POST['delivery_type'] === 'shop')
            ? 'shop'
            : 'home';
    }

    $controller->redirect('index.php?action=cart');
}

