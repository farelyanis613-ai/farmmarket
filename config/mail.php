<?php

require_once __DIR__ . '/bootstrap.php';
loadEnvFile(__DIR__ . '/../.env');

// Configuration SMTP (Gmail)
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', intval(env('MAIL_PORT', 587)));
define('MAIL_USERNAME', env('MAIL_USERNAME', 'votre-email@gmail.com'));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', 'votre-app-password'));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')); // TLS pour port 587
define('MAIL_DEBUG', intval(env('MAIL_DEBUG', 0)));
define('MAIL_FROM_EMAIL', env('MAIL_FROM_EMAIL', MAIL_USERNAME));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'FarmMarket'));

/**
 * ═══════════════════════════════════════════════════════════
 * INSTRUCTIONS DE CONFIGURATION GMAIL
 * ═══════════════════════════════════════════════════════════
 * 
 * 1. Activez "Accounts and Import" dans Gmail Settings :
 *    https://myaccount.google.com/security
 * 
 * 2. Activez "2-Step Verification"
 * 
 * 3. Générez une "App Password" pour "Mail" et "Windows Computer" :
 *    https://myaccount.google.com/apppasswords
 *    → Vous recevrez un mot de passe à 16 caractères
 * 
 * 4. Remplacez :
 *    MAIL_USERNAME = votre-email@gmail.com
 *    MAIL_PASSWORD = le mot de passe d'application (16 caractères, sans espaces)
 * 
 * 5. Optionnel : créez un fichier .env à la racine et utilisez getenv()
 *    pour éviter de hardcoder les identifiants :
 *    
 *    .env :
 *    ─────────────────────
 *    MAIL_HOST=smtp.gmail.com
 *    MAIL_PORT=587
 *    MAIL_USERNAME=votreemail@gmail.com
 *    MAIL_PASSWORD=votre-app-password-16-chars
 *    MAIL_FROM_EMAIL=votreemail@gmail.com
 *    MAIL_FROM_NAME=FarmMarket
 * 
 * 6. Chargez le fichier .env au démarrage (index.php ou config) :
 *    
 *    if (file_exists(__DIR__ . '/.env')) {
 *        $env = parse_ini_file(__DIR__ . '/.env');
 *        foreach ($env as $key => $value) {
 *            putenv("$key=$value");
 *        }
 *    }
 */
