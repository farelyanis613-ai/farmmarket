<?php

require_once __DIR__ . '/bootstrap.php';
loadEnvFile(__DIR__ . '/../.env');

$atUsername = env('AT_USERNAME', '');
$atApiKey = env('AT_API_KEY', '');
$atFrom = env('AT_FROM', '');
$smsProvider = env('SMS_PROVIDER', 'africastalking');
$defaultSmsEnabled = ($smsProvider === 'africastalking' && $atUsername !== '' && $atApiKey !== '');

if (!defined('AT_USERNAME')) {
    define('AT_USERNAME', $atUsername);
}
if (!defined('AT_API_KEY')) {
    define('AT_API_KEY', $atApiKey);
}
if (!defined('AT_FROM')) {
    define('AT_FROM', $atFrom);
}
if (!defined('SMS_PROVIDER')) {
    define('SMS_PROVIDER', $smsProvider);
}
if (!defined('SMS_ENABLED')) {
    define('SMS_ENABLED', envBool('SMS_ENABLED', $defaultSmsEnabled));
}
