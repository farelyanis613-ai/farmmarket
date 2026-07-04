<?php

// Interdire l'accès via HTTP : ce script doit être invoqué uniquement en CLI
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../NotificationService.php';

// Usage en CLI : php tools/test_africastalking_sms.php +229XXXXXXXX 'Message'

$to = $argv[1] ?? '';
$message = $argv[2] ?? '';

if ($to === '' || $message === '') {
    echo "Usage: php tools/test_africastalking_sms.php +229XXXXXXXX 'Message'\n";
    exit(1);
}

$result = sendNotificationSms($to, $message);
if ($result) {
    echo "SMS envoyé avec succès à $to\n";
    exit(0);
}

echo "Échec de l'envoi SMS à $to. Consultez le log d'erreur.\n";
exit(1);
