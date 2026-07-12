<?php

require 'vendor/autoload.php';
require 'config.php';

use AfricasTalking\SDK\AfricasTalking;

$username = AT_USERNAME;
$apiKey   = AT_API_KEY;

$AT = new AfricasTalking($username, $apiKey);
$sms = $AT->sms();

try {
    $result = $sms->send([
        'to' => '+2290193773042', // Remplace par ton numéro
        'message' => 'Test SMS FarmMarket'
    ]);

    print_r($result);

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . PHP_EOL;
}