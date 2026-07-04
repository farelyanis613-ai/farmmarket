# Configuration PHPMailer - Envoi d'emails SMTP Gmail

## ✅ Installation complète

PHPMailer v6.12.0 a été installé avec succès via Composer.

```
vendor/autoload.php ✓
vendor/phpmailer/phpmailer/ ✓
```

## 📧 Configuration SMTP Gmail

Le fichier `config/mail.php` a été créé avec les variables de configuration.

### Étapes de configuration

#### 1️⃣ Préparer votre compte Gmail

1. Allez sur [https://myaccount.google.com/security](https://myaccount.google.com/security)
2. Activez **"2-Step Verification"** (Vérification en deux étapes)
3. Allez sur [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
   - Sélectionnez "Mail" et "Windows Computer"
   - Google vous génère un mot de passe d'application à 16 caractères
   - Copiez-le (sans espaces)

#### 2️⃣ Configurer les identifiants

Deux options :

**Option A : Hardcoder les identifiants (rapide, développement)**

Éditez `config/mail.php` :

```php
define('MAIL_USERNAME', 'votre-email@gmail.com');
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx');  // 16 caractères
define('MAIL_FROM_EMAIL', 'votre-email@gmail.com');
```

**Option B : Utiliser un fichier `.env` (recommandé, production)**

Créez `.env` à la racine du projet :

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_EMAIL=votre-email@gmail.com
MAIL_FROM_NAME=FarmMarket
```

Puis chargez-le dans `index.php` au démarrage :

```php
// config/mail.php ou au début d'index.php
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}
```

## 🔧 Modification de sendInvoiceEmail()

La fonction `sendInvoiceEmail($orderId, $recipientEmail)` a été remplacée :

**Avant (mail() PHP classique) :**
```php
$result = mail($recipientEmail, $subject, $message, $headers);
```

**Après (PHPMailer SMTP) :**
```php
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->isSMTP();
$mail->Host = MAIL_HOST;
$mail->SMTPAuth = true;
$mail->Username = MAIL_USERNAME;
$mail->Password = MAIL_PASSWORD;
$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = MAIL_PORT;
// ... configuration et envoi
```

### Améliorations

✅ **Email HTML professionnel** - Facture mise en forme avec CSS
✅ **Email texte alternatif** - Fallback pour les clients sans HTML
✅ **Gestion des erreurs** - Try/catch avec logs détaillés
✅ **Encodage UTF-8** - Support des caractères spéciaux (FCFA, accents)
✅ **MIME multipart** - Support des deux formats (HTML + texte)
✅ **Template HTML responsive** - Fonctionne sur tous les appareils

## 📨 Flux d'envoi

### 1. Utilisateur remplit le formulaire de paiement
- Option "Recevoir la facture par email" (checkbox)
- Adresse email pré-remplie (depuis la session)

### 2. À la validation du paiement (checkoutComplete)
```php
if (!empty($_POST['receipt_email']) && !empty($_POST['receipt_email_address'])) {
    sendInvoiceEmail($orderId, $_POST['receipt_email_address']);
}
```

### 3. sendInvoiceEmail() est appelée
- Récupère les détails de la commande
- Génère le contenu HTML
- Crée une instance PHPMailer
- Configure SMTP Gmail
- Envoie par TLS sur le port 587
- Loggue le succès/l'erreur

### 4. Destinataire reçoit un email
- **De :** FarmMarket (MAIL_FROM_EMAIL)
- **À :** email entré par l'utilisateur
- **Sujet :** Confirmation de commande FarmMarket #123
- **Corps :** 
  - HTML professionnel avec design responsif
  - Détail des produits, montants, adresse de livraison
  - Lien pour télécharger la facture PDF

## 🧪 Tests

### Test simple en CLI

```bash
cd "C:\Users\Yanis\Desktop\SOUTENANCE\FARMMARKET"
php -r "
require 'vendor/autoload.php';
require 'config/mail.php';

\$mail = new PHPMailer\PHPMailer\PHPMailer(true);
\$mail->isSMTP();
\$mail->Host = MAIL_HOST;
\$mail->SMTPAuth = true;
\$mail->Username = MAIL_USERNAME;
\$mail->Password = MAIL_PASSWORD;
\$mail->SMTPSecure = 'tls';
\$mail->Port = MAIL_PORT;

echo 'SMTP Settings Loaded:' . PHP_EOL;
echo 'Host: ' . MAIL_HOST . PHP_EOL;
echo 'Port: ' . MAIL_PORT . PHP_EOL;
echo 'Username: ' . MAIL_USERNAME . PHP_EOL;
echo 'Encryption: TLS' . PHP_EOL;
"
```

### Test avec envoi réel

À partir du checkout FarmMarket :
1. Ajouter un produit au panier
2. Aller au checkout
3. Cocher "Recevoir la facture par email"
4. Entrer l'adresse email du jury
5. Valider la commande
6. **Vérifier la boîte mail** - L'email devrait arriver en 1-5 secondes

## 📝 Logs des erreurs

Les erreurs d'envoi sont loggées dans le PHP error_log :

```
[sendInvoiceEmail] Email sent successfully to user@example.com for order #123
[sendInvoiceEmail] Exception: SMTP Error: Connection timeout (Order #456, Email: jury@example.com)
[sendInvoiceEmail] Invalid email: (empty)
```

Localisation : Généralement `C:\xampp1\php\logs\php_error.log` ou `php error_log`

## ⚠️ Dépannage

### L'email n'est pas reçu

1. **Vérifiez les identifiants :**
   - Gmail ne reconnaît pas les mots de passe standard
   - Utilisez **obligatoirement** un "App Password" (16 caractères)
   - Pas d'espace dans le mot de passe

2. **Vérifiez la 2FA :**
   - La "2-Step Verification" doit être activée
   - Les "App Passwords" nécessitent la 2FA

3. **Vérifiez les logs :**
   ```bash
   tail -f "C:\xampp1\php\logs\php_error.log"
   ```

4. **Testez la connexion SMTP :**
   ```bash
   telnet smtp.gmail.com 587
   ```

### Erreur "Username and Password not accepted"

- Vérifiez que les guillemets ne sont pas inclus dans `MAIL_PASSWORD`
- Vérifiez que le compte Gmail a la 2FA activée
- Régénérez l'App Password depuis myaccount.google.com

### Erreur "Unexpected response code"

- Vérifiez que `MAIL_PORT` est 587 (pas 465 ou 25)
- Vérifiez que `MAIL_ENCRYPTION` est 'tls' (pas 'ssl')

## 📚 Ressources

- [PHPMailer GitHub](https://github.com/PHPMailer/PHPMailer)
- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)
- [Google SMTP Settings](https://support.google.com/mail/answer/7126229)

## 🎯 Prochaines étapes

✅ PHPMailer installé et configuré
✅ Fonction sendInvoiceEmail() remplacée
✅ Templates HTML/texte créés
✅ Gestion des erreurs complète

📋 **À faire :**
1. Configurer config/mail.php avec vos identifiants Gmail
2. Tester le flux checkout → email
3. Vérifier que les emails arrivent sur les comptes de test
4. Présenter au jury avec les vrais identifiants pendant la soutenance

Bonne chance pour votre soutenance ! 🎉
