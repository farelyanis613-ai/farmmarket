<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit');
}

/**
 * Script de test de connexion SMTP (PHPMailer + Gmail)
 * 
 * Usage: php tools/test_smtp.php
 */

echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
echo "  TEST DE CONNEXION SMTP (PHPMailer + Gmail)                 " . PHP_EOL;
echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
echo PHP_EOL;

// Vérifier que config/mail.php existe
if (!file_exists(__DIR__ . '/../config/mail.php')) {
    echo "❌ ERREUR: config/mail.php n'existe pas" . PHP_EOL;
    exit(1);
}

// Vérifier que vendor/autoload.php existe
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "❌ ERREUR: vendor/autoload.php n'existe pas" . PHP_EOL;
    echo "   Exécutez: composer install" . PHP_EOL;
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

echo "1️⃣  Chargement de la configuration..." . PHP_EOL;
echo "   ├─ Host: " . MAIL_HOST . PHP_EOL;
echo "   ├─ Port: " . MAIL_PORT . PHP_EOL;
echo "   ├─ Username: " . MAIL_USERNAME . PHP_EOL;
echo "   ├─ Encryption: " . MAIL_ENCRYPTION . PHP_EOL;
echo "   └─ From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">" . PHP_EOL;
echo PHP_EOL;

// Vérifier que les identifiants sont configurés
if (MAIL_USERNAME === 'votre-email@gmail.com' || MAIL_PASSWORD === 'votre-app-password') {
    echo "⚠️  ATTENTION: Les identifiants ne sont pas configurés !" . PHP_EOL;
    echo "   Éditez config/mail.php ou créez un fichier .env" . PHP_EOL;
    exit(1);
}

echo "2️⃣  Initialisation de PHPMailer..." . PHP_EOL;

try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    // Configuration SMTP
    $mail->isSMTP();
    $mail->SMTPDebug  = MAIL_DEBUG;
    $mail->Debugoutput = 'echo';
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->Timeout    = 10;

    // Paramètres d'expédition
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_USERNAME);  // Envoyer à soi-même pour test
    $mail->Subject = 'Test FarmMarket SMTP - ' . date('Y-m-d H:i:s');
    $mail->isHTML(true);
    
    $mail->Body = <<<HTML
<h2>Test de Configuration SMTP</h2>
<p>Cet email est un test de configuration PHPMailer avec Gmail.</p>
<hr>
<p><strong>Configuration :</strong></p>
<ul>
    <li>Host: MAIL_HOST</li>
    <li>Port: MAIL_PORT</li>
    <li>Username: MAIL_USERNAME</li>
    <li>Encryption: TLS</li>
    <li>Server Time: {$_SERVER['REQUEST_TIME']}</li>
</ul>
<hr>
<p>Si vous recevez cet email, la configuration SMTP fonctionne correctement ! ✅</p>
HTML;

    $mail->AltBody = "Test SMTP - Si vous recevez cet email, la configuration fonctionne.";

    echo "   ├─ From: " . MAIL_FROM_EMAIL . PHP_EOL;
    echo "   ├─ To: " . MAIL_USERNAME . " (test à soi-même)" . PHP_EOL;
    echo "   └─ Subject: " . $mail->Subject . PHP_EOL;
    echo PHP_EOL;

    echo "3️⃣  Connexion SMTP..." . PHP_EOL;
    
    // Tester uniquement la connexion
    if (!$mail->smtpConnect()) {
        echo "   ❌ Échec de la connexion SMTP" . PHP_EOL;
        echo "   Erreur: " . $mail->ErrorInfo . PHP_EOL;
        exit(1);
    }
    
    echo "   ✅ Connexion réussie" . PHP_EOL;
    $mail->smtpClose();
    echo PHP_EOL;

    echo "4️⃣  Envoi du test..." . PHP_EOL;
    
    if (!$mail->send()) {
        echo "   ❌ Échec de l'envoi" . PHP_EOL;
        echo "   Erreur: " . $mail->ErrorInfo . PHP_EOL;
        exit(1);
    }

    echo "   ✅ Email envoyé avec succès !" . PHP_EOL;
    echo PHP_EOL;

    echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
    echo "✅ TEST RÉUSSI - Votre configuration SMTP est opérationnelle" . PHP_EOL;
    echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
    echo PHP_EOL;
    echo "Prochaines étapes:" . PHP_EOL;
    echo "  1. Vérifiez que l'email test est arrivé dans votre boîte" . PHP_EOL;
    echo "  2. Testez le flux checkout → facture par email" . PHP_EOL;
    echo "  3. Présentez au jury avec confiance ! 🎉" . PHP_EOL;

} catch (Exception $e) {
    echo PHP_EOL;
    echo "   ❌ Exception: " . $e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
    echo "❌ TEST ÉCHOUÉ" . PHP_EOL;
    echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
    echo PHP_EOL;
    echo "Possibilités :" . PHP_EOL;
    echo "  • Le nom d'utilisateur ou le mot de passe est incorrect" . PHP_EOL;
    echo "  • La 2-Step Verification n'est pas activée sur le compte Gmail" . PHP_EOL;
    echo "  • L'App Password n'a pas été généré correctement" . PHP_EOL;
    echo "  • La connexion internet ou le firewall bloque le port 587" . PHP_EOL;
    echo PHP_EOL;
    echo "Vérifications:" . PHP_EOL;
    echo "  1. Config/mail.php - Les identifiants sont-ils corrects ?" . PHP_EOL;
    echo "  2. Gmail - La 2FA est-elle activée ?" . PHP_EOL;
    echo "  3. Gmail - L'App Password a-t-il été régénéré ?" . PHP_EOL;
    echo "  4. Firewall - Le port 587 est-il ouvert ?" . PHP_EOL;
    exit(1);
}
