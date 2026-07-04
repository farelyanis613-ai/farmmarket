<?php

require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../core/Controller.php';

function checkDeliveryAuth()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'delivery') {
        header('Location: index.php?action=login');
        exit;
    }
}

function deliveryDashboard()
{
    checkDeliveryAuth();
    $controller = new Controller();
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    
    $accepted = $orderModel->findByDeliveryAndStatus($deliveryId, 'accepted');
    $completed = $orderModel->findByDeliveryAndStatus($deliveryId, 'delivered');
    $failed = $orderModel->findByDeliveryAndStatus($deliveryId, 'failed');
    
    // Include delivered orders also in the 'accepted' list (show them in both sections)
    $delivered = $orderModel->findByDeliveryAndStatus($deliveryId, 'delivered');
    // Merge accepted + delivered while preserving order and removing duplicates by id
    $seen = [];
    $mergedAccepted = [];
    foreach (array_merge($accepted, $delivered) as $o) {
        if (!isset($seen[$o['id']])) {
            $seen[$o['id']] = true;
            $mergedAccepted[] = $o;
        }
    }

    $controller->render('delivery/dashboard.php', [
        'accepted' => $mergedAccepted,
        'completed' => $delivered,
        'failed' => $failed,
    ]);
}

function deliveryAssignments()
{
    checkDeliveryAuth();
    $controller = new Controller();
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/DeliveryModel.php';
    $deliveryModel = new DeliveryModel();
    $assignments = $deliveryModel->findByDelivery($deliveryId);
    
    $controller->render('delivery/assignments.php', ['assignments' => $assignments]);
}

function updateDeliveryStatus()
{
    checkDeliveryAuth();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    $orderId = intval($_POST['order_id'] ?? 0);
    $status = htmlspecialchars($_POST['status'] ?? '');
    $deliveryId = $_SESSION['user']['id'];
    
    // Si le statut est "failed", rediriger vers la page de raison d'échec
    if ($status === 'failed') {
        header('Location: index.php?action=delivery/failure-reason&order_id=' . $orderId);
        exit;
    }
    
    $validStatuses = ['accepted', 'delivered'];
    
    if (!in_array($status, $validStatuses)) {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    require_once __DIR__ . '/../models/DeliveryModel.php';
    $deliveryModel = new DeliveryModel();
    $order = $deliveryModel->findByOrderAndDelivery($orderId, $deliveryId);
    
    if (empty($order)) {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    $deliveryModel->updateStatus($orderId, $status);
    
    header('Location: index.php?action=delivery/dashboard');
    exit;
}

function deliveryHistory()
{
    checkDeliveryAuth();
    $controller = new Controller();
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $accepted = $orderModel->findByDeliveryAndStatus($deliveryId, 'accepted');
    $completed = $orderModel->findByDeliveryAndStatus($deliveryId, 'delivered');
    $failed = $orderModel->findByDeliveryAndStatus($deliveryId, 'failed');
    
    $controller->render('delivery/history.php', [
        'accepted' => $accepted,
        'completed' => $completed,
        'failed' => $failed,
    ]);
}

function deliveryFailureReason()
{
    checkDeliveryAuth();
    $controller = new Controller();
    $orderId = intval($_GET['order_id'] ?? 0);
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $order = $orderModel->getById($orderId);
    
    if (!$order || $order['delivery_person_id'] != $deliveryId || !in_array(normalizeStatus($order['status']), ['accepted', 'failed'])) {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    $errors = [];
    $reason = $order['failed_reason'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reason = htmlspecialchars(trim($_POST['reason'] ?? ''));
        if (empty($reason)) {
            $errors[] = 'Veuillez indiquer la raison de l’échec.';
        }
        if (empty($errors)) {
            $orderModel->updateOrderStatus($orderId, 'failed');
            $orderModel->updateFailedReason($orderId, $reason);
            header('Location: index.php?action=delivery/dashboard');
            exit;
        }
    }
    
    $controller->render('delivery/failure_reason.php', [
        'order' => $order,
        'errors' => $errors,
        'reason' => $reason
    ]);
}

function deliveryDetails()
{
    checkDeliveryAuth();
    $controller = new Controller();
    
    $orderId = intval($_GET['order_id'] ?? 0);
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/DeliveryModel.php';
    $deliveryModel = new DeliveryModel();
    $delivery = $deliveryModel->findByOrderAndDelivery($orderId, $deliveryId);
    
    if (!$delivery) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    $controller->render('delivery/details.php', ['delivery' => $delivery]);
}

function deliveryHowItWorks()
{
    $controller = new Controller();
    $controller->render('delivery/how_it_works.php');
}

function deliveryStats()
{
    checkDeliveryAuth();
    $controller = new Controller();
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/DeliveryModel.php';
    $deliveryModel = new DeliveryModel();
    
    $stats = $deliveryModel->getStats($deliveryId);
    
    $controller->render('delivery/stats.php', ['stats' => $stats]);
}

function respondToDelivery()
{
    checkDeliveryAuth();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    $orderId = intval($_POST['order_id'] ?? 0);
    $action = htmlspecialchars($_POST['action'] ?? '');
    $deliveryId = $_SESSION['user']['id'];
    
    if (!in_array($action, ['accept', 'reject'])) {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    
    $order = $orderModel->getById($orderId);
    
    if (!$order || $order['delivery_person_id'] != $deliveryId) {
        header('Location: index.php?action=delivery/dashboard');
        exit;
    }
    
    $newStatus = ($action === 'accept') ? 'accepted' : 'rejected';
    $orderModel->updateOrderStatus($orderId, $newStatus);
    
    header('Location: index.php?action=delivery/dashboard');
    exit;
}

function deliveryOrderDetail()
{
    checkDeliveryAuth();
    $controller = new Controller();
    
    $orderId = intval($_GET['order_id'] ?? 0);
    $deliveryId = $_SESSION['user']['id'];
    
    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();
    $order = $orderModel->getById($orderId);

    if (!$order || $order['delivery_person_id'] != $deliveryId) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    // Enrich order with customer info to avoid undefined index in views
    require_once __DIR__ . '/../models/UserModel.php';
    $userModel = new UserModel();
    $user = $userModel->find($order['user_id'] ?? 0);

    $order['customer_name'] = $user['name'] ?? ($order['customer_name'] ?? 'Client inconnu');
    $order['phone'] = $user['phone'] ?? ($order['phone'] ?? '');
    $order['address'] = $user['address'] ?? ($order['address'] ?? '');

    $items = $orderModel->getOrderItems($orderId);

    $controller->render('delivery/order_detail.php', [
        'order' => $order,
        'items' => $items
    ]);
}
