<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Controller.php';

function profile()
{
    $controller = new Controller();
    
    if (!isset($_SESSION['user'])) {
        $controller->redirect('index.php?action=login');
    }

    $userModel = new UserModel();
    $user = $userModel->find($_SESSION['user']['id']);
    $errors = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $rawPhone = trim($_POST['phone'] ?? '');
        $phone = normalizePhone($rawPhone);
        $address = htmlspecialchars(trim($_POST['address'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($phone) || empty($address)) {
            $errors[] = 'Le téléphone et l\'adresse sont requis.';
        }

        if ($phone !== '' && !isValidBeninPhone($phone)) {
            $errors[] = 'Numéro de téléphone invalide. Utilisez le format +229 01 XX XX XX XX.';
        }

        if ($password !== '') {
            if ($password !== $confirmPassword) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        // If changing email, ensure uniqueness
        if (!empty($email) && $email !== $user['email']) {
            $existing = $userModel->findByEmail($email);
            if ($existing) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }
        }

        if (empty($errors)) {
            $updateData = [
                'phone' => $phone,
                'address' => $address,
            ];
            if ($password !== '') {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            if (!empty($name) && $name !== $user['name']) {
                $updateData['name'] = $name;
            }
            if (!empty($email) && $email !== $user['email']) {
                $updateData['email'] = $email;
            }

            if (!empty($updateData)) {
                $userModel->update($_SESSION['user']['id'], $updateData);

                // Update session values
                if (isset($updateData['phone'])) $_SESSION['user']['phone'] = $updateData['phone'];
                if (isset($updateData['address'])) $_SESSION['user']['address'] = $updateData['address'];
                if (isset($updateData['name'])) $_SESSION['user']['name'] = $updateData['name'];
                if (isset($updateData['email'])) $_SESSION['user']['email'] = $updateData['email'];
            }

            $success = true;
            $user = $userModel->find($_SESSION['user']['id']);
        }
    }

    $controller->render('account/profile.php', [
        'user' => $user,
        'errors' => $errors,
        'success' => $success
    ]);
}
