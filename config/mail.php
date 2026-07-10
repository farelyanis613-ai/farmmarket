<?php

require_once __DIR__ . '/bootstrap.php';
loadEnvFile(__DIR__ . '/../.env');

// Configuration SMTP (Gmail) — conservée pour référence, plus utilisée pour l'envoi
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', intval(env('MAIL_PORT', 587)));
define('MAIL_USERNAME', env('MAIL_USERNAME', 'votre-email@gmail.com'));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', 'votre-app-password'));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')); // TLS pour port 587
define('MAIL_DEBUG', intval(env('MAIL_DEBUG', 0)));
define('MAIL_FROM_EMAIL', env('MAIL_FROM_EMAIL', MAIL_USERNAME));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'FarmMarket'));

// Clé API Brevo (envoi email via HTTPS, contournement du blocage SMTP sur Railway)
define('BREVO_API_KEY', env('BREVO_API_KEY', ''));

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
 *
 * ═══════════════════════════════════════════════════════════
 * ENVOI RÉEL DES EMAILS : API BREVO (pas SMTP)
 * ═══════════════════════════════════════════════════════════
 *
 * Railway bloque les ports SMTP (25, 465, 587) sur les plans
 * gratuits (Free/Trial/Hobby). NotificationService.php utilise
 * donc l'API HTTPS de Brevo (https://api.brevo.com) pour l'envoi
 * réel, plutôt que PHPMailer en SMTP.
 *
 * Pour configurer :
 * 1. Compte gratuit sur https://www.brevo.com/
 * 2. Vérifier l'expéditeur (Senders, domaine, IP → Senders)
 * 3. Générer une clé API (SMTP et API → API Keys)
 * 4. Ajouter BREVO_API_KEY=... dans .env (local) ou dans les
 *    Variables du service sur Railway (production)
 */