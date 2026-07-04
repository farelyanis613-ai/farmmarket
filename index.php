<?php
// Load environment helpers early so we can read APP_ENV and related vars
require_once __DIR__ . '/config/bootstrap.php';
loadEnvFile(__DIR__ . '/.env');

// Configure secure session cookie params before starting the session
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$appEnv = env('APP_ENV', '');
$secure = $isHttps || strtolower($appEnv) === 'production';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/helpers.php';

$action = $_GET['action'] ?? 'home';
$id = $_GET['id'] ?? null;

// Helper to check role
function hasRole($role) {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === $role;
}

function requireRole($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: index.php?action=login');
        exit;
    }
}

switch ($action) {
    // Product routes
    case 'products':
        require_once __DIR__ . '/controllers/productController.php';
        listProducts();
        break;
    case 'product':
        require_once __DIR__ . '/controllers/productController.php';
        viewProduct($id);
        break;

    // Cart routes
    case 'cart':
        require_once __DIR__ . '/controllers/cartController.php';
        viewCart();
        break;
    case 'cart/add':
        require_once __DIR__ . '/controllers/cartController.php';
        addToCart();
        break;
    case 'cart/remove':
        require_once __DIR__ . '/controllers/cartController.php';
        removeFromCart();
        break;

    // Order routes (client)
    case 'checkout':
        require_once __DIR__ . '/controllers/orderController.php';
        checkout();
        break;
    case 'checkout/mobile':
        require_once __DIR__ . '/controllers/orderController.php';
        checkoutMobile();
        break;
    case 'checkout/complete':
        require_once __DIR__ . '/controllers/orderController.php';
        checkoutComplete();
        break;
    case 'checkout/cancel':
        require_once __DIR__ . '/controllers/orderController.php';
        checkoutCancel();
        break;
    case 'order/place':
        require_once __DIR__ . '/controllers/orderController.php';
        placeOrder();
        break;
    case 'orders':
        require_once __DIR__ . '/controllers/orderController.php';
        orderHistory();
        break;
    case 'order/clear-history':
        require_once __DIR__ . '/controllers/orderController.php';
        clearOrderHistory();
        break;
    case 'order/invoice':
    case 'order/pdf':
        require_once __DIR__ . '/controllers/orderController.php';
        downloadInvoice();
        break;
    case 'order/view':
        require_once __DIR__ . '/controllers/orderController.php';
        viewOrder(intval($id));
        break;
    case 'order/mark-delivered':
        require_once __DIR__ . '/controllers/orderController.php';
        markOrderDelivered();
        break;
    case 'order/report-failure':
        require_once __DIR__ . '/controllers/orderController.php';
        reportFailure();
        break;

    // Auth routes
    case 'login':
        require_once __DIR__ . '/controllers/authController.php';
        login();
        break;
    case 'register':
        require_once __DIR__ . '/controllers/authController.php';
        register();
        break;
    case 'logout':
        require_once __DIR__ . '/controllers/authController.php';
        logout();
        break;

    // Farmer routes
    case 'farmer/dashboard':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDashboard();
        break;
    case 'farmer/products':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerProducts();
        break;
    case 'farmer/add-product':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerAddProduct();
        break;
    case 'farmer/add-delivery':
        // Deprecated route kept for backward compatibility - redirect to canonical route
        header('Location: index.php?action=farmer/deliveries/add', true, 302);
        exit;
        break;
    case 'farmer/edit-product':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerEditProduct();
        break;
    case 'farmer/delete-product':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDeleteProduct();
        break;
    case 'farmer/orders':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerOrders();
        break;
    case 'farmer/order/view':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerOrderView();
        break;
    case 'farmer/assign-delivery-api':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerAssignDeliveryApi();
        break;
    case 'farmer/update-order-status-api':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerUpdateOrderStatusApi();
        break;
    case 'farmer/categories':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerCategories();
        break;
    case 'farmer/add-category':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerAddCategory();
        break;
    case 'farmer/edit-category':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerEditCategory();
        break;
    case 'farmer/delete-category':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDeleteCategory();
        break;
    case 'farmer/assign-delivery':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerAssignDelivery();
        break;
    case 'farmer/orders-poll':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerOrdersPoll();
        break;
    case 'farmer/mark-pickup':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerMarkPickup();
        break;
    case 'farmer/deliveries':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDeliveries();
        break;
    case 'farmer/deliveries/add':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDeliveriesAdd();
        break;
    case 'farmer/deliveries/edit':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDeliveriesEdit();
        break;
    case 'farmer/deliveries/delete':
        require_once __DIR__ . '/controllers/farmerController.php';
        farmerDeliveriesDelete();
        break;

    // Delivery routes
    case 'delivery/dashboard':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryDashboard();
        break;
    case 'delivery/assignments':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryAssignments();
        break;
    case 'delivery/update-status':
        require_once __DIR__ . '/controllers/deliveryController.php';
        updateDeliveryStatus();
        break;
    case 'delivery/details':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryDetails();
        break;
    case 'delivery/how-it-works':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryHowItWorks();
        break;
    case 'delivery/stats':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryStats();
        break;
    case 'delivery/history':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryHistory();
        break;
    case 'delivery/failure-reason':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryFailureReason();
        break;
    case 'delivery/respond':
        require_once __DIR__ . '/controllers/deliveryController.php';
        respondToDelivery();
        break;
    case 'delivery/order-detail':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryOrderDetail();
        break;
    case 'delivery/assignments-poll':
        require_once __DIR__ . '/controllers/deliveryController.php';
        deliveryAssignmentsPoll();
        break;

    // Serve farmer images through PHP route
    case 'farmer/image':
        require_once __DIR__ . '/controllers/imageController.php';
        serveFarmerImage();
        break;

    // Account routes
    case 'profile':
        require_once __DIR__ . '/controllers/accountController.php';
        profile();
        break;

    // Admin routes
    case 'admin':
        require_once __DIR__ . '/controllers/adminController.php';
        dashboard();
        break;

    default:
        require_once __DIR__ . '/views/home.php';
}
