<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/database.php';

function login()
{
    $controller = new Controller();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        } else {
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $errors[] = 'Email invalide.';
            } else {
                $password = $_POST['password'] ?? '';
                $as = isset($_POST['as']) ? trim($_POST['as']) : (isset($_GET['as']) ? trim($_GET['as']) : null);
                $next = trim($_POST['next'] ?? $_GET['next'] ?? '');

                $userModel = new UserModel();
                $user = $userModel->findByEmail($email);

                // Brute-force protection: use login_attempts table when available, fallback to session
                $identifier = strtolower(trim($email ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown')));
                $maxAttempts = 5;
                $lockMinutes = 15;

                $isLocked = false;
                try {
                    if (isset($pdo)) {
                        $stmt = $pdo->prepare('SELECT attempts, locked_until FROM login_attempts WHERE identifier = ?');
                        $stmt->execute([$identifier]);
                        $la = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($la && !empty($la['locked_until']) && strtotime($la['locked_until']) > time()) {
                            $isLocked = true;
                            $remaining = ceil((strtotime($la['locked_until']) - time()) / 60);
                            $errors[] = 'Trop de tentatives. Réessayez dans ' . $remaining . ' minutes.';
                        }
                    }
                } catch (Exception $e) {
                    // ignore DB errors on attempts check; we'll fallback to session below
                }

                // Session fallback lock check
                if (!$isLocked && empty($errors)) {
                    if (!empty($_SESSION['login_attempts'][$identifier]['locked_until']) && strtotime($_SESSION['login_attempts'][$identifier]['locked_until']) > time()) {
                        $isLocked = true;
                        $remaining = ceil((strtotime($_SESSION['login_attempts'][$identifier]['locked_until']) - time()) / 60);
                        $errors[] = 'Trop de tentatives. Réessayez dans ' . $remaining . ' minutes.';
                    }
                }

                if ($isLocked) {
                    // render immediately without checking password to avoid timing attacks
                    $controller->render('auth/login.php', ['errors' => $errors, 'next' => trim($_GET['next'] ?? ''), 'csrf_token' => getCsrfToken()]);
                    return;
                }

                if ($user && password_verify($password, $user['password'])) {
                    if ($as && $user['role'] !== $as) {
                        $errors[] = 'Accès non autorisé pour ce type de compte.';
                    } else {
                        // Prevent session fixation
                        if (session_status() !== PHP_SESSION_ACTIVE) {
                            session_start();
                        }
                        session_regenerate_id(true);

                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                            'name' => $user['name'],
                            'farm_name' => $user['farm_name'] ?? null,
                        ];

                        $_SESSION['success'] = 'Vous êtes connecté ! Bienvenue ' . htmlspecialchars($user['name']) . '.';

                        $redirects = [
                            'farmer' => 'index.php?action=farmer/dashboard',
                            'delivery' => 'index.php?action=delivery/dashboard',
                            'admin' => 'index.php?action=admin',
                            'client' => 'index.php?action=home',
                        ];

                        $redirect = $redirects[$user['role']] ?? 'index.php?action=home';
                        $allowedNext = ['orders', 'products', 'cart', 'checkout', 'checkout/mobile'];
                        if ($user['role'] === 'client' && in_array($next, $allowedNext, true)) {
                            $redirect = 'index.php?action=' . $next;
                        }

                        // On successful login reset login attempts
                        try {
                            if (isset($pdo)) {
                                $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE identifier = ?');
                                $stmt->execute([$identifier]);
                            }
                        } catch (Exception $e) { /* ignore */ }
                        if (isset($_SESSION['login_attempts'][$identifier])) {
                            unset($_SESSION['login_attempts'][$identifier]);
                        }

                        $controller->redirect($redirect);
                    }
                } else {
                    $errors[] = 'Identifiants incorrects.';

                    // Record failed attempt (DB if available, else session)
                    try {
                        if (isset($pdo)) {
                            $stmt = $pdo->prepare('SELECT attempts FROM login_attempts WHERE identifier = ?');
                            $stmt->execute([$identifier]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($row) {
                                $attempts = intval($row['attempts']) + 1;
                                $lockedUntil = null;
                                if ($attempts >= $maxAttempts) {
                                    $lockedUntil = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
                                }
                                $u = $pdo->prepare('UPDATE login_attempts SET attempts = ?, last_failed = NOW(), locked_until = ? WHERE identifier = ?');
                                $u->execute([$attempts, $lockedUntil, $identifier]);
                            } else {
                                $lockedUntil = null;
                                $attempts = 1;
                                if ($attempts >= $maxAttempts) {
                                    $lockedUntil = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
                                }
                                $i = $pdo->prepare('INSERT INTO login_attempts (identifier, attempts, last_failed, locked_until) VALUES (?, ?, NOW(), ?)');
                                $i->execute([$identifier, $attempts, $lockedUntil]);
                            }
                        } else {
                            // session fallback
                            if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = [];
                            if (!isset($_SESSION['login_attempts'][$identifier])) {
                                $_SESSION['login_attempts'][$identifier] = ['attempts' => 1, 'last_failed' => time()];
                            } else {
                                $_SESSION['login_attempts'][$identifier]['attempts']++;
                                $_SESSION['login_attempts'][$identifier]['last_failed'] = time();
                            }
                            if ($_SESSION['login_attempts'][$identifier]['attempts'] >= $maxAttempts) {
                                $_SESSION['login_attempts'][$identifier]['locked_until'] = time() + ($lockMinutes * 60);
                            }
                        }
                    } catch (Exception $e) { /* ignore logging errors */ }
                }
            }
        }
    }

    $controller->render('auth/login.php', ['errors' => $errors, 'next' => trim($_GET['next'] ?? ''), 'csrf_token' => getCsrfToken()]);
}

function register()
{
    $controller = new Controller();
    $errors = [];
    $next = trim($_POST['next'] ?? $_GET['next'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Jeton CSRF invalide.';
        } else {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm'] ?? '';
            $role = 'client';

            if (empty($name) || !$email || empty($password) || empty($confirm)) {
                $errors[] = 'Tous les champs sont requis.';
            } else if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                $userModel = new UserModel();
                if ($userModel->findByEmail($email)) {
                    $errors[] = 'Cet email est déjà utilisé.';
                } else {
                    $userData = [
                        'name' => $name,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => $role,
                    ];

                    global $pdo;
                    $userModel->create($userData);
                    $userId = $pdo->lastInsertId();
                    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id' => $userId,
                        'email' => $email,
                        'role' => $role,
                        'name' => $name,
                        'farm_name' => null,
                    ];

                    $_SESSION['success'] = 'Vous êtes connecté ! Bienvenue ' . htmlspecialchars($name) . '.';

                    $allowedNext = ['orders', 'products', 'cart', 'checkout'];
                    $redirect = 'index.php?action=orders';
                    if (in_array($next, $allowedNext, true)) {
                        $redirect = 'index.php?action=' . $next;
                    }

                    $controller->redirect($redirect);
                }
            }
        }
    }

    $controller->render('auth/register.php', ['errors' => $errors, 'next' => $next, 'csrf_token' => getCsrfToken()]);
}

function logout()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Preserve a logout flash message only
    $successMessage = 'Vous êtes déconnecté.';
    $_SESSION = [];
    $_SESSION['success'] = $successMessage;

    // Regenerate session id to clear previous state
    session_regenerate_id(true);

    // Optional next parameter to control post-logout redirect (e.g., to login)
    $next = trim($_GET['next'] ?? '');
    if ($next === 'login') {
        header('Location: index.php?action=login');
    } else {
        header('Location: index.php');
    }
    exit;
}