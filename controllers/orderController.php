<?php

require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../NotificationService.php';

function checkout()
{
    $controller = new Controller();
    $cart = CartModel::getCart();

    if (empty($cart)) {
        $controller->redirect('index.php?action=cart');
        return;
    }

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=checkout');
        return;
    }

    $controller->render('cart/checkout.php', ['cart' => $cart]);
}

function checkoutMobile()
{
    $controller = new Controller();
    $cart = CartModel::getCart();

    if (empty($cart)) {
        $controller->redirect('index.php?action=cart');
        return;
    }

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=checkout');
        return;
    }

    $deliveryType = trim($_GET['delivery_type'] ?? 'home');
    $deliveryFee  = intval($_GET['delivery_fee'] ?? getDeliveryFee($deliveryType));
    $deliveryLatitude  = trim($_GET['latitude'] ?? '');
    $deliveryLongitude = trim($_GET['longitude'] ?? '');

    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += ($item['product']['price'] ?? 0) * ($item['quantity'] ?? 1);
    }
    $total = $subtotal + $deliveryFee;

    $controller->render('cart/checkout_mobile.php', [
        'cart'              => $cart,
        'deliveryType'      => $deliveryType,
        'deliveryFee'       => $deliveryFee,
        'deliveryLatitude'  => $deliveryLatitude,
        'deliveryLongitude' => $deliveryLongitude,
        'subtotal'          => $subtotal,
        'total'             => $total,
    ]);
}

function checkoutComplete()
{
    $controller = new Controller();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Requête invalide ou jeton CSRF manquant.';
        $controller->redirect('index.php?action=checkout');
        return;
    }

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=checkout');
        return;
    }

    $cart = CartModel::getCart();
    if (empty($cart)) {
        $controller->redirect('index.php?action=cart');
        return;
    }

    $deliveryType    = trim($_POST['delivery_type']    ?? 'home');
    $deliveryFee     = intval($_POST['delivery_fee']   ?? 0);
    $deliveryAddress = trim($_POST['delivery_address'] ?? '');

    // Coordonnées GPS sélectionnées par le client sur la carte (checkout.php).
    // Uniquement pertinentes pour une livraison à domicile.
    $deliveryLatitude  = null;
    $deliveryLongitude = null;

    if ($deliveryType === 'home') {
        if ($deliveryAddress === '' && !empty($_SESSION['user']['address'])) {
            $deliveryAddress = $_SESSION['user']['address'];
        }

        if (isset($_POST['delivery_latitude']) && $_POST['delivery_latitude'] !== '') {
            $lat = filter_var($_POST['delivery_latitude'], FILTER_VALIDATE_FLOAT);
            if ($lat !== false && $lat >= -90 && $lat <= 90) {
                $deliveryLatitude = $lat;
            }
        }

        if (isset($_POST['delivery_longitude']) && $_POST['delivery_longitude'] !== '') {
            $lng = filter_var($_POST['delivery_longitude'], FILTER_VALIDATE_FLOAT);
            if ($lng !== false && $lng >= -180 && $lng <= 180) {
                $deliveryLongitude = $lng;
            }
        }

        // On ne garde les coordonnées que si les deux sont valides ensemble.
        if ($deliveryLatitude === null || $deliveryLongitude === null) {
            $deliveryLatitude  = null;
            $deliveryLongitude = null;
        }
    } else {
        $deliveryAddress = '';
    }

    $mobileMoneyPhone = normalizePhone(trim($_POST['phone_number'] ?? ''));
    if ($mobileMoneyPhone !== '') {
        if (!isValidBeninPhone($mobileMoneyPhone)) {
            $_SESSION['error'] = 'Numéro Mobile Money invalide. Utilisez le format +229 xxxxxxxxxx.';
            $_SESSION['old_phone_number'] = $_POST['phone_number'] ?? '';
            $_SESSION['old_operator'] = trim($_POST['operator'] ?? 'Moov Money');
            $_SESSION['old_delivery_address'] = $deliveryAddress;
            $controller->redirect('index.php?action=checkout/mobile&delivery_type=' . urlencode($deliveryType) . '&delivery_fee=' . urlencode($deliveryFee));
            return;
        }
    }

    $orderModel = new OrderModel();
    $orderId    = $orderModel->createOrder(
        $_SESSION['user']['id'],
        $cart,
        $deliveryType,
        $deliveryFee,
        $deliveryAddress,
        $deliveryLatitude,
        $deliveryLongitude
    );

    if (!$orderId) {
        $lastError = method_exists($orderModel, 'getLastError') ? $orderModel->getLastError() : '';
        error_log('[checkoutComplete] createOrder failed: ' . $lastError);
        $_SESSION['error'] = 'Erreur lors de la validation de la commande.';
        $controller->redirect('index.php?action=checkout');
        return;
    }

    $order      = $orderModel->getOrderWithDetails($orderId);
    $orderItems = $orderModel->getOrderItems($orderId);

    try {
        notifierEleveurNouvelleCommande($order);
    } catch (Exception $e) {
        error_log('[checkoutComplete] notifierEleveurNouvelleCommande error: ' . $e->getMessage());
    }

    CartModel::clearCart();
    unset($_SESSION['promo_code'], $_SESSION['cart_delivery']);

    // Envoi automatique de la facture par email au client
    $clientEmail = $_SESSION['user']['email'] ?? ($order['customer_email'] ?? '');
    if (!empty($clientEmail) && filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        $sentInvoice = sendInvoiceEmail($orderId, $clientEmail);
        if ($sentInvoice) {
            $_SESSION['success'] = 'Paiement effectué avec succès. La facture a été envoyée à ' . htmlspecialchars($clientEmail) . '.';
        } else {
            $_SESSION['success'] = 'Paiement effectué. Envoi automatique de la facture impossible.';
        }
    } else {
        $_SESSION['success'] = 'Paiement effectué. Adresse email client absente ou invalide.';
    }

    try {
        notifierClientFacture($order, $_SESSION['user']);
    } catch (Exception $e) {
        error_log('[checkoutComplete] notifierClientFacture error: ' . $e->getMessage());
    }

    $controller->redirect('index.php?action=orders');
}

function placeOrder()
{
    $controller = new Controller();
    $cart = CartModel::getCart();

    if (empty($cart)) {
        $controller->redirect('index.php?action=cart');
        return;
    }

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=checkout');
        return;
    }

    $deliveryType = $_POST['delivery_type'] ?? 'home';
    $deliveryFee  = intval($_POST['delivery_fee'] ?? 0);

    $orderModel = new OrderModel();
    $orderId    = $orderModel->createOrder(
        $_SESSION['user']['id'],
        $cart,
        $deliveryType,
        $deliveryFee
    );

    if ($orderId) {
        $order      = $orderModel->getOrderWithDetails($orderId);
        $orderItems = $orderModel->getOrderItems($orderId);
        $orderNumber = $orderModel->getUserOrderSequence($orderId) ?? $orderId;
        CartModel::clearCart();
        // Envoi automatique de la facture et notifications
        $clientEmail = $_SESSION['user']['email'] ?? ($order['customer_email'] ?? '');
        if (!empty($clientEmail) && filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            sendInvoiceEmail($orderId, $clientEmail);
        }

        try {
            notifierEleveurNouvelleCommande($order);
        } catch (Exception $e) {
            error_log('[placeOrder] notifierEleveurNouvelleCommande error: ' . $e->getMessage());
        }

        try {
            notifierClientFacture($order, $_SESSION['user']);
        } catch (Exception $e) {
            error_log('[placeOrder] notifierClientFacture error: ' . $e->getMessage());
        }

        $controller->render('cart/confirmation.php', [
            'orderId'    => $orderId,
            'orderNumber' => $orderNumber,
            'order'      => $order,
            'orderItems' => $orderItems,
        ]);
        return;
    }

    $lastError = method_exists($orderModel, 'getLastError') ? $orderModel->getLastError() : '';
    error_log('[placeOrder] createOrder failed: ' . $lastError);
    echo 'Erreur lors de la validation de la commande.';
}

function orderHistory()
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=orders');
        return;
    }

    $orderModel = new OrderModel();
    $orders     = $orderModel->getByUser($_SESSION['user']['id']);

    foreach ($orders as &$order) {
        $order['order_number'] = $orderModel->getUserOrderSequence(intval($order['id'] ?? 0)) ?? intval($order['id'] ?? 0);
    }
    unset($order);

    $controller->render('cart/orders.php', ['orders' => $orders]);
}

function clearOrderHistory()
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=orders');
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Requête invalide ou jeton CSRF manquant.';
        $controller->redirect('index.php?action=orders');
        return;
    }

    $orderModel = new OrderModel();
    if ($orderModel->deleteByUser($_SESSION['user']['id'])) {
        $_SESSION['success'] = 'Historique des commandes effacé. Vos prochaines commandes commenceront à #1.';
    } else {
        $lastError = method_exists($orderModel, 'getLastError') ? $orderModel->getLastError() : '';
        error_log('[clearOrderHistory] deleteByUser failed: ' . $lastError);
        $_SESSION['error'] = 'Impossible d\'effacer l\'historique des commandes. Réessayez plus tard.';
    }

    $controller->redirect('index.php?action=orders');
}

function viewOrder($id)
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=orders');
        return;
    }

    $orderModel = new OrderModel();
    $order      = $orderModel->getOrderWithDetails($id);

    if (!$order || intval($order['user_id']) !== intval($_SESSION['user']['id'])) {
        header('HTTP/1.0 404 Not Found');
        echo 'Commande introuvable.';
        exit;
    }

    $orderItems = $orderModel->getOrderItems($id);
    $orderNumber = $orderModel->getUserOrderSequence($id) ?? $id;
    $controller->render('cart/order_detail.php', [
        'order'      => $order,
        'orderItems' => $orderItems,
        'orderNumber' => $orderNumber,
    ]);
}

function markOrderDelivered()
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login&next=orders');
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->redirect('index.php?action=orders');
        return;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=orders');
        return;
    }

    $orderId = intval($_GET['id'] ?? 0);
    if ($orderId <= 0) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $orderModel = new OrderModel();
    $order = $orderModel->getOrderWithDetails($orderId);

    if (!$order || $order['user_id'] !== $_SESSION['user']['id']) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    if (normalizeStatus($order['status']) === 'delivered' || isFailedStatus($order['status'])) {
        $_SESSION['error'] = 'Le statut de cette commande ne peut pas être modifié.';
        $controller->redirect('index.php?action=order/view&id=' . $orderId);
        return;
    }

    if ($orderModel->updateOrderStatus($orderId, 'delivered')) {
        $_SESSION['success'] = 'Commande marquée comme livrée.';
    } else {
        $_SESSION['error'] = 'Impossible de mettre à jour le statut de la commande.';
    }

    $controller->redirect('index.php?action=order/view&id=' . $orderId);
}

/**
 * Génère les octets PDF de la facture avec Dompdf
 */
function generateInvoicePDFBytes($orderId, $orderDetails, $orderItems, $orderNumber = null)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $invoiceNumber  = $orderNumber !== null ? $orderNumber : $orderId;
    $invoiceDate    = date('d/m/Y', strtotime($orderDetails['created_at']));
    $invoiceTime    = date('H:i', strtotime($orderDetails['created_at']));
    $invoiceDateObj = date('d M Y', strtotime($orderDetails['created_at']));

    // ── Données client (clé "phone" en BDD, fallback sur "customer_phone") ──
    $customerName  = htmlspecialchars($orderDetails['customer_name']  ?? '');
    $customerEmail = htmlspecialchars($orderDetails['customer_email'] ?? '');
    $customerPhone = htmlspecialchars(
        $orderDetails['phone'] ?? $orderDetails['customer_phone'] ?? ''
    );
    $customerAddr  = htmlspecialchars($orderDetails['address'] ?? 'Non spécifiée');
    $deliveryType  = ($orderDetails['delivery_type'] ?? '') === 'home'
        ? 'Livraison a domicile'
        : 'Retrait en boutique';
    // Données livreur et métadonnées additionnelles
    $deliveryPersonName  = htmlspecialchars($orderDetails['delivery_person_name'] ?? '');
    $deliveryPersonPhone = htmlspecialchars($orderDetails['delivery_person_phone'] ?? '');
    $deliveryPersonEmail = htmlspecialchars($orderDetails['delivery_person_email'] ?? '');
    $failedReason        = htmlspecialchars($orderDetails['failed_reason'] ?? '');
    $latitude            = htmlspecialchars($orderDetails['latitude'] ?? '');
    $longitude           = htmlspecialchars($orderDetails['longitude'] ?? '');

    // ── Lignes articles ──
    $subtotal  = 0;
    $itemRows  = '';
    $itemCount = 0;
    $rowIndex  = 0;

    foreach ($orderItems as $item) {
        $rowIndex++;
        $lineTotal  = (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 0);
        $subtotal  += $lineTotal;
        $itemCount += (int)($item['quantity'] ?? 0);
        $bg         = $rowIndex % 2 === 0 ? '#F8FAFF' : '#FFFFFF';

        $itemRows .= sprintf(
            '<tr style="background:%s;">
                <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:11px; color:#1E293B;">%s</td>
                <td style="padding:10px 12px; text-align:center; border-bottom:1px solid #E2E8F0; font-size:11px; color:#475569;">%d</td>
                <td style="padding:10px 12px; text-align:right; border-bottom:1px solid #E2E8F0; font-size:11px; color:#475569;">%s FCFA</td>
                <td style="padding:10px 12px; text-align:right; border-bottom:1px solid #E2E8F0; font-size:11px; font-weight:700; color:#1a56db;">%s FCFA</td>
            </tr>',
            $bg,
            htmlspecialchars($item['product_name'] ?? 'Produit'),
            (int)($item['quantity'] ?? 0),
            number_format((float)($item['unit_price'] ?? 0), 0, '', ' '),
            number_format($lineTotal, 0, '', ' ')
        );
    }

    // ── Totaux ──
    $deliveryFee     = (float)($orderDetails['delivery_fee']    ?? 0);
    $promoDiscount   = (float)($orderDetails['promo_discount']  ?? 0);
    $totalPrice      = (float)($orderDetails['total_price']     ?? 0);

    $subtotalFmt     = number_format($subtotal,      0, '', ' ');
    $deliveryFmt     = number_format($deliveryFee,   0, '', ' ');
    $promoFmt        = number_format($promoDiscount, 0, '', ' ');
    $totalFmt        = number_format($totalPrice,    0, '', ' ');
    $invoiceRef      = 'FAC-' . date('Y') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);

    // ── Statut ──
    $statusColor = match($orderDetails['status'] ?? 'pending') {
        'completed'  => '#10b981',
        'processing' => '#f59e0b',
        'cancelled'  => '#ef4444',
        default      => '#6b7280',
    };
    $statusLabel = match($orderDetails['status'] ?? 'pending') {
        'completed'  => 'COMPLETEE',
        'processing' => 'EN COURS',
        'pending'    => 'EN ATTENTE',
        'cancelled'  => 'ANNULEE',
        default      => htmlspecialchars($orderDetails['status'] ?? 'En attente'),
    };

    // ── Lignes frais/promo ──
    $feesHtml = '';
    if ($deliveryFee > 0) {
        $feesHtml .= '
        <div style="display:table; width:100%; padding:7px 0; border-bottom:1px solid #F1F5F9;">
            <span style="display:table-cell; font-size:10px; color:#64748B;">Frais de livraison</span>
            <span style="display:table-cell; text-align:right; font-size:10px; font-weight:600; color:#1E293B;">+' . $deliveryFmt . ' FCFA</span>
        </div>';
    }
    if ($promoDiscount > 0) {
        $feesHtml .= '
        <div style="display:table; width:100%; padding:7px 0; border-bottom:1px solid #F1F5F9;">
            <span style="display:table-cell; font-size:10px; color:#64748B;">Reduction promo</span>
            <span style="display:table-cell; text-align:right; font-size:10px; font-weight:600; color:#dc2626;">-' . $promoFmt . ' FCFA</span>
        </div>';
    }

    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    color: #1E293B;
    background: #EEF2FF;
    font-size: 11px;
    line-height: 1.5;
  }
  .page { width: 210mm; padding: 20px; background: #EEF2FF; }

  /* HEADER */
  .header { background: #1a56db; padding: 24px 28px; margin-bottom: 16px; }
  .header-inner { display: table; width: 100%; }
  .header-left  { display: table-cell; vertical-align: middle; width: 55%; }
  .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 45%; }
  .brand-name    { font-size: 22px; font-weight: 700; color: #FFFFFF; }
  .brand-tagline { font-size: 10px; color: rgba(255,255,255,0.7); margin-top: 3px; }
  .brand-contact { font-size: 9px; color: rgba(255,255,255,0.6); margin-top: 8px; line-height: 1.8; }
  .inv-label  { font-size: 9px; font-weight: 700; letter-spacing: 0.15em; color: rgba(255,255,255,0.55); text-transform: uppercase; }
  .inv-number { font-size: 18px; font-weight: 700; color: #FFFFFF; margin-top: 3px; }
  .inv-date   { font-size: 9px; color: rgba(255,255,255,0.65); margin-top: 4px; }
  .badge {
    display: inline-block; margin-top: 8px;
    background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35);
    color: #FFFFFF; font-size: 9px; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; padding: 3px 10px;
  }

  /* INFO GRID */
  .info-grid  { display: table; width: 100%; margin-bottom: 16px; }
  .info-left  {
    display: table-cell; width: 50%;
    background: #FFFFFF; border: 1px solid #E2E8F0;
    padding: 14px 16px; vertical-align: top;
  }
  .info-right {
    display: table-cell; width: 50%;
    background: #FFFFFF; border: 1px solid #E2E8F0; border-left: none;
    padding: 14px 16px; vertical-align: top;
  }
  .cell-title {
    font-size: 8px; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: #1a56db;
    border-bottom: 1px solid #DBEAFE; padding-bottom: 6px; margin-bottom: 10px;
  }
  .info-line { font-size: 10px; color: #475569; margin-bottom: 4px; line-height: 1.6; }
  .info-line strong { color: #0F172A; font-weight: 700; }

  /* SECTION TITLE */
  .sec-title {
    font-size: 8px; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: #64748B; margin-bottom: 8px;
  }

  /* TABLE */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; border: 1px solid #E2E8F0; }
  .items-table thead tr { background: #1a56db; }
  .items-table thead th {
    padding: 10px 12px; font-size: 9px; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase; color: #FFFFFF;
  }
  .items-table tbody tr:last-child td { border-bottom: none; }

  /* SUMMARY */
  .sum-wrapper { display: table; width: 100%; margin-bottom: 16px; }
  .sum-spacer  { display: table-cell; width: 50%; }
  .sum-box {
    display: table-cell; width: 50%;
    background: #FFFFFF; border: 1px solid #E2E8F0;
    padding: 14px 16px; vertical-align: top;
  }
  .sum-subtotal { display: table; width: 100%; padding: 7px 0; border-bottom: 1px solid #F1F5F9; }
  .sum-lbl { display: table-cell; font-size: 10px; color: #64748B; }
  .sum-val { display: table-cell; text-align: right; font-size: 10px; font-weight: 600; color: #1E293B; }
  .total-bar {
    display: table; width: 100%;
    background: #1a56db; padding: 10px 12px; margin-top: 8px;
  }
  .total-lbl { display: table-cell; font-size: 11px; font-weight: 700; color: #FFFFFF; }
  .total-val { display: table-cell; text-align: right; font-size: 13px; font-weight: 700; color: #FFFFFF; }

  /* FOOTER */
  .footer { background: #FFFFFF; border: 1px solid #E2E8F0; padding: 14px 20px; text-align: center; }
  .footer-thanks  { font-size: 11px; font-weight: 700; color: #1a56db; margin-bottom: 4px; }
  .footer-divider { width: 36px; height: 2px; background: #1a56db; margin: 8px auto; }
  .footer-text    { font-size: 9px; color: #94A3B8; line-height: 1.7; }
</style>
</head>
<body>
<div class="page">

  <!-- EN-TETE -->
  <div class="header">
    <div class="header-inner">
      <div class="header-left">
        <div class="brand-name">FarmMarket</div>
        <div class="brand-tagline">Produits frais, livres chez vous</div>
        <div class="brand-contact">
          123 Rue de la Ferme, Cotonou, Benin<br>
          contact@farmmarket.com · +229 97 00 00 00
        </div>
      </div>
      <div class="header-right">
        <div class="inv-label">Facture</div>
        <div class="inv-number">{$invoiceRef}</div>
        <div class="inv-date">Emise le {$invoiceDate} a {$invoiceTime}</div>
        <div class="badge" style="color:{$statusColor}; border-color:{$statusColor};">{$statusLabel}</div>
      </div>
    </div>
  </div>

  <!-- INFOS -->
  <div class="info-grid">
    <div class="info-left">
      <div class="cell-title">Informations client</div>
      <div class="info-line"><strong>{$customerName}</strong></div>
      <div class="info-line">{$customerEmail}</div>
      <div class="info-line">{$customerPhone}</div>
      <div class="info-line">{$customerAddr}</div>
    </div>
        <div class="info-right">
            <div class="cell-title">Details commande</div>
            <div class="info-line"><strong>Ref.</strong> #CMD-{$orderId}</div>
            <div class="info-line"><strong>Date</strong> {$invoiceDate}</div>
            <div class="info-line"><strong>Livraison</strong> {$deliveryType}</div>
            <div class="info-line"><strong>Articles</strong> {$itemCount}</div>
            <div class="info-line"><strong>Livreur</strong> {$deliveryPersonName}</div>
            <div class="info-line"><strong>Tél. livreur</strong> {$deliveryPersonPhone}</div>
            <div class="info-line"><strong>Email livreur</strong> {$deliveryPersonEmail}</div>
            <div class="info-line"><strong>Coordonnées</strong> Lat: {$latitude} Lon: {$longitude}</div>
            <div class="info-line"><strong>Raison échec</strong> {$failedReason}</div>
        </div>
  </div>

  <!-- ARTICLES -->
  <div class="sec-title">Detail des articles</div>
  <table class="items-table">
    <thead>
      <tr>
        <th style="text-align:left;">Produit</th>
        <th style="text-align:center; width:70px;">Qte</th>
        <th style="text-align:right; width:120px;">Prix unitaire</th>
        <th style="text-align:right; width:120px;">Total ligne</th>
      </tr>
    </thead>
    <tbody>{$itemRows}</tbody>
  </table>

  <!-- TOTAUX -->
  <div class="sum-wrapper">
    <div class="sum-spacer"></div>
    <div class="sum-box">
      <div class="sum-subtotal">
        <span class="sum-lbl">Sous-total articles</span>
        <span class="sum-val">{$subtotalFmt} FCFA</span>
      </div>
      {$feesHtml}
      <div class="total-bar">
        <span class="total-lbl">Total TTC</span>
        <span class="total-val">{$totalFmt} FCFA</span>
      </div>
    </div>
  </div>

  <!-- PIED DE PAGE -->
  <div class="footer">
    <div class="footer-thanks">Merci pour votre confiance !</div>
    <div class="footer-divider"></div>
    <div class="footer-text">
      Facture generee automatiquement · valable sans signature<br>
      contact@farmmarket.com · +229 97 00 00 00 · FarmMarket, Cotonou, Benin
    </div>
  </div>

</div>
</body>
</html>
HTML;

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
}

function checkoutCancel()
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login');
        return;
    }

    $_SESSION['success'] = 'Paiement annulé.';
    $controller->redirect('index.php?action=products');
}

function downloadInvoice()
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login');
        return;
    }

    $orderId = intval($_GET['id'] ?? 0);
    if ($orderId <= 0) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $orderModel = new OrderModel();
    $order      = $orderModel->getById($orderId);

    // Allow download for the order owner, the farmer for that order, the assigned delivery person, or admins
    $allowed = false;
    $sessionUserId = intval($_SESSION['user']['id'] ?? 0);
    $sessionRole = $_SESSION['user']['role'] ?? '';
    if ($order && intval($order['user_id']) === $sessionUserId) {
        $allowed = true;
    }
    if (!$allowed && $sessionRole === 'farmer' && intval($order['farmer_id'] ?? 0) === $sessionUserId) {
        $allowed = true;
    }
    if (!$allowed && $sessionRole === 'delivery' && intval($order['delivery_person_id'] ?? 0) === $sessionUserId) {
        $allowed = true;
    }
    if (!$allowed && $sessionRole === 'admin') {
        $allowed = true;
    }

    if (!$order || !$allowed) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $orderDetails = $orderModel->getOrderWithDetails($orderId);
    $orderItems   = $orderModel->getOrderItems($orderId);
    $orderNumber  = $orderModel->getUserOrderSequence($orderId) ?? $orderId;

    $pdf = generateInvoicePDFBytes($orderId, $orderDetails, $orderItems, $orderNumber);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="facture-commande-' . $orderId . '.pdf"');
    echo $pdf;
    exit;
}

function sendInvoiceEmail($orderId, $recipientEmail)
{
    if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('[sendInvoiceEmail] Invalid email: ' . var_export($recipientEmail, true));
        return false;
    }

    try {
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new Exception('vendor/autoload.php not found. Run: composer install');
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../config/mail.php';

        $orderModel   = new OrderModel();
        $orderDetails = $orderModel->getOrderWithDetails($orderId);
        $orderItems   = $orderModel->getOrderItems($orderId);
        $orderNumber  = $orderModel->getUserOrderSequence($orderId) ?? $orderId;

        if (!$orderDetails) {
            throw new Exception('Order #' . $orderId . ' not found');
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug   = defined('MAIL_DEBUG') ? MAIL_DEBUG : 0;
        $mail->Debugoutput = 'error_log';
        $mail->Host        = MAIL_HOST;
        $mail->SMTPAuth    = true;
        $mail->Username    = MAIL_USERNAME;
        $mail->Password    = MAIL_PASSWORD;

        if (defined('MAIL_ENCRYPTION') && strtolower(MAIL_ENCRYPTION) === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = MAIL_PORT;

        $senderEmail = MAIL_FROM_EMAIL ?: MAIL_USERNAME;
        $senderName  = MAIL_FROM_NAME ?: 'FarmMarket';

        $mail->setFrom($senderEmail, $senderName);
        $mail->Sender = $senderEmail;
        $mail->addAddress($recipientEmail);
        $mail->addReplyTo($senderEmail, $senderName);
        $mail->addCustomHeader('X-Mailer', 'FarmMarket Mailer');
        $mail->Priority = 3;
        $mail->Timeout = 10;

        $mail->Subject = 'Confirmation de commande FarmMarket #' . $orderId;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];

        $mail->Body    = generateInvoiceHTML($orderId, $orderDetails, $orderItems, $orderNumber);
        $mail->AltBody = generateInvoiceText($orderId, $orderDetails, $orderItems, $orderNumber);

        try {
            $pdfBytes = generateInvoicePDFBytes($orderId, $orderDetails, $orderItems, $orderNumber);
            if (!empty($pdfBytes)) {
                $mail->addStringAttachment(
                    $pdfBytes,
                    'facture-commande-' . $orderId . '.pdf',
                    'base64',
                    'application/pdf'
                );
            }
        } catch (Exception $e) {
            error_log('[sendInvoiceEmail] Could not attach PDF: ' . $e->getMessage());
        }

        $result = $mail->send();

        if ($result) {
            error_log('[sendInvoiceEmail] Email sent to ' . $recipientEmail . ' for order #' . $orderId);
        }

        return $result;

    } catch (Exception $e) {
        error_log('[sendInvoiceEmail] Exception: ' . $e->getMessage() . ' (Order #' . $orderId . ')');
        return false;
    }
}

/**
 * Envoie un email simple (HTML + alt) en réutilisant la config PHPMailer
 */
function sendSimpleEmail($toEmail, $subject, $htmlBody, $altBody = '')
{
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    try {
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new Exception('vendor/autoload.php not found. Run: composer install');
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../config/mail.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug   = defined('MAIL_DEBUG') ? MAIL_DEBUG : 0;
        $mail->Debugoutput = 'error_log';
        $mail->Host        = MAIL_HOST;
        $mail->SMTPAuth    = true;
        $mail->Username    = MAIL_USERNAME;
        $mail->Password    = MAIL_PASSWORD;
        $mail->Port        = MAIL_PORT;
        if (defined('MAIL_ENCRYPTION') && strtolower(MAIL_ENCRYPTION) === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }

        $senderEmail = MAIL_FROM_EMAIL ?: MAIL_USERNAME;
        $senderName  = MAIL_FROM_NAME ?: 'FarmMarket';

        $mail->setFrom($senderEmail, $senderName);
        $mail->Sender = $senderEmail;
        $mail->addAddress($toEmail);
        $mail->addReplyTo($senderEmail, $senderName);
        $mail->addCustomHeader('X-Mailer', 'FarmMarket Mailer');
        $mail->Priority = 3;
        $mail->Timeout = 10;
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];

        return $mail->send();
    } catch (Exception $e) {
        error_log('[sendSimpleEmail] Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Envoie notifications email + SMS au client et aux livreurs lors d'une nouvelle commande
 */
// sendOrderNotifications removed: duplicate of NotificationService responsibilities.

/**
 * Génère le HTML de la facture pour l'email
 */
function generateInvoiceHTML($orderId, $orderDetails, $orderItems, $orderNumber = null)
{
    $invoiceNumber = $orderNumber !== null ? $orderNumber : $orderId;
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $baseUrl  = 'http://' . $host . ($basePath === '/' || $basePath === '.' ? '' : $basePath);

    $customerName         = $orderDetails['customer_name'] ?? 'Client';
    $orderDate            = date('d/m/Y à H:i', strtotime($orderDetails['created_at']));
    $orderStatus          = formatStatusLabel($orderDetails['status'] ?? 'pending');
    $deliveryMode         = ($orderDetails['delivery_type'] ?? '') === 'home'
        ? 'Livraison à domicile' : 'Retrait en boutique';
    $deliveryAddr         = nl2br(htmlspecialchars($orderDetails['address'] ?? 'Non spécifiée'));

    // Données livreur pour l'email
    $deliveryPersonNameEmail  = htmlspecialchars($orderDetails['delivery_person_name'] ?? '');
    $deliveryPersonPhoneEmail = htmlspecialchars($orderDetails['delivery_person_phone'] ?? '');
    $deliveryPersonEmailAddr  = htmlspecialchars($orderDetails['delivery_person_email'] ?? '');
    $failedReasonEmail        = htmlspecialchars($orderDetails['failed_reason'] ?? '');
    $latitudeEmail            = htmlspecialchars($orderDetails['latitude'] ?? '');
    $longitudeEmail           = htmlspecialchars($orderDetails['longitude'] ?? '');

    $subtotal = 0;
    foreach ($orderItems as $item) {
        $subtotal += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
    }
    $deliveryFee          = $orderDetails['delivery_fee'] ?? 0;
    $total                = $orderDetails['total_price'] ?? ($subtotal + $deliveryFee);
    $subtotalFormatted    = number_format($subtotal,     0, '', ' ');
    $deliveryFeeFormatted = number_format($deliveryFee,  0, '', ' ');
    $totalFormatted       = number_format($total,        0, '', ' ');

    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture FarmMarket #{$invoiceNumber}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a56db; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p  { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }
        .content { background: #f8fafc; padding: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 10px; border-bottom: 2px solid #1a56db; padding-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-weight: 600; }
        .items-table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 13px; }
        .items-table th { background: #e2e8f0; padding: 10px; text-align: left; font-weight: 600; color: #475569; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .total-row { display: flex; justify-content: space-between; padding: 14px; background: #1a56db; color: white; font-weight: 700; font-size: 15px; margin-top: 12px; }
        .footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; background: #1a56db; color: white; padding: 10px 20px; text-decoration: none; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>FarmMarket</h1>
        <p>Confirmation de commande</p>
    </div>
    <div class="content">
        <p>Bonjour <strong>{$customerName}</strong>,</p>
        <p>Merci pour votre commande ! Voici le détail de votre facture.</p>

        <div class="section">
            <div class="section-title">Informations de commande</div>
            <div class="info-row"><span class="info-label">Numéro :</span><span>#{$invoiceNumber}</span></div>
            <div class="info-row"><span class="info-label">Date :</span><span>{$orderDate}</span></div>
            <div class="info-row"><span class="info-label">Statut :</span><span>{$orderStatus}</span></div>
        </div>

        <div class="section">
            <div class="section-title">Détail des produits</div>
            <table class="items-table">
                <thead><tr><th>Produit</th><th>Qté</th><th style="text-align:right;">P.U.</th><th style="text-align:right;">Total</th></tr></thead>
                <tbody>
HTML;

    foreach ($orderItems as $item) {
        $itemTotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
        $html .= sprintf(
            '<tr><td>%s</td><td>%d</td><td style="text-align:right;">%s FCFA</td><td style="text-align:right;">%s FCFA</td></tr>',
            htmlspecialchars($item['product_name']),
            (int)$item['quantity'],
            number_format((float)$item['unit_price'], 0, '', ' '),
            number_format($itemTotal, 0, '', ' ')
        );
    }

    $html .= <<<HTML
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Récapitulatif</div>
            <div class="info-row"><span class="info-label">Sous-total :</span><span>{$subtotalFormatted} FCFA</span></div>
            <div class="info-row"><span class="info-label">Frais de livraison :</span><span>{$deliveryFeeFormatted} FCFA</span></div>
            <div class="total-row"><span>Total payé :</span><span>{$totalFormatted} FCFA</span></div>
        </div>

        <div class="section">
            <div class="section-title">Informations de livraison</div>
            <div class="info-row"><span class="info-label">Mode :</span><span>{$deliveryMode}</span></div>
            <div class="info-row"><span class="info-label">Adresse :</span><span>{$deliveryAddr}</span></div>
            <div class="info-row"><span class="info-label">Livreur :</span><span>{$deliveryPersonNameEmail}</span></div>
            <div class="info-row"><span class="info-label">Téléphone livreur :</span><span>{$deliveryPersonPhoneEmail}</span></div>
            <div class="info-row"><span class="info-label">Email livreur :</span><span>{$deliveryPersonEmailAddr}</span></div>
            <div class="info-row"><span class="info-label">Coordonnées :</span><span>Lat: {$latitudeEmail} Lon: {$longitudeEmail}</span></div>
            <div class="info-row"><span class="info-label">Raison échec :</span><span>{$failedReasonEmail}</span></div>
        </div>

        <div style="text-align:center;">
            <a href="{$baseUrl}/index.php?action=order/invoice&id={$orderId}" class="btn">Télécharger la facture PDF</a>
        </div>

        <div class="footer">
            <p>Pour toute question : contact@farmmarket.com</p>
            <p>© 2025 FarmMarket. Tous droits réservés.</p>
        </div>
    </div>
</div>
</body>
</html>
HTML;

    return $html;
}

/**
 * Génère la version texte de la facture pour l'email
 */
function generateInvoiceText($orderId, $orderDetails, $orderItems, $orderNumber = null)
{
    $invoiceNumber = $orderNumber !== null ? $orderNumber : $orderId;
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $baseUrl  = 'http://' . $host . ($basePath === '/' || $basePath === '.' ? '' : $basePath);

    $lines   = [];
    $lines[] = '=======================================';
    $lines[] = '        FARMMARKET - FACTURE';
    $lines[] = '=======================================';
    $lines[] = '';
    $lines[] = 'Bonjour ' . ($orderDetails['customer_name'] ?? 'Client') . ',';
    $lines[] = 'Merci pour votre commande !';
    $lines[] = '';
    $lines[] = '--- COMMANDE ---';
    $lines[] = 'Numero  : #' . $invoiceNumber;
    $lines[] = 'Date    : ' . date('d/m/Y H:i', strtotime($orderDetails['created_at']));
    $lines[] = 'Statut  : ' . formatStatusLabel($orderDetails['status'] ?? 'pending');
    $lines[] = '';
    $lines[] = '--- PRODUITS ---';

    $subtotal = 0;
    foreach ($orderItems as $item) {
        $itemTotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
        $subtotal += $itemTotal;
        $lines[] = sprintf(
            '%s x%d : %s FCFA = %s FCFA',
            $item['product_name'],
            (int)$item['quantity'],
            number_format((float)$item['unit_price'], 0, '', ' '),
            number_format($itemTotal, 0, '', ' ')
        );
    }

    $deliveryFee = (float)($orderDetails['delivery_fee'] ?? 0);
    $total       = (float)($orderDetails['total_price']  ?? 0);

    $lines[] = '';
    $lines[] = '--- RECAPITULATIF ---';
    $lines[] = 'Sous-total         : ' . number_format($subtotal,     0, '', ' ') . ' FCFA';
    $lines[] = 'Frais de livraison : ' . number_format($deliveryFee,  0, '', ' ') . ' FCFA';
    $lines[] = 'TOTAL              : ' . number_format($total,        0, '', ' ') . ' FCFA';
    $lines[] = '';
    $lines[] = '--- LIVRAISON ---';
    $lines[] = 'Mode    : ' . (($orderDetails['delivery_type'] ?? '') === 'home' ? 'Livraison a domicile' : 'Retrait en boutique');
    $lines[] = 'Adresse : ' . ($orderDetails['address'] ?? 'Non specifiee');
    $lines[] = 'Livreur : ' . ($orderDetails['delivery_person_name'] ?? 'N/A');
    $lines[] = 'Tel livreur : ' . ($orderDetails['delivery_person_phone'] ?? 'N/A');
    $lines[] = 'Email livreur : ' . ($orderDetails['delivery_person_email'] ?? 'N/A');
    $lines[] = 'Coordonnees : Lat: ' . ($orderDetails['latitude'] ?? '') . ' Lon: ' . ($orderDetails['longitude'] ?? '');
    $lines[] = 'Raison echec : ' . ($orderDetails['failed_reason'] ?? '');
    $lines[] = '';
    $lines[] = 'Telecharger la facture PDF :';
    $lines[] = $baseUrl . '/index.php?action=order/invoice&id=' . $orderId;
    $lines[] = '';
    $lines[] = '=======================================';
    $lines[] = 'Cordialement, L\'equipe FarmMarket';
    $lines[] = '=======================================';

    return implode("\n", $lines);
}

function reportFailure()
{
    $controller = new Controller();

    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login');
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->redirect('index.php?action=orders');
        return;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Jeton CSRF invalide.';
        $controller->redirect('index.php?action=orders');
        return;
    }

    $orderId = intval($_POST['order_id'] ?? 0);
    $reason  = trim($_POST['reason'] ?? '');

    if ($orderId <= 0) {
        header('HTTP/1.0 400 Bad Request');
        exit;
    }

    $orderModel = new OrderModel();
    $order = $orderModel->getById($orderId);

    if (!$order || $order['user_id'] !== $_SESSION['user']['id']) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    // Only allow reporting failure if order is not already delivered/failed
    if (normalizeStatus($order['status']) === 'delivered' || isFailedStatus($order['status'])) {
        $_SESSION['error'] = 'Le statut de cette commande ne peut pas être modifié.';
        $controller->redirect('index.php?action=order/view&id=' . $orderId);
        return;
    }

    // Mark as failed and save reason
    if ($orderModel->updateOrderStatus($orderId, 'failed')) {
        $orderModel->updateFailedReason($orderId, $reason ?: 'Signalé par le client');
        $_SESSION['success'] = 'Commande signalée comme échouée.';
    } else {
        $_SESSION['error'] = 'Impossible de signaler l’échec de la commande.';
    }

    $controller->redirect('index.php?action=order/view&id=' . $orderId);
}