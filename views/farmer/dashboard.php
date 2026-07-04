<?php
/**
 * Tableau de bord Éleveur — Version améliorée
 *
 * @var string $farm_name
 * @var array  $products
 * @var int    $orderCount
 * @var float  $totalSales
 * @var int    $deliveredCount
 * @var array  $orders
 * @var array  $deliveries
 * @var array  $salesTrendLabels
 * @var array  $salesTrendData
 */

require __DIR__ . '/../partials/header.php';

$avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : null;
$deliveryRate  = $orderCount > 0 ? round(($deliveredCount / $orderCount) * 100) : null;
$periodTotal   = !empty($salesTrendData) ? array_sum($salesTrendData) : 0;

$hour = (int) date('H');
if ($hour < 12)       $greeting = 'Bonjour';
elseif ($hour < 18)   $greeting = 'Bon après-midi';
else                  $greeting = 'Bonsoir';

$joursFR = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
$moisFR  = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$today   = $joursFR[(int)date('w')] . ' ' . (int)date('j') . ' ' . $moisFR[(int)date('n')] . ' ' . date('Y');

function fcfa($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<!-- Inline styles moved to public/css/style.css -->
<!-- farmer/dashboard.php: removed inline <style> block; styles consolidated into public/css/style.css -->

<div class="page-content font-body text-slate-800">

    <!-- En-tête -->
    <div class="page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-sm shrink-0" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                    <path d="M7 20h10"/><path d="M10 20c0-7 2-9 7-11-1 5-3 7-7 8"/><path d="M10 17c-3-1-5-4-5-9 5 0 7 2 8 5"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-slate-900"><?= $greeting ?>, <?= htmlspecialchars($farm_name) ?></h1>
                <p class="text-slate-500 text-sm mt-1 capitalize"><?= htmlspecialchars($today) ?></p>
            </div>
        </div>
        <a href="index.php?action=farmer/add-product"
           class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl font-medium shadow-sm shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-5 h-5" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Ajouter un produit
        </a>
    </div>

    <!-- Indicateurs KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500">Total de produits</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-display font-bold text-slate-900"><?= count($products) ?></div>
            <p class="text-xs text-slate-400 mt-1">Références actives au catalogue</p>
        </div>

        <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500">Commandes totales</span>
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
                        <path d="M2.5 3h2l2.8 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/>
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-display font-bold text-slate-900"><?= $orderCount ?></div>
            <p class="text-xs text-slate-400 mt-1">Depuis le début de l'activité</p>
        </div>

        <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500">Chiffre d'affaires</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 10v.01M18 14v.01"/>
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-display font-bold text-slate-900">
                <?= number_format($totalSales, 0, '', ' ') ?>
                <span class="text-base font-body font-medium text-slate-400">FCFA</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                <?= $avgOrderValue !== null ? 'Soit ' . fcfa($avgOrderValue) . ' / commande' : 'Aucune commande enregistrée' ?>
            </p>
        </div>

        <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500">Livraisons complétées</span>
                <span class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M3 7h11v8H3z"/><path d="M14 11h4l3 3v1h-7z"/>
                        <circle cx="7" cy="17" r="1.6"/><circle cx="17.5" cy="17" r="1.6"/>
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-display font-bold text-slate-900"><?= $deliveredCount ?></div>
            <?php if ($deliveryRate !== null): ?>
                <div class="mt-2">
                    <div class="h-1.5 w-full stock-bar-track rounded-full overflow-hidden">
                        <div class="h-full bg-violet-500 rounded-full" style="width: <?= min(100, $deliveryRate) ?>%"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1"><?= $deliveryRate ?>% des commandes livrées</p>
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400 mt-1">Aucune commande enregistrée</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gestion rapide + Graphique -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-1">
            <h2 class="text-lg font-display font-bold text-slate-900 mb-4">Gestion rapide</h2>
            <div class="grid grid-cols-1 gap-3">
                <a href="index.php?action=farmer/products" class="quick-action flex items-center gap-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 px-4 py-3 rounded-xl">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                    <span class="font-medium text-sm">Mes produits</span>
                </a>
                <a href="index.php?action=farmer/categories" class="quick-action flex items-center gap-3 bg-slate-50 hover:bg-slate-100 text-slate-800 px-4 py-3 rounded-xl">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0" aria-hidden="true"><path d="M20.59 13.41 11 3.83A2 2 0 0 0 9.59 3.24L4 3a1 1 0 0 0-1 1l.24 5.59a2 2 0 0 0 .58 1.41l9.59 9.59a2 2 0 0 0 2.83 0l4.35-4.35a2 2 0 0 0 0-2.83Z"/><circle cx="7.5" cy="7.5" r="1.2"/></svg>
                    <span class="font-medium text-sm">Mes catégories</span>
                </a>
                <a href="index.php?action=farmer/deliveries" class="quick-action flex items-center gap-3 bg-orange-50 hover:bg-orange-100 text-orange-800 px-4 py-3 rounded-xl">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0" aria-hidden="true"><path d="M3 7h11v8H3z"/><path d="M14 11h4l3 3v1h-7z"/><circle cx="7" cy="17" r="1.6"/><circle cx="17.5" cy="17" r="1.6"/></svg>
                    <span class="font-medium text-sm">Livreur</span>
                </a>
                <a href="index.php?action=farmer/orders" class="quick-action flex items-center gap-3 bg-violet-50 hover:bg-violet-100 text-violet-800 px-4 py-3 rounded-xl">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0" aria-hidden="true"><path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg>
                    <span class="font-medium text-sm">Voir mes ventes</span>
                </a>
                <!-- Bouton Google Maps — ouvre la zone par défaut (Cotonou) -->
                <a href="https://www.google.com/maps/search/?api=1&query=Cotonou,+Bénin"
                   target="_blank" rel="noopener noreferrer"
                   class="quick-action flex items-center gap-3 bg-blue-50 hover:bg-blue-100 text-blue-800 px-4 py-3 rounded-xl">
                    <!-- Icône Google Maps -->
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0 text-blue-600" aria-hidden="true">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    <span class="font-medium text-sm">Ouvrir Google Maps</span>
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-lg font-display font-bold text-slate-900">Tendance des ventes</h2>
                <?php if ($periodTotal > 0): ?>
                    <span class="text-sm font-medium text-emerald-600"><?= fcfa($periodTotal) ?> sur la période</span>
                <?php endif; ?>
            </div>
            <div class="h-80 mt-3">
                <?php if (!empty($salesTrendData)): ?>
                    <canvas id="salesTrendChart" role="img" aria-label="Graphique de l'évolution des ventes"></canvas>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center text-center text-slate-400">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 mb-3" aria-hidden="true"><path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg>
                        <p class="text-sm">Pas encore assez de ventes pour afficher une tendance</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Carte géographique améliorée -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-display font-bold text-slate-900">Suivi géographique des livraisons</h2>
                <p class="text-xs text-slate-400 mt-0.5">Recherchez une adresse ou visualisez vos destinations de livraison</p>
            </div>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-orange-50 text-orange-600 flex items-center gap-1.5 self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> OpenStreetMap
            </span>
        </div>

        <!-- Barre de recherche d'adresse -->
        <div id="map-search-wrapper">
            <svg class="search-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input
                type="text"
                id="map-search-input"
                placeholder="Rechercher une adresse, une ville… (ex: Porto-Novo, Bénin)"
                autocomplete="off"
                aria-label="Rechercher une adresse sur la carte"
                aria-autocomplete="list"
                aria-controls="map-suggestions"
            />
            <button id="map-search-btn" type="button" aria-label="Lancer la recherche">Rechercher</button>
            <div id="map-suggestions" role="listbox" aria-label="Suggestions d'adresses"></div>
        </div>
        <div id="map-status" aria-live="polite" aria-atomic="true"></div>

        <!-- Carte Leaflet -->
        <div id="deliveryMap" class="w-full h-96 rounded-xl border border-slate-100 bg-slate-50 z-10 mt-3"></div>

        <!-- Légende -->
        <div class="flex flex-wrap gap-4 mt-3 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Livraison
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Adresse recherchée
            </span>
        </div>
    </div>

    <!-- Produits + Commandes récentes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Derniers produits -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-display font-bold text-slate-900">Derniers produits</h2>
                <a href="index.php?action=farmer/products" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Voir tout</a>
            </div>

            <?php if (empty($products)): ?>
                <div class="flex flex-col items-center text-center py-10 text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 mb-3" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
                    <p class="text-sm mb-4">Aucun produit pour le moment</p>
                    <a href="index.php?action=farmer/add-product" class="text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-lg">Ajouter mon premier produit</a>
                </div>
            <?php else: ?>
                <div class="space-y-1">
                    <?php foreach (array_slice($products, 0, 5) as $product):
                        $stock = (int) $product['stock'];
                        if ($stock <= 5)       { $badgeClass = 'bg-red-50 text-red-600';     $badgeLabel = 'Stock faible'; }
                        elseif ($stock <= 20)  { $badgeClass = 'bg-amber-50 text-amber-600'; $badgeLabel = 'Stock moyen'; }
                        else                   { $badgeClass = 'bg-emerald-50 text-emerald-600'; $badgeLabel = 'En stock'; }
                        $barWidth = max(4, min(100, ($stock / 50) * 100));
                    ?>
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-slate-100 last:border-0">
                            <div class="min-w-0">
                                <p class="font-medium text-sm text-slate-800 truncate"><?= htmlspecialchars($product['name']) ?></p>
                                <div class="h-1.5 w-32 stock-bar-track rounded-full overflow-hidden mt-1.5" aria-hidden="true">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: <?= $barWidth ?>%"></div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-medium px-2 py-1 rounded-full <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                                <p class="text-xs text-slate-400 mt-1"><?= $stock ?> en stock</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Commandes récentes -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-display font-bold text-slate-900">Commandes récentes</h2>
                <a href="index.php?action=farmer/orders" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Voir tout</a>
            </div>
            <div id="farmerOrdersApp" aria-live="polite"></div>
            <script type="application/json" id="farmerOrdersData">
                <?= json_encode(['orders' => $orders, 'deliveries' => $deliveries]) ?>
            </script>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {

        /* ─── 1. Données PHP → JS ─── */
        let appData = { deliveries: [] };
        try { appData = JSON.parse(document.getElementById('farmerOrdersData').textContent); } catch(e) {}

        /* ─── 2. Initialisation Leaflet ─── */
        const DEFAULT_LAT = 6.37, DEFAULT_LNG = 2.43, DEFAULT_ZOOM = 11;
        const map = L.map('deliveryMap').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        /* ─── Icônes personnalisées ─── */
        function makeIcon(color) {
            return L.divIcon({
                className: '',
                html: `<div style="
                    width:18px;height:18px;border-radius:50%;
                    background:${color};border:3px solid #fff;
                    box-shadow:0 2px 6px rgba(0,0,0,.3);
                "></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9],
                popupAnchor: [0, -12]
            });
        }
        const deliveryIcon = makeIcon('#10b981'); // emerald
        const searchIcon   = makeIcon('#3b82f6'); // blue

        /* ─── 3. Marqueurs de livraison + bouton Google Maps ─── */
        let deliveryLayer = L.featureGroup().addTo(map);

        if (appData.deliveries && appData.deliveries.length > 0) {
            appData.deliveries.forEach(function (d) {
                if (d.latitude && d.longitude) {
                    const lat = parseFloat(d.latitude);
                    const lng = parseFloat(d.longitude);
                    const gmapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
                    const popup = `
                        <div style="font-family:'Inter',sans-serif;color:#1e293b;min-width:160px">
                            <strong style="color:#0f6b4c;font-size:.85rem">Livraison #${d.id || '—'}</strong><br>
                            <span style="font-size:.75rem;color:#64748b">Statut : ${d.status || 'En cours'}</span>
                            ${d.address ? `<br><span style="font-size:.73rem;color:#94a3b8">${d.address}</span>` : ''}
                            <br>
                            <a class="gmaps-btn" href="${gmapsUrl}" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                Voir sur Google Maps
                            </a>
                        </div>`;
                    L.marker([lat, lng], { icon: deliveryIcon }).bindPopup(popup).addTo(deliveryLayer);
                }
            });

            if (deliveryLayer.getLayers().length > 0) {
                map.fitBounds(deliveryLayer.getBounds(), { padding: [40, 40] });
            }
        }

        /* ─── 4. Recherche d'adresse (Nominatim) ─── */
        const searchInput  = document.getElementById('map-search-input');
        const searchBtn    = document.getElementById('map-search-btn');
        const suggestBox   = document.getElementById('map-suggestions');
        const statusEl     = document.getElementById('map-status');

        let searchMarker   = null;
        let suggestTimeout = null;
        let activeIndex    = -1;
        let currentResults = [];

        function setStatus(msg, type) {
            statusEl.className = 'flex items-center gap-2 ' + (type === 'error' ? 'error text-red-600' : type === 'success' ? 'success text-emerald-700' : 'text-slate-500');
            statusEl.innerHTML = type === 'loading'
                ? `<span class="spinner"></span> ${msg}`
                : msg;
        }

        function clearStatus() { statusEl.innerHTML = ''; statusEl.className = ''; }

        function placeSearchMarker(lat, lng, displayName) {
            if (searchMarker) map.removeLayer(searchMarker);
            const gmapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            const popup = `
                <div style="font-family:'Inter',sans-serif;color:#1e293b;min-width:170px">
                    <strong style="color:#1d4ed8;font-size:.82rem">📍 Adresse trouvée</strong><br>
                    <span style="font-size:.72rem;color:#64748b">${displayName}</span><br>
                    <a class="gmaps-btn" href="${gmapsUrl}" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        Ouvrir dans Google Maps
                    </a>
                    <br>
                    <a class="gmaps-btn" style="background:#34a853;margin-top:4px"
                       href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}"
                       target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24"><path d="M21.71 11.29l-9-9a1 1 0 0 0-1.42 0l-9 9a1 1 0 0 0 0 1.42l9 9a1 1 0 0 0 1.42 0l9-9a1 1 0 0 0 0-1.42zM14 14.5V12h-4v3H8v-4a1 1 0 0 1 1-1h5V7.5l3.5 3.5-3.5 3.5z"/></svg>
                        Itinéraire
                    </a>
                </div>`;
            searchMarker = L.marker([lat, lng], { icon: searchIcon })
                .bindPopup(popup)
                .addTo(map)
                .openPopup();
            map.setView([lat, lng], 15, { animate: true, duration: 0.8 });
        }

        function closeSuggestions() {
            suggestBox.style.display = 'none';
            suggestBox.innerHTML = '';
            activeIndex = -1;
            currentResults = [];
        }

        async function fetchSuggestions(query) {
            if (query.length < 3) { closeSuggestions(); return; }
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1&accept-language=fr`;
                const res  = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
                const data = await res.json();
                currentResults = data;

                if (!data.length) { closeSuggestions(); return; }

                suggestBox.innerHTML = data.map((item, i) => {
                    const main = item.display_name.split(',')[0];
                    const rest = item.display_name.split(',').slice(1, 3).join(',').trim();
                    return `<div class="suggestion-item" role="option" data-index="${i}" tabindex="-1">
                        <div>${main}</div>
                        <div class="suggestion-secondary">${rest}</div>
                    </div>`;
                }).join('');

                suggestBox.style.display = 'block';

                suggestBox.querySelectorAll('.suggestion-item').forEach(el => {
                    el.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        const idx = parseInt(this.dataset.index);
                        selectResult(currentResults[idx]);
                    });
                });
            } catch(err) {
                closeSuggestions();
            }
        }

        function selectResult(item) {
            searchInput.value = item.display_name.split(',').slice(0, 2).join(',');
            closeSuggestions();
            placeSearchMarker(parseFloat(item.lat), parseFloat(item.lon), item.display_name);
            setStatus('Adresse trouvée et affichée sur la carte.', 'success');
        }

        async function doSearch() {
            const query = searchInput.value.trim();
            if (!query) return;
            closeSuggestions();
            setStatus('Recherche en cours…', 'loading');
            searchBtn.disabled = true;
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&accept-language=fr`;
                const res  = await fetch(url);
                const data = await res.json();
                if (!data.length) {
                    setStatus('Aucun résultat trouvé. Essayez une adresse plus précise.', 'error');
                } else {
                    selectResult(data[0]);
                }
            } catch(err) {
                setStatus('Erreur réseau. Vérifiez votre connexion.', 'error');
            } finally {
                searchBtn.disabled = false;
            }
        }

        /* Événements recherche */
        searchInput.addEventListener('input', function () {
            clearTimeout(suggestTimeout);
            suggestTimeout = setTimeout(() => fetchSuggestions(this.value.trim()), 300);
        });

        searchInput.addEventListener('keydown', function (e) {
            const items = suggestBox.querySelectorAll('.suggestion-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && currentResults[activeIndex]) {
                    selectResult(currentResults[activeIndex]);
                } else {
                    doSearch();
                }
            } else if (e.key === 'Escape') {
                closeSuggestions();
            }
        });

        searchInput.addEventListener('blur', function () {
            setTimeout(closeSuggestions, 200);
        });

        searchBtn.addEventListener('click', doSearch);

        /* ─── 5. Graphique Chart.js ─── */
        const ctx = document.getElementById('salesTrendChart');
        if (ctx) {
            const ctx2d = ctx.getContext('2d');
            const gradient = ctx2d.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, 'rgba(16,185,129,.30)');
            gradient.addColorStop(1, 'rgba(16,185,129,.02)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($salesTrendLabels) ?>,
                    datasets: [{
                        label: 'Ventes',
                        data: <?= json_encode($salesTrendData) ?>,
                        borderColor: '#0f6b4c',
                        backgroundColor: gradient,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#0f6b4c',
                        pointHoverRadius: 6,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8', font: { family: 'Inter' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Inter' },
                                callback: function (value) {
                                    return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
    <script defer src="public/js/farmerOrdersDashboard.js"></script>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>