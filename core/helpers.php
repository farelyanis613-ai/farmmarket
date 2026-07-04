<?php

function normalizeStatus($status)
{
    $status = strtolower(trim((string)$status));
    $status = str_replace(['é', 'è', 'ê', 'ë'], 'e', $status);

    $mappings = [
        'en attente' => 'pending',
        'pending' => 'pending',
        'en cours' => 'in_progress',
        'in_progress' => 'in_progress',
        'accepted' => 'accepted',
        'acceptée' => 'accepted',
        'acceptee' => 'accepted',
        'livre' => 'delivered',
        'livree' => 'delivered',
        'livré' => 'delivered',
        'delivered' => 'delivered',
        'échouee' => 'failed',
        'echouee' => 'failed',
        'failed' => 'failed',
        'refusee' => 'rejected',
        'refusée' => 'rejected',
        'rejected' => 'rejected',
        'rejected' => 'rejected',
    ];

    return $mappings[$status] ?? $status;
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
