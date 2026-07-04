<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Controller.php';

function checkFarmerAuth()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'farmer') {
        header('Location: index.php?action=login');
        exit;
    }
}

function farmerDashboard()
{
    checkFarmerAuth();
    $controller = new Controller();
    $userId = $_SESSION['user']['id'];
    
    $productModel = new ProductModel();
    $products = $productModel->findByFarmerId($userId);

    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $orders = $orderModel->findByFarmerId($userId);

    $totalSales = array_sum(array_column($orders, 'total_price'));
    $orderCount = count($orders);
    $deliveredCount = count(array_filter($orders, function ($order) {
        return normalizeStatus($order['status']) === 'delivered';
    }));
    $pendingCount = $orderCount - $deliveredCount;

    $userModel = new UserModel();
    $deliveries = $userModel->findByRole('delivery');

    $salesTrend = [];
    for ($i = 5; $i >= 0; $i--) {
        $period = date('M Y', strtotime("-{$i} month"));
        $salesTrend[$period] = 0;
    }
    foreach ($orders as $order) {
        $period = date('M Y', strtotime($order['created_at']));
        if (!isset($salesTrend[$period])) {
            $salesTrend[$period] = 0;
        }
        $salesTrend[$period] += floatval($order['total_price']);
    }

    $controller->render('farmer/dashboard.php', [
        'products' => $products,
        'farm_name' => $_SESSION['user']['farm_name'],
        'totalSales' => $totalSales,
        'orderCount' => $orderCount,
        'deliveredCount' => $deliveredCount,
        'pendingCount' => $pendingCount,
        'salesTrendLabels' => array_keys($salesTrend),
        'salesTrendData' => array_values($salesTrend),
        'orders' => $orders,
        'deliveries' => $deliveries,
    ]);
}

function farmerAssignDeliveryApi()
{
    checkFarmerAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $orderId = intval($_POST['order_id'] ?? 0);
    $deliveryId = intval($_POST['delivery_id'] ?? 0);
    $userId = $_SESSION['user']['id'];

    require_once __DIR__ . '/../models/OrderModel.php';
    require_once __DIR__ . '/../models/UserModel.php';

    $orderModel = new OrderModel();
    $userModel = new UserModel();

    $order = $orderModel->getById($orderId);
    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
        exit;
    }

    if ($order['farmer_id'] !== $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
        exit;
    }

    $delivery = $userModel->find($deliveryId);
    if (!$delivery || $delivery['role'] !== 'delivery') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Livreur invalide.']);
        exit;
    }

    if (!$orderModel->assignDelivery($orderId, $deliveryId)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible d’assigner le livreur.']);
        exit;
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => true,
        'order' => [
            'id' => $orderId,
            'delivery_person_name' => $delivery['name'],
            'delivery_person_phone' => $delivery['phone'],
            'status' => 'in_progress',
        ],
        'message' => 'Livreur assigné avec succès.',
    ]);
    exit;
}

function farmerUpdateOrderStatusApi()
{
    checkFarmerAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $orderId = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $userId = $_SESSION['user']['id'];
    $allowedStatuses = ['pending', 'in_progress', 'accepted', 'delivered', 'failed', 'rejected'];

    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Statut invalide.']);
        exit;
    }

    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $order = $orderModel->getById($orderId);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
        exit;
    }

    if ($order['farmer_id'] !== $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
        exit;
    }

    if (!$orderModel->updateOrderStatus($orderId, $status)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible de mettre à jour le statut.']);
        exit;
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => 'Statut mis à jour.',
    ]);
    exit;
}

function farmerProducts()
{
    checkFarmerAuth();
    $controller = new Controller();
    $userId = $_SESSION['user']['id'];
    
    $productModel = new ProductModel();
    $products = $productModel->findByFarmerId($userId);
    
    $controller->render('farmer/products.php', ['products' => $products]);
}

function farmerAddDelivery()
{
    checkFarmerAuth();
    $controller = new Controller();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        } else {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm'] ?? '';

            if (empty($name) || !$email || empty($password)) {
                $errors[] = 'Tous les champs sont requis. Email doit être valide.';
            } else if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                require_once __DIR__ . '/../models/UserModel.php';
                $userModel = new UserModel();

                if ($userModel->findByEmail($email)) {
                    $errors[] = 'Cet email est déjà utilisé.';
                } else {
                    $userModel->create([
                        'name' => $name,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => 'delivery',
                    ]);
                    $_SESSION['success'] = 'Livreur affecté avec succès';
                    $controller->redirect('index.php?action=farmer/add-delivery');
                }
            }
        }
    }

    $controller->render('farmer/add_delivery.php', ['errors' => $errors, 'csrf_token' => getCsrfToken()]);
}

function farmerAddProduct()
{
    checkFarmerAuth();
    $controller = new Controller();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $category_id = null;
        if (isset($_POST['category_id']) && (string)$_POST['category_id'] !== '') {
            $cid = intval($_POST['category_id']);
            if ($cid > 0) {
                $category_id = $cid;
            }
        }

        if (empty($name) || $price <= 0 || $stock < 0) {
            $errors[] = 'Données invalides.';
        }

        if (empty($errors)) {
            // Determine default image when none is provided
            $defaultImage = 'placeholder.png';
            if (!empty($_POST['image'] ?? '')) {
                $defaultImage = trim($_POST['image']);
            } else {
                // Prefer category-based defaults: 1 => lapin, 2 => poulet
                if ($category_id === 1 || stripos($name, 'lapin') !== false || stripos($name, 'rabbit') !== false) {
                    $defaultImage = 'lapin.png';
                } elseif ($category_id === 2 || stripos($name, 'poule') !== false || stripos($name, 'poulet') !== false || stripos($name, 'chicken') !== false) {
                    $defaultImage = 'poulet.png';
                }
                // Verify file exists in public/images, accept png or svg, otherwise fallback
                $base = pathinfo($defaultImage, PATHINFO_FILENAME);
                $candidates = [ $defaultImage, $base . '.png', $base . '.svg', $base . '.jpg', $base . '.jpeg' ];
                $found = false;
                foreach ($candidates as $cand) {
                    $imgPath = __DIR__ . '/../../public/images/' . $cand;
                    if (file_exists($imgPath)) {
                        $defaultImage = $cand;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $defaultImage = 'placeholder.png';
                }
            }

            $productModel = new ProductModel();
            $productModel->create([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'category_id' => $category_id,
                'image' => $defaultImage,
                'farmer_id' => $_SESSION['user']['id'],
            ]);
            $controller->redirect('index.php?action=farmer/products');
        }
    }

    require_once __DIR__ . '/../models/CategoryModel.php';
    $categoryModel = new CategoryModel();
    $categories = $categoryModel->all();

    $controller->render('farmer/add_product.php', [
        'errors' => $errors,
        'categories' => $categories,
    ]);
}

function farmerEditProduct()
{
    checkFarmerAuth();
    $controller = new Controller();
    $id = intval($_GET['id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $productModel = new ProductModel();
    $product = $productModel->find($id);

    if (!$product || $product['farmer_id'] !== $userId) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $category_id = null;
        if (isset($_POST['category_id']) && (string)$_POST['category_id'] !== '') {
            $cid = intval($_POST['category_id']);
            if ($cid > 0) {
                $category_id = $cid;
            }
        }
        $image = trim($_POST['image'] ?? '');

        if (empty($name) || $price <= 0 || $stock < 0) {
            $errors[] = 'Données invalides.';
        }

        if (empty($errors)) {
            $updateData = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
            ];
            if ($category_id !== null) {
                $updateData['category_id'] = $category_id;
            }
            if ($image !== '') {
                $updateData['image'] = $image;
            }
            $productModel->update($id, $updateData);
            $controller->redirect('index.php?action=farmer/products');
        }
    }

    require_once __DIR__ . '/../models/CategoryModel.php';
    $categoryModel = new CategoryModel();
    $categories = $categoryModel->all();

    $controller->render('farmer/edit_product.php', [
        'product' => $product,
        'errors' => $errors,
        'categories' => $categories,
    ]);
}

function farmerDeleteProduct()
{
    checkFarmerAuth();
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        header('Location: index.php?action=farmer/products');
        exit;
    }
    
    $id = intval($_POST['id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $productModel = new ProductModel();
    $product = $productModel->find($id);

    if ($product && $product['farmer_id'] === $userId) {
        $productModel->delete($id);
    }

    header('Location: index.php?action=farmer/products');
    exit;
}

function farmerOrders()
{
    checkFarmerAuth();
    $controller = new Controller();
    $userId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $orders = $orderModel->findByFarmerId($userId);

    foreach ($orders as &$order) {
        $order['items'] = $orderModel->getOrderItemsByFarmer($order['id'], $userId);
    }
    unset($order);
    
    $controller->render('farmer/orders.php', ['orders' => $orders]);
}

function farmerOrderView()
{
    checkFarmerAuth();
    $controller = new Controller();
    $id = intval($_GET['id'] ?? 0);
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $order = $orderModel->getByIdForFarmer($id, $_SESSION['user']['id']);

    if (!$order) {
        header('HTTP/1.0 404 Not Found');
        echo 'Commande introuvable.';
        exit;
    }

    $order['items'] = $orderModel->getOrderItemsByFarmer($id, $_SESSION['user']['id']);
    $controller->render('farmer/order_detail.php', ['order' => $order]);
}

function farmerAssignDelivery()
{
    checkFarmerAuth();
    $controller = new Controller();
    $orderId = intval($_GET['order_id'] ?? 0);
    
    require_once __DIR__ . '/../models/OrderModel.php';
    require_once __DIR__ . '/../models/UserModel.php';
    
    $orderModel = new OrderModel();
    $userModel = new UserModel();
    
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    $deliveries = $userModel->findByRole('delivery');
    $errors = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $deliveryId = intval($_POST['delivery_id'] ?? 0);
        
        $delivery = $userModel->find($deliveryId);
        if (!$delivery || $delivery['role'] !== 'delivery') {
            $errors[] = 'Livreur invalide.';
        }
        
        if (empty($errors)) {
            $orderModel->assignDelivery($orderId, $deliveryId);
            $controller->redirect('index.php?action=farmer/orders');
        }
    }
    
    $controller->render('farmer/assign_delivery.php', [
        'order' => $order,
        'deliveries' => $deliveries,
        'errors' => $errors
    ]);
}

function farmerMarkPickup()
{
    checkFarmerAuth();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?action=farmer/orders');
        exit;
    }

    $orderId = intval($_POST['order_id'] ?? 0);
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $order = $orderModel->getByIdForFarmer($orderId, $_SESSION['user']['id']);

    if (!$order || ($order['delivery_type'] ?? '') !== 'shop') {
        header('Location: index.php?action=farmer/orders');
        exit;
    }

    $orderModel->updateOrderStatus($orderId, 'delivered');
    header('Location: index.php?action=farmer/orders');
    exit;
}

function farmerCategories()
{
    checkFarmerAuth();
    $controller = new Controller();
    require_once __DIR__ . '/../models/CategoryModel.php';
    $catModel = new CategoryModel();
    $categories = $catModel->all();
    $controller->render('farmer/categories.php', [
        'categories' => $categories,
        'csrf_token' => getCsrfToken(),
    ]);
}

function farmerAddCategory()
{
    checkFarmerAuth();
    $controller = new Controller();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $errors[] = 'Le nom est requis.';
        }
        if (empty($errors)) {
            require_once __DIR__ . '/../models/CategoryModel.php';
            $catModel = new CategoryModel();
            $catModel->create($name);
            $controller->redirect('index.php?action=farmer/categories');
        }
    }

    $controller->render('farmer/add_category.php', [
        'errors' => $errors,
        'csrf_token' => getCsrfToken(),
    ]);
}

function farmerEditCategory()
{
    checkFarmerAuth();
    $controller = new Controller();
    $id = intval($_GET['id'] ?? 0);
    require_once __DIR__ . '/../models/CategoryModel.php';
    $catModel = new CategoryModel();
    $category = $catModel->find($id);
    if (!$category) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $errors[] = 'Le nom est requis.';
        }
        if (empty($errors)) {
            $catModel->update($id, $name);
            $controller->redirect('index.php?action=farmer/categories');
        }
    }
    $controller->render('farmer/edit_category.php', [
        'category' => $category,
        'errors' => $errors,
        'csrf_token' => getCsrfToken(),
    ]);
}

function farmerDeleteCategory()
{
    checkFarmerAuth();
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        header('Location: index.php?action=farmer/categories');
        exit;
    }
    
    $id = intval($_POST['id'] ?? 0);
    require_once __DIR__ . '/../models/CategoryModel.php';
    $catModel = new CategoryModel();
    $catModel->delete($id);
    header('Location: index.php?action=farmer/categories');
    exit;
}

function farmerDeliveries()
{
    checkFarmerAuth();
    $controller = new Controller();
    require_once __DIR__ . '/../models/DeliveryPersonModel.php';
    require_once __DIR__ . '/../models/OrderModel.php';
    
    $driverModel = new DeliveryPersonModel();
    $orderModel = new OrderModel();
    $userId = $_SESSION['user']['id'];
    
    $deliverers = $driverModel->getAvailable();
    $orders = $orderModel->findByFarmerId($userId);
    
    $controller->render('farmer/deliveries.php', [
        'deliverers' => $deliverers,
        'orders' => $orders
    ]);
}

function farmerDeliveriesAdd()
{
    checkFarmerAuth();
    $controller = new Controller();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        } else {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
            $address = htmlspecialchars(trim($_POST['address'] ?? ''));

            if (empty($name) || !$email) {
                $errors[] = 'Le nom et l\'email valide sont requis.';
            }

            if (empty($errors)) {
                require_once __DIR__ . '/../models/DeliveryPersonModel.php';
                $driverModel = new DeliveryPersonModel();

                if ($driverModel->create($name, $email, $phone, $address)) {
                    $_SESSION['success'] = 'Livreur ajouté avec succès';
                    $controller->redirect('index.php?action=farmer/deliveries');
                } else {
                    $errors[] = 'Erreur lors de l\'ajout. Cet email peut déjà être utilisé.';
                }
            }
        }
    }

    $controller->render('farmer/deliveries_add.php', ['errors' => $errors, 'csrf_token' => getCsrfToken()]);
}

function farmerDeliveriesEdit()
{
    checkFarmerAuth();
    $controller = new Controller();
    $id = intval($_GET['id'] ?? 0);
    require_once __DIR__ . '/../models/DeliveryPersonModel.php';
    $driverModel = new DeliveryPersonModel();
    $deliverer = $driverModel->getById($id);
    
    if (!$deliverer) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        } else {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
            $address = htmlspecialchars(trim($_POST['address'] ?? ''));

            if (empty($name) || !$email) {
                $errors[] = 'Le nom et l\'email valide sont requis.';
            }

            if (empty($errors)) {
                if ($driverModel->update($id, $name, $email, $phone, $address)) {
                    $_SESSION['success'] = 'Livreur modifié avec succès';
                    $controller->redirect('index.php?action=farmer/deliveries');
                } else {
                    $errors[] = 'Erreur lors de la modification.';
                }
            }
        }
    }

    $controller->render('farmer/deliveries_edit.php', ['deliverer' => $deliverer, 'errors' => $errors, 'csrf_token' => getCsrfToken()]);
}

function farmerDeliveriesDelete()
{
    checkFarmerAuth();
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        header('Location: index.php?action=farmer/deliveries');
        exit;
    }
    
    $id = intval($_POST['id'] ?? 0);
    require_once __DIR__ . '/../models/DeliveryPersonModel.php';
    $driverModel = new DeliveryPersonModel();
    
    if ($driverModel->delete($id)) {
        $_SESSION['success'] = 'Livreur supprimé avec succès';
    }
    
    header('Location: index.php?action=farmer/deliveries');
    exit;
}
