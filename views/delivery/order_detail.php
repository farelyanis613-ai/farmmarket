<?php require __DIR__ . '/../partials/header.php'; ?>

<?php if (!empty($order) && normalizeStatus($order['status']) === 'accepted'): ?>
    <link rel="stylesheet" href="public/lib/leaflet/leaflet.css">
    <!-- Styles de la carte dans public/css/style.css -->
<?php endif; ?>

<div class="max-w-4xl mx-auto space-y-6 pb-10 px-4">
    <a href="index.php?action=delivery/dashboard" class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-800 hover:text-amber-950 transition">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 18l-6-6 6-6"/></svg>
        Retour au tableau de bord
    </a>

    <?php if (empty($order)): ?>
        <div class="rounded-2xl bg-white p-10 shadow-sm text-center border border-amber-100">
            <div class="text-3xl mb-2">🔍</div>
            <p class="text-amber-700 font-medium">Commande non trouvée</p>
        </div>
    <?php else: ?>
        <?php
            $statusLabel = formatStatusLabel($order['status']);
            $statusClass = getStatusBadgeClasses($order['status']);
            $latitudeRaw = trim((string)($order['latitude'] ?? ''));
            $longitudeRaw = trim((string)($order['longitude'] ?? ''));
            $addressText = trim((string)($order['address'] ?? $order['delivery_address'] ?? ''));
            $hasCoordinates = $latitudeRaw !== '' && $longitudeRaw !== '' && is_numeric($latitudeRaw) && is_numeric($longitudeRaw);
            $normStatus = normalizeStatus($order['status']);
        ?>

        <!-- ── En-tête commande ── -->
        <div class="rounded-2xl bg-white p-5 md:p-7 shadow-sm border border-amber-100">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 mb-1">Commande</p>
                    <h1 class="text-2xl md:text-3xl font-black text-amber-950">#<?= $order['id'] ?></h1>
                    <span class="inline-flex items-center mt-2 rounded-full px-3 py-1 text-xs font-bold <?= $statusClass ?>">
                        <?= htmlspecialchars($statusLabel) ?>
                    </span>
                </div>
                <div class="text-left md:text-right">
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-600">Total</p>
                    <p class="text-3xl font-black text-amber-950 mt-1">
                        <?= number_format($order['total_price'], 0, '', ' ') ?> <span class="text-lg font-bold">FCFA</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- ── Infos client ── -->
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5">
                <h2 class="flex items-center gap-2 text-base font-bold text-amber-950 mb-4">
                    <span>👤</span> Informations client
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs uppercase text-amber-700 font-semibold">Nom</p>
                        <p class="text-lg font-bold text-amber-950"><?= htmlspecialchars($order['customer_name']) ?></p>
                    </div>

                    <?php if ($normStatus === 'accepted'): ?>
                        <div class="border-t border-amber-200 pt-3">
                            <p class="text-xs uppercase text-amber-700 font-semibold mb-1">Téléphone</p>
                            <p class="text-lg font-bold text-amber-950">
                                <?php if (!empty($order['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($order['phone']) ?>" class="inline-flex items-center gap-1.5 hover:underline">
                                        📞 <?= htmlspecialchars($order['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-amber-700 text-sm font-normal">Non fourni</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="border-t border-amber-200 pt-3">
                            <p class="text-xs uppercase text-amber-700 font-semibold mb-1">Adresse de livraison</p>
                            <p class="text-sm leading-relaxed font-semibold text-amber-950">
                                <?php if (!empty($order['address'])): ?>
                                    📍 <?= nl2br(htmlspecialchars($order['address'])) ?>
                                <?php else: ?>
                                    <span class="text-amber-700 font-normal">Non fournie</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="border-t border-amber-200 pt-3 flex items-center gap-2 text-amber-700">
                            <span>🔒</span>
                            <p class="text-sm italic">
                                Les informations de livraison seront visibles après acceptation
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── Produits ── -->
            <div class="rounded-2xl bg-white p-5 border border-amber-100">
                <h2 class="flex items-center gap-2 text-base font-bold text-amber-950 mb-4">
                    <span>🛒</span> Produits à livrer
                </h2>
                <div class="divide-y divide-amber-50">
                    <?php if (empty($items)): ?>
                        <p class="text-amber-700 text-sm py-2">Aucun article</p>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between items-center py-2.5 first:pt-0 last:pb-0">
                                <div>
                                    <p class="font-medium text-amber-950"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p class="text-xs text-amber-600">Qté&nbsp;: <?= $item['quantity'] ?></p>
                                </div>
                                <p class="font-bold text-amber-950">
                                    <?= number_format($item['quantity'] * $item['unit_price'], 0, '', ' ') ?> FCFA
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── Carte GPS (uniquement si acceptée) ── -->
        <?php if ($normStatus === 'accepted'): ?>
            <div class="rounded-2xl border border-amber-100 bg-white p-5">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="flex items-center gap-2 text-base font-bold text-amber-950">
                        <span>🗺️</span> Itinéraire et géolocalisation
                    </h2>
                    <span class="text-xs bg-amber-100 text-amber-900 px-2.5 py-1 rounded-full font-semibold">OpenStreetMap</span>
                </div>
                <div id="orderMap" class="relative w-full h-80 rounded-xl bg-amber-50 border border-amber-100 overflow-hidden z-10"></div>
                <div class="flex items-center justify-between mt-2 px-0.5">
                    <p id="orderMapNote" class="text-xs text-amber-600"></p>
                </div>
                <div class="grid grid-cols-2 gap-2.5 mt-3">
                    <button type="button" id="openMapsBtn" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-white px-4 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-50 transition">
                        Google Maps
                    </button>
                    <button type="button" id="openWazeBtn" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-white px-4 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-50 transition">
                        Waze
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Actions / statut ── -->
        <?php if ($normStatus === 'pending'): ?>
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 md:p-7">
                <h3 class="text-lg font-bold text-amber-950 mb-4">Vous devez d'abord accepter ou refuser cette commande</h3>

                <form action="index.php?action=delivery/respond" method="post" class="flex flex-col md:flex-row gap-3">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

                    <button type="submit" name="action" value="accept" class="flex-1 rounded-xl bg-amber-900 px-6 py-3.5 font-bold text-white hover:bg-amber-800 active:bg-amber-950 transition-colors shadow-sm">
                        ✓ Accepter cette commande
                    </button>

                    <button type="submit" name="action" value="reject" class="flex-1 rounded-xl bg-red-600 px-6 py-3.5 font-bold text-white hover:bg-red-700 active:bg-red-800 transition-colors shadow-sm">
                        ✕ Refuser
                    </button>
                </form>
            </div>
        <?php elseif ($normStatus === 'accepted'): ?>
            <div class="rounded-2xl bg-green-50 border border-green-200 p-5 md:p-7 text-center">
                <p class="text-green-900 font-bold">✓ Commande acceptée — vous avez accès aux informations de livraison</p>
            </div>
        <?php elseif ($normStatus === 'delivered'): ?>
            <div class="rounded-2xl bg-green-50 border border-green-200 p-5 md:p-7 text-center">
                <p class="text-green-900 font-bold">✓ Commande livrée</p>
            </div>
        <?php elseif (isFailedStatus($order['status'])): ?>
            <div class="rounded-2xl bg-red-50 border border-red-200 p-5 md:p-7 text-center">
                <p class="text-red-900 font-bold">✕ Commande échouée</p>
                <?php if (!empty($order['failed_reason'])): ?>
                    <p class="mt-2 text-sm text-red-800">Raison : <?= htmlspecialchars($order['failed_reason']) ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($normStatus === 'rejected'): ?>
            <div class="rounded-2xl bg-red-50 border border-red-200 p-5 md:p-7 text-center">
                <p class="text-red-900 font-bold">✕ Commande refusée</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (!empty($order) && normalizeStatus($order['status']) === 'accepted'): ?>
    <script src="public/lib/leaflet/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const mapContainer = document.getElementById('orderMap');
            const note = document.getElementById('orderMapNote');
            if (!mapContainer || typeof L === 'undefined') return;

            const hasCoordinates = <?= json_encode($hasCoordinates) ?>;
            const latitude = <?= json_encode($latitudeRaw) ?>;
            const longitude = <?= json_encode($longitudeRaw) ?>;
            const addressText = <?= json_encode($addressText) ?>;
            const orderId = <?= json_encode((string)($order['id'] ?? '')) ?>;
            const clientName = <?= json_encode((string)($order['customer_name'] ?? 'Client')) ?>;

            const showLoading = (text) => {
                mapContainer.innerHTML = `
                    <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-center text-sm px-4 text-amber-700">
                        <svg class="animate-spin h-5 w-5 text-amber-400" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        ${text}
                    </div>`;
            };

            const showMessage = (icon, text) => {
                mapContainer.innerHTML = `
                    <div class="w-full h-full flex flex-col items-center justify-center gap-1.5 text-center text-sm px-4 text-amber-700">
                        <span class="text-xl">${icon}</span>${text}
                    </div>`;
            };

            const geocodeAddress = async (address) => {
                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&accept-language=fr`;
                    const res = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
                    if (!res.ok) return null;
                    const results = await res.json();
                    return results.length ? { lat: parseFloat(results[0].lat), lng: parseFloat(results[0].lon) } : null;
                } catch (e) {
                    return null;
                }
            };

            let lat = null;
            let lng = null;
            let approximate = false;

            if (hasCoordinates) {
                lat = parseFloat(latitude);
                lng = parseFloat(longitude);
            } else if (addressText) {
                showLoading("Localisation de l'adresse…");
                const geo = await geocodeAddress(addressText);
                if (!mapContainer.isConnected) return;
                if (!geo) {
                    showMessage('🗺️', `Adresse introuvable : « ${addressText} »`);
                    return;
                }
                lat = geo.lat;
                lng = geo.lng;
                approximate = true;
            } else {
                showMessage('📭', "Aucune adresse ni coordonnées GPS n'est disponible pour cette commande.");
                return;
            }

            mapContainer.innerHTML = '';
            const map = L.map(mapContainer, { scrollWheelZoom: false }).setView([lat, lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const icon = L.divIcon({
                className: '',
                html: `<div style="width:32px;height:32px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:${approximate ? '#f59e0b' : '#78350f'};border:3px solid #fff;box-shadow:0 3px 8px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;">
                           <span style="transform:rotate(45deg);font-size:14px;">🏠</span>
                         </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -30]
            });

            const popupText = approximate
                ? `<div style="font-family: sans-serif; color: #451a03; min-width: 170px;"><strong style="color: #78350f;">Livraison #${orderId}</strong><br><span>Client : ${clientName}</span><br><span style="color: #b45309;">Position approximative</span></div>`
                : `<div style="font-family: sans-serif; color: #451a03; min-width: 170px;"><strong style="color: #78350f;">Livraison #${orderId}</strong><br><span>Client : ${clientName}</span></div>`;

            L.marker([lat, lng], { icon }).addTo(map)
                .bindPopup(popupText)
                .openPopup();

            if (approximate && note) {
                note.textContent = "Position estimée à partir de l'adresse (pas de GPS enregistré).";
            }

            setTimeout(() => map.invalidateSize(), 150);

            // Boutons de navigation externe
            const openMapsBtn = document.getElementById('openMapsBtn');
            const openWazeBtn = document.getElementById('openWazeBtn');
            const gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(lat + ',' + lng)}`;
            const wazeUrl = `https://waze.com/ul?ll=${encodeURIComponent(lat + ',' + lng)}&navigate=yes`;
            openMapsBtn?.addEventListener('click', () => window.open(gmapsUrl, '_blank', 'noopener,noreferrer'));
            openWazeBtn?.addEventListener('click', () => window.open(wazeUrl, '_blank', 'noopener,noreferrer'));
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>