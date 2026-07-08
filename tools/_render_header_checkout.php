<?php
chdir(__DIR__ . '/..');
$_GET['action'] = 'checkout';
ob_start();
include 'views/partials/header.php';
$html = ob_get_clean();
file_put_contents('tools/_header_checkout_output.html', $html);
echo "Wrote tools/_header_checkout_output.html\n";
