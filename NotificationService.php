<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/OrderModel.php';

use AfricasTalking\SDK\AfricasTalking;

function sendNotificationEmail($toEmail, $subject, $htmlBody, $altBody = '')
{
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('[NotificationService] Invalid email: ' . var_export($toEmail, true));
        return false;
    }

    if (empty(BREVO_API_KEY)) {
        error_log('[NotificationService] BREVO_API_KEY manquante, envoi email annulé.');
        return false;
    }

    $senderEmail = MAIL_FROM_EMAIL ?: MAIL_USERNAME;
    $senderName = MAIL_FROM_NAME ?: 'FarmMarket';

    $payload = [
        'sender' => [
            'name' => $senderName,
            'email' => $senderEmail,
        ],
        'to' => [
            ['email' => $toEmail],
        ],
        'subject' => $subject,
        'htmlContent' => $htmlBody,
        'textContent' => $altBody ?: strip_tags($htmlBody),
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'api-key: ' . BREVO_API_KEY,
            'content-type: application/json',
        ],
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log('[NotificationService] Brevo cURL error: ' . $curlError);
        return false;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('[NotificationService] Brevo API error (HTTP ' . $httpCode . '): ' . $response);
        return false;
    }

    return true;
}

function getAfricasTalkingClient()
{
    if (!SMS_ENABLED || SMS_PROVIDER !== 'africastalking') {
        return null;
    }

    $missing = [];
    if (empty(AT_USERNAME)) {
        $missing[] = 'AT_USERNAME';
    }
    if (empty(AT_API_KEY)) {
        $missing[] = 'AT_API_KEY';
    }

    if (!empty($missing)) {
        error_log('[NotificationService] Africa\'s Talking config missing: ' . implode(', ', $missing) . '.');
        return null;
    }

    try {
        return new AfricasTalking(AT_USERNAME, AT_API_KEY);
    } catch (Exception $e) {
        error_log('[NotificationService] AfricasTalking init failed: ' . $e->getMessage());
        return null;
    }
}

function sendNotificationSms($toPhone, $message)
{
    $toPhone = normalizePhoneForSms($toPhone);

    if ($toPhone === '') {
        error_log('[NotificationService] Invalid SMS phone number: ' . var_export($toPhone, true));
        return false;
    }

    $client = getAfricasTalkingClient();

    if (!$client) {
        error_log('[NotificationService] Africa\'s Talking client unavailable for SMS delivery.');
        return false;
    }

    try {
        $sms = $client->sms();

        $options = [
            'to'      => $toPhone,
            'message' => $message,
        ];

        if (!empty(AT_FROM)) {
            $options['from'] = AT_FROM;
        }

        $result = $sms->send($options);

        if (empty($result['status']) || $result['status'] !== 'success') {
            error_log('[NotificationService] AfricasTalking SMS failed: ' . print_r($result, true));
            return false;
        }

        $data = $result['data'] ?? null;
        if (!$data || !isset($data->SMSMessageData->Recipients)) {
            error_log('[NotificationService] AfricasTalking SMS invalid response: ' . print_r($result, true));
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log('[NotificationService] AfricasTalking exception: ' . $e->getMessage());
        return false;
    }
}
 
function notifierEleveurNouvelleCommande($commande)
{
    $orderModel = new OrderModel();

    $orderId = intval($commande['id'] ?? 0);
    $items = $orderModel->getOrderItems($orderId);
    $farmerIds = [];

    foreach ($items as $item) {
        if (!empty($item['farmer_id'])) {
            $farmerIds[intval($item['farmer_id'])] = true;
        }
    }

    foreach (array_keys($farmerIds) as $farmerId) {
        $userModel = new UserModel();
        $farmer = $userModel->find($farmerId);
        if (!$farmer) {
            continue;
        }

        $subject = 'Nouvelle commande FarmMarket #' . $orderId;
        $html = '<p>Bonjour ' . htmlspecialchars($farmer['name'] ?? 'Éleveur') . ',</p>' .
                '<p>Une nouvelle commande <strong>#' . $orderId . '</strong> contient au moins un de vos produits.</p>' .
                '<p>Connectez-vous pour la traiter.</p>';
        $alt = 'Bonjour ' . ($farmer['name'] ?? 'Éleveur') . ', une nouvelle commande #' . $orderId . ' contient au moins un de vos produits. Connectez-vous pour la traiter.';

        if (!empty($farmer['email']) && filter_var($farmer['email'], FILTER_VALIDATE_EMAIL)) {
            if (!sendNotificationEmail($farmer['email'], $subject, $html, $alt)) {
                error_log('[NotificationService] Failed to email farmer #' . $farmerId . ' for order #' . $orderId);
            }
        }

        if (!empty($farmer['phone'])) {
            $sms = 'Bonjour ' . ($farmer['name'] ?? 'Éleveur') . ', nouvelle commande #' . $orderId . ' reçue avec au moins un de vos produits. Connectez-vous pour la traiter.';
            if (!sendNotificationSms($farmer['phone'], $sms)) {
                error_log('[NotificationService] Failed SMS farmer #' . $farmerId . ' for order #' . $orderId);
            }
        }
    }

    return true;
}

function notifierLivreurAssignation($commande, $livreur)
{
    $orderId = intval($commande['id'] ?? 0);
    $subject = 'Nouvelle assignation de livraison #' . $orderId;
    $html = '<p>Bonjour ' . htmlspecialchars($livreur['name'] ?? 'Livreur') . ',</p>' .
            '<p>Vous avez été assigné(e) à la livraison de la commande <strong>#' . $orderId . '</strong>.</p>' .
            '<p>Merci de vous connecter pour voir les détails.</p>';
    $alt = 'Bonjour ' . ($livreur['name'] ?? 'Livreur') . ', vous avez été assigné(e) à la livraison de la commande #' . $orderId . '. Connectez-vous pour voir les détails.';

    if (!empty($livreur['email']) && filter_var($livreur['email'], FILTER_VALIDATE_EMAIL)) {
        if (!sendNotificationEmail($livreur['email'], $subject, $html, $alt)) {
            error_log('[NotificationService] Failed to email deliverer #' . intval($livreur['id'] ?? 0) . ' for order #' . $orderId);
        }
    }

    if (!empty($livreur['phone'])) {
        $sms = 'Bonjour ' . ($livreur['name'] ?? 'Livreur') . ', la commande #' . $orderId . ' vous a été assignée. Connectez-vous pour consulter les détails.';
        if (!sendNotificationSms($livreur['phone'], $sms)) {
            error_log('[NotificationService] Failed SMS deliverer #' . intval($livreur['id'] ?? 0) . ' for order #' . $orderId);
        }
    }

    return true;
}

function notifierEleveurCommandeLivree($commande)
{
    $orderModel = new OrderModel();
    $orderId = intval($commande['id'] ?? 0);
    $items = $orderModel->getOrderItems($orderId);
    $farmerIds = [];

    foreach ($items as $item) {
        if (!empty($item['farmer_id'])) {
            $farmerIds[intval($item['farmer_id'])] = true;
        }
    }

    foreach (array_keys($farmerIds) as $farmerId) {
        $userModel = new UserModel();
        $farmer = $userModel->find($farmerId);
        if (!$farmer) {
            continue;
        }

        $subject = 'Commande #' . $orderId . ' livrée';
        $html = '<p>Bonjour ' . htmlspecialchars($farmer['name'] ?? 'Éleveur') . ',</p>' .
                '<p>La commande <strong>#' . $orderId . '</strong> a été confirmée livrée par le client.</p>' .
                '<p>Merci de vérifier la commande dans votre espace.</p>';
        $alt = 'Bonjour ' . ($farmer['name'] ?? 'Éleveur') . ', la commande #' . $orderId . ' a été confirmée livrée par le client. Merci de vérifier la commande dans votre espace.';

        if (!empty($farmer['email']) && filter_var($farmer['email'], FILTER_VALIDATE_EMAIL)) {
            if (!sendNotificationEmail($farmer['email'], $subject, $html, $alt)) {
                error_log('[NotificationService] Failed to email farmer #' . $farmerId . ' for delivered order #' . $orderId);
            }
        }

        if (!empty($farmer['phone'])) {
            $sms = 'Bonjour ' . ($farmer['name'] ?? 'Éleveur') . ', la commande #' . $orderId . ' a été confirmée livrée par le client. Vérifiez le suivi dans votre espace.';
            if (!sendNotificationSms($farmer['phone'], $sms)) {
                error_log('[NotificationService] Failed SMS farmer #' . $farmerId . ' for delivered order #' . $orderId);
            }
        }
    }

    return true;
}

function notifierLivreurCommandeLivree($commande)
{
    $orderId = intval($commande['id'] ?? 0);
    $livreur = [
        'id'    => $commande['delivery_person_id'] ?? null,
        'name'  => $commande['delivery_person_name'] ?? 'Livreur',
        'email' => $commande['delivery_person_email'] ?? '',
        'phone' => $commande['delivery_person_phone'] ?? '',
    ];

    if (!empty($livreur['email']) && filter_var($livreur['email'], FILTER_VALIDATE_EMAIL)) {
        $subject = 'Commande #' . $orderId . ' livrée avec succès';
        $html = '<p>Bonjour ' . htmlspecialchars($livreur['name'] ?? 'Livreur') . ',</p>' .
                '<p>La commande <strong>#' . $orderId . '</strong> a été confirmée livrée par le client.</p>' .
                '<p>Merci pour votre travail.</p>';
        $alt = 'Bonjour ' . ($livreur['name'] ?? 'Livreur') . ', la commande #' . $orderId . ' a été confirmée livrée par le client. Merci pour votre travail.';

        if (!sendNotificationEmail($livreur['email'], $subject, $html, $alt)) {
            error_log('[NotificationService] Failed to email deliverer #' . intval($livreur['id'] ?? 0) . ' for delivered order #' . $orderId);
        }
    }

    if (!empty($livreur['phone'])) {
        $sms = 'Bonjour ' . ($livreur['name'] ?? 'Livreur') . ', la commande #' . $orderId . ' a été confirmée livrée par le client. Merci pour votre travail.';
        if (!sendNotificationSms($livreur['phone'], $sms)) {
            error_log('[NotificationService] Failed SMS deliverer #' . intval($livreur['id'] ?? 0) . ' for delivered order #' . $orderId);
        }
    }

    return true;
}

function notifierClientFacture($commande, $client)
{
    $orderModel = new OrderModel();
    $orderId = intval($commande['id'] ?? 0);
    $orderNumber = $orderModel->getUserOrderSequence($orderId) ?? $orderId;
    $deliveryTypeLabel = (($commande['delivery_type'] ?? 'home') === 'home') ? 'livraison à domicile' : 'retrait en boutique';
    $deliveryFee = number_format((float)($commande['delivery_fee'] ?? 0), 0, '', ' ');
    $totalPrice = number_format((float)($commande['total_price'] ?? 0), 0, '', ' ');
    $subject = 'Votre facture FarmMarket #' . $orderNumber;
    $html = '<p>Bonjour ' . htmlspecialchars($client['name'] ?? 'Client') . ',</p>' .
            '<p>Votre paiement a bien été confirmé pour la commande <strong>#' . $orderNumber . '</strong>.</p>' .
            '<p><strong>Détails :</strong> ' . htmlspecialchars($deliveryTypeLabel) . ' · Total : ' . htmlspecialchars($totalPrice) . ' FCFA</p>' .
            '<p>La facture a été envoyée à votre adresse email.</p>';
    $alt = 'Bonjour ' . ($client['name'] ?? 'Client') . ', votre paiement a été confirmé pour la commande #' . $orderNumber . '. Détails : ' . $deliveryTypeLabel . ' - Total ' . $totalPrice . ' FCFA. La facture a été envoyée par email.';

        // For client notifications we only send an SMS here to avoid duplicate emails:
        if (!empty($client['phone'])) {
            $sms = 'Commande #' . $orderNumber . ' confirmée. Paiement reçu. ' . ucfirst($deliveryTypeLabel) . '. Total ' . $totalPrice . ' FCFA. Merci pour votre commande FarmMarket.';
            if (!sendNotificationSms($client['phone'], $sms)) {
                error_log('[NotificationService] Failed SMS client #' . intval($client['id'] ?? 0) . ' for order #' . $orderId);
            }
        }

        return true;
}