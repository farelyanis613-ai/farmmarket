<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Marché fermier en ligne, produits frais locaux et livraison rapide.">
    <?php
    $pageTitle = $pageTitle ?? 'Farmmarket';
    $googleMapsKey = getenv('VITE_GOOGLE_MAPS_API_KEY') ?: ($_ENV['VITE_GOOGLE_MAPS_API_KEY'] ?? '');
    ?>
    <meta name="google-maps-api-key" content="<?= htmlspecialchars($googleMapsKey) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="public/css/style.css">

    <script defer src="public/js/googleMapsLoader.js"></script>
    <script defer src="public/js/googleMapsIntegration.js"></script>
    <script defer src="public/js/app.js"></script>
</head>
<?php
$pageTitle = $pageTitle ?? 'Farmmarket';
$currentAction = $_GET['action'] ?? 'home';
$userRole = $_SESSION['user']['role'] ?? null;
$isFarmer = $userRole === 'farmer';
$isDelivery = $userRole === 'delivery';

function isActiveNav(string $target, string $currentAction): bool {
    return $currentAction === $target || strpos($currentAction, $target . '/') === 0;
}

$logoLink = 'index.php';
if (isset($_SESSION['user'])) {
    if ($userRole === 'delivery') {
        $logoLink = 'index.php?action=delivery/dashboard';
    } elseif ($userRole === 'farmer') {
        $logoLink = 'index.php?action=farmer/dashboard';
    }
}

$badgeClass = $isDelivery ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700';
$mainLinks = [];
if (!isset($_SESSION['user'])) {
    $mainLinks = [
        ['href' => 'index.php?action=products', 'label' => 'Produits', 'active' => isActiveNav('products', $currentAction)],
        ['href' => 'index.php?action=cart', 'label' => 'Panier', 'active' => isActiveNav('cart', $currentAction)],
        ['href' => 'index.php?action=login', 'label' => 'Connexion', 'active' => isActiveNav('login', $currentAction)],
    ];
} elseif ($userRole === 'client') {
    $mainLinks = [
        ['href' => 'index.php?action=products', 'label' => 'Produits', 'active' => isActiveNav('products', $currentAction)],
        ['href' => 'index.php?action=cart', 'label' => 'Panier', 'active' => isActiveNav('cart', $currentAction)],
        ['href' => 'index.php?action=orders', 'label' => 'Commandes', 'active' => isActiveNav('orders', $currentAction)],
    ];
} elseif ($userRole === 'farmer') {
    $mainLinks = [
        ['href' => 'index.php?action=farmer/dashboard', 'label' => 'Dashboard', 'active' => isActiveNav('farmer/dashboard', $currentAction)],
        ['href' => 'index.php?action=farmer/products', 'label' => 'Produits', 'active' => isActiveNav('farmer/products', $currentAction)],
        ['href' => 'index.php?action=farmer/orders', 'label' => 'Commandes', 'active' => isActiveNav('farmer/orders', $currentAction)],
    ];
} elseif ($userRole === 'delivery') {
    $mainLinks = [
        ['href' => 'index.php?action=delivery/dashboard', 'label' => 'Dashboard', 'active' => isActiveNav('delivery/dashboard', $currentAction)],
        ['href' => 'index.php?action=delivery/assignments', 'label' => 'Livraisons', 'active' => isActiveNav('delivery/assignments', $currentAction)],
        ['href' => 'index.php?action=delivery/stats', 'label' => 'Statistiques', 'active' => isActiveNav('delivery/stats', $currentAction)],
    ];
} elseif ($userRole === 'admin') {
    $mainLinks = [
        ['href' => 'index.php?action=admin', 'label' => 'Admin', 'active' => isActiveNav('admin', $currentAction)],
    ];
}
?>
<body class="<?= $isFarmer ? 'farmer-theme bg-slate-950 text-slate-100' : ($isDelivery ? 'delivery-theme bg-amber-50 text-slate-900' : 'bg-slate-50 text-slate-900') ?> flex flex-col min-h-screen">
<header class="site-header shadow-sm sticky top-0 z-20 flex-shrink-0 <?= $isFarmer ? 'farmer-header' : ($isDelivery ? 'delivery-header' : '') ?>">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 md:px-6">
        <a href="<?= $logoLink ?>" class="site-logo text-2xl md:text-3xl font-bold text-white">Farmmarket</a>
        <nav class="site-nav flex items-center gap-2 md:gap-3 text-xs sm:text-sm flex-wrap md:flex-nowrap" aria-label="Navigation principale">
            <?php foreach ($mainLinks as $link): ?>
                <a href="<?= $link['href'] ?>" class="nav-pill<?= $link['active'] ? ' nav-pill-active' : '' ?>"><?= htmlspecialchars($link['label']) ?></a>
            <?php endforeach; ?>

            <?php if (isset($_SESSION['user'])) : ?>
                <div class="border-l border-slate-300 pl-2 md:pl-3 flex gap-2 md:gap-3 items-center flex-wrap">
                    <span class="px-2 py-1 rounded <?= $badgeClass ?> text-xs font-medium whitespace-nowrap">
                        <?= htmlspecialchars(substr($_SESSION['user']['name'], 0, 15)) ?>
                    </span>
                    <a href="index.php?action=profile" class="nav-pill hidden sm:inline<?= isActiveNav('profile', $currentAction) ? ' nav-pill-active' : '' ?>">Profil</a>
                    <?php if (in_array($userRole, ['farmer', 'delivery'], true)) : ?>
                        <button id="themeToggleBtn" type="button" class="nav-pill theme-toggle">Basculer de thème</button>
                    <?php endif; ?>
                    <a href="index.php?action=logout&next=login" class="nav-pill nav-pill-logout">Déconnexion</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-6xl px-4 py-6 md:px-6 flex-grow w-full">