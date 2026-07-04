<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Marché fermier en ligne, produits frais locaux et livraison rapide.">
    <?php
    $currentAction = $_GET['action'] ?? 'home';
    $pageTitle = $pageTitle ?? 'Farmmarket';
    $googleMapsKey = getenv('VITE_GOOGLE_MAPS_API_KEY') ?: ($_ENV['VITE_GOOGLE_MAPS_API_KEY'] ?? '');
    $loadLeaflet = $currentAction === 'checkout';
    ?>
    <meta name="google-maps-api-key" content="<?= htmlspecialchars($googleMapsKey) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="public/css/tailwind.min.css">
    <link rel="stylesheet" href="public/css/fonts.css">
    <link rel="stylesheet" href="public/css/style.css">
    <?php if ($loadLeaflet) : ?>
        <link rel="stylesheet" href="public/lib/leaflet/leaflet.css">
        <script defer src="public/lib/leaflet/leaflet.js"></script>
    <?php endif; ?>

    <?php
    // Load Google Maps scripts only when a real API key is configured and
    // never on the checkout page (checkout uses Leaflet/OpenStreetMap).
    $isCheckoutPage = in_array($currentAction, ['checkout', 'checkout/mobile'], true);
    if (!empty($googleMapsKey) && $googleMapsKey !== 'your-google-maps-api-key' && !$isCheckoutPage) :
    ?>
        <script defer src="public/js/googleMapsLoader.js"></script>
        <script defer src="public/js/googleMapsIntegration.js"></script>
    <?php endif; ?>

    <script defer src="public/js/app.js"></script>
</head>
<?php
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
    <div class="mx-auto flex w-full max-w-6xl flex-col items-start gap-3 px-4 py-4 md:flex-row md:items-center md:justify-between md:gap-0 md:px-6">
        <a href="<?= $logoLink ?>" class="site-logo text-2xl md:text-3xl font-bold text-white">Farmmarket</a>
        <nav class="site-nav flex w-full flex-wrap items-center gap-2 md:flex-nowrap md:w-auto text-xs sm:text-sm justify-start md:justify-end" aria-label="Navigation principale">
            <?php foreach ($mainLinks as $link): ?>
                <?php $linkClasses = 'inline-flex items-center justify-center px-3 py-2 rounded-full border text-sm font-semibold transition-colors duration-150 whitespace-nowrap'; ?>
                <?php $linkClasses .= $link['active'] ? ' bg-slate-950 text-white border-slate-950' : ' bg-white/90 text-slate-900 border-slate-200 hover:bg-slate-100'; ?>
                <a href="<?= $link['href'] ?>" class="<?= $linkClasses ?>"><?= htmlspecialchars($link['label']) ?></a>
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