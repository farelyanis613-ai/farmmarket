<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/OrderModel.php';

use AfricasTalking\SDK\AfricasTalking;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function sendNotificationEmail($toEmail, $subject, $htmlBody, $altBody = '')
{
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('[NotificationService] Invalid email: ' . var_export($toEmail, true));
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = defined('MAIL_DEBUG') ? MAIL_DEBUG : 0;
        $mail->Debugoutput = 'error_log';
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->Port = MAIL_PORT;

        if (defined('MAIL_ENCRYPTION') && strtolower(MAIL_ENCRYPTION) === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $senderEmail = MAIL_FROM_EMAIL ?: MAIL_USERNAME;
        $senderName = MAIL_FROM_NAME ?: 'FarmMarket';

        $mail->setFrom($senderEmail, $senderName);
        $mail->Sender = $senderEmail;
        $mail->addAddress($toEmail);
        $mail->addReplyTo($senderEmail, $senderName);
        $mail->addCustomHeader('X-Mailer', 'FarmMarket Mailer');
        $mail->Priority = 3;
        $mail->Timeout = 10;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];

        return $mail->send();
    } catch (PHPMailerException $e) {
        error_log('[NotificationService] Email exception: ' . $e->getMessage());
        return false;
    }
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
        error_log('[NotificationService] Invalid phone number: ' . var_export($toPhone, true));
        return false;
    }

    $client = getAfricasTalkingClient();
    if (!$client) {
        error_log('[NotificationService] Africa\'s Talking client unavailable');
        return false;
    }

    try {
        $sms = $client->sms();
        $options = [
            'to' => $toPhone,
            'message' => $message,
        ];
        if (!empty(AT_FROM)) {
            $options['from'] = AT_FROM;
        }
        $result = $sms->send($options);

        if (!empty($result['status']) && $result['status'] === 'success') {
            return true;
        }

        error_log('[NotificationService] SMS API error: ' . var_export($result, true));
        return false;
    } catch (Exception $e) {
        error_log('[NotificationService] SMS exception: ' . $e->getMessage());
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
            $sms = 'Nouvelle commande #' . $orderId . ' recue. Connectez-vous pour la traiter.';
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
        $sms = 'Commande #' . $orderId . ' assignée. Connectez-vous pour voir les détails.';
        if (!sendNotificationSms($livreur['phone'], $sms)) {
            error_log('[NotificationService] Failed SMS deliverer #' . intval($livreur['id'] ?? 0) . ' for order #' . $orderId);
        }
    }

    return true;
}

function notifierClientFacture($commande, $client)
{
    $orderId = intval($commande['id'] ?? 0);
    $subject = 'Votre facture FarmMarket #' . $orderId;
    $html = '<p>Bonjour ' . htmlspecialchars($client['name'] ?? 'Client') . ',</p>' .
            '<p>Votre paiement a été confirmé pour la commande <strong>#' . $orderId . '</strong>.</p>' .
            '<p>La facture a été envoyée à votre adresse email.</p>';
    $alt = 'Bonjour ' . ($client['name'] ?? 'Client') . ', votre paiement a été confirmé pour la commande #' . $orderId . '. La facture a été envoyée par email.';

        // For client notifications we only send an SMS here to avoid duplicate emails:
        if (!empty($client['phone'])) {
            $sms = 'Paiement confirmé pour la commande #' . $orderId . '. Merci pour votre commande.';
            if (!sendNotificationSms($client['phone'], $sms)) {
                error_log('[NotificationService] Failed SMS client #' . intval($client['id'] ?? 0) . ' for order #' . $orderId);
            }
        }

        return true;
}
