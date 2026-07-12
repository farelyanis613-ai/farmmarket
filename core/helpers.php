<?php

function normalizeStatus($status)
{
    $status = strtolower(trim((string)$status));
    $status = str_replace(
        ['é', 'è', 'ê', 'ë', 'à', 'á', 'â', 'ä', 'ç', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ÿ'],
        ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'a', 'c', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'y'],
        $status
    );
    $status = preg_replace('/[^a-z0-9]+/', '_', $status);
    $status = trim($status, '_');

    $mappings = [
        'en_attente' => 'pending',
        'pending' => 'pending',
        'en_cours' => 'in_progress',
        'in_progress' => 'in_progress',
        'inprogress' => 'in_progress',
        'accepted' => 'accepted',
        'acceptee' => 'accepted',
        'livre' => 'delivered',
        'livree' => 'delivered',
        'delivered' => 'delivered',
        'echouee' => 'failed',
        'failed' => 'failed',
        'refusee' => 'rejected',
        'rejected' => 'rejected',
        'annulee' => 'cancelled',
        'cancelled' => 'cancelled',
    ];

    return $mappings[$status] ?? $status;
}

if (!defined('HOME_DELIVERY_FEE')) {
    define('HOME_DELIVERY_FEE', 2000);
}

function getDeliveryFee($deliveryType = 'home')
{
    return trim(strtolower((string)$deliveryType)) === 'home' ? HOME_DELIVERY_FEE : 0;
}

function formatStatusLabel($status)
{
    $status = normalizeStatus($status);
    $labels = [
        'pending' => 'En attente',
        'in_progress' => 'En cours',
        'accepted' => 'Acceptée',
        'delivered' => 'Livré',
        'failed' => 'Échouée',
        'rejected' => 'Rejetée',
    ];

    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function getStatusBadgeClasses($status)
{
    switch (normalizeStatus($status)) {
        case 'delivered':
            return 'bg-green-600 text-white';
        case 'failed':
        case 'rejected':
            return 'bg-red-600 text-white';
        case 'accepted':
            return 'bg-blue-600 text-white';
        case 'in_progress':
            return 'bg-indigo-600 text-white';
        case 'pending':
        default:
            return 'bg-yellow-500 text-white';
    }
}

function isFailedStatus($status)
{
    return normalizeStatus($status) === 'failed';
}

function isDeliveredStatus($status)
{
    return normalizeStatus($status) === 'delivered';
}

function isPendingStatus($status)
{
    return normalizeStatus($status) === 'pending';
}

function normalizePhone($phone)
{
    $phone = trim((string)$phone);
    if ($phone === '') {
        return '';
    }

    $phone = preg_replace('/[^\d+]/', '', $phone);

    if (preg_match('/^\+229(\d{10})$/', $phone, $matches)) {
        return '+229' . $matches[1];
    }

    if (preg_match('/^229(\d{10})$/', $phone, $matches)) {
        return '+229' . $matches[1];
    }

    if (preg_match('/^\+229(\d{8})$/', $phone, $matches)) {
        return '+229' . $matches[1];
    }

    if (preg_match('/^229(\d{8})$/', $phone, $matches)) {
        return '+229' . $matches[1];
    }

    if (preg_match('/^(\d{10})$/', $phone, $matches)) {
        return '+229' . $matches[1];
    }

    if (preg_match('/^(\d{8})$/', $phone, $matches)) {
        return '+229' . $matches[1];
    }

    return $phone;
}

function normalizePhoneForSms($phone)
{
    $phone = trim((string)$phone);
    if ($phone === '') {
        return '';
    }

    // Remove all non-digit characters except leading plus
    $clean = preg_replace('/[^\d+]/', '', $phone);
    if ($clean === '') {
        return '';
    }

    if ($clean[0] !== '+') {
        if (strpos($clean, '229') === 0) {
            $clean = '+' . $clean;
        } elseif (preg_match('/^\d{8}$/', $clean) || preg_match('/^\d{10}$/', $clean)) {
            $clean = '+229' . $clean;
        }
    }

    return $clean;
}

function isValidBeninPhone($phone)
{
    // Accept human-entered Benin numbers with spaces or separators by normalizing first.
    $normalized = normalizePhone($phone);
    return preg_match('/^\+229\d{8}$/', trim((string)$normalized)) === 1
        || preg_match('/^\+229\d{10}$/', trim((string)$normalized)) === 1;
}

function formatPhoneDisplay($phone)
{
    $normalized = normalizePhone($phone);
    $compact = str_replace(' ', '', $normalized);

    if (preg_match('/^\+229(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', $compact, $matches)) {
        return '+229 ' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5];
    }

    if (preg_match('/^\+229(\d{2})(\d{2})(\d{2})(\d{2})$/', $compact, $matches)) {
        return '+229 ' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4];
    }

    return $normalized;
}

/**
 * Générer un token CSRF et le stocker dans la session
 */
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Obtenir le token CSRF actuel (crée s'il n'existe pas)
 */
function getCsrfToken()
{
    return generateCsrfToken();
}

/**
 * Vérifier si le token CSRF fourni est valide
 */
function verifyCsrfToken($token = null)
{
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Envoyer un SMS via Africa's Talking (si configuré)
 * Retourne true si envoyé, false sinon
 */
function sendSms($to, $message)
{
    $to = trim((string)$to);
    $message = trim((string)$message);
    if ($to === '' || $message === '') {
        error_log('[sendSms] Missing recipient or message');
        return false;
    }

    if (file_exists(__DIR__ . '/../config/sms.php')) {
        require_once __DIR__ . '/../config/sms.php';
    }

    if (!defined('SMS_ENABLED') || !SMS_ENABLED) {
        error_log('[sendSms] SMS_DISABLED - message not sent to ' . $to);
        return false;
    }

    if (!function_exists('sendNotificationSms')) {
        require_once __DIR__ . '/../NotificationService.php';
    }

    return sendNotificationSms($to, $message);
}
 