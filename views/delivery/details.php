<?php require __DIR__ . '/../partials/header.php'; ?>

<?php if (!empty($delivery)): ?>
    <link rel="stylesheet" href="public/lib/leaflet/leaflet.css">
    <!-- Inline styles moved to public/css/style.css -->
    <!-- views/delivery/details.php: small Leaflet overrides consolidated into public/css/style.css -->
<?php endif; ?>

<div class="container mx-auto p-6">
    <a href="index.php?action=delivery/dashboard" class="text-amber-900 hover:underline mb-4 inline-block">← Retour au tableau de bord</a>
    
    <h1 class="text-3xl font-bold mb-4">Détails de livraison #<?= htmlspecialchars($_GET['order_id'] ?? '') ?></h1>

    <?php if (empty($delivery)): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded">
            Livraison non trouvée
        </div>
    <?php else: ?>
        <?php 
            $first = isset($delivery[0]) ? $delivery[0] : null;
            if (!$first) {
                echo '<div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded">Livraison non trouvée</div>';
            } else {
        ?>
        <?php
            $normalizedStatus = normalizeStatus($first['status']);
            $currentStatus = formatStatusLabel($first['status']);
            $statusClass = getStatusBadgeClasses($first['status']);
            $latitudeRaw = trim((string)($first['latitude'] ?? ''));
            $longitudeRaw = trim((string)($first['longitude'] ?? ''));
            $addressText = trim((string)($first['address'] ?? $first['delivery_address'] ?? ''));
            $hasCoordinates = $latitudeRaw !== '' && $longitudeRaw !== '' && is_numeric($latitudeRaw) && is_numeric($longitudeRaw);
        ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 text-amber-950">Informations client</h2>
                <div class="space-y-3 text-sm text-amber-700">
                    <div>
                        <div class="font-semibold">Nom</div>
                        <div><?= htmlspecialchars($first['user_name']) ?></div>
                    </div>
                    <div>
                        <div class="font-semibold">Email</div>
                        <div><?= htmlspecialchars($first['email']) ?></div>
                    </div>
                    <div>
                        <div class="font-semibold">Statut actuel</div>
                        <div class="font-semibold <?= $statusClass ?>"><?= $currentStatus ?></div>
                    </div>
                    <?php if (!empty($first['failed_reason']) && isFailedStatus($first['status'])): ?>
                        <div>
                            <div class="font-semibold">Raison de l’échec</div>
                            <div class="text-red-700"><?= htmlspecialchars($first['failed_reason']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 text-amber-950">Produits</h2>
                <div class="space-y-3 text-sm text-amber-700-600 max-h-60 overflow-y-auto pr-1">
                    <?php foreach ($delivery as $item): ?>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 mb-2 last:mb-0">
                            <div class="font-semibold text-amber-950"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div class="text-sm text-amber-700">Qté: <?= $item['quantity'] ?></div>
                            <div class="text-sm font-medium text-amber-900">Total: <?= number_format($item['unit_price'] * $item['quantity'], 0, '', ' ') ?> FCFA</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-amber-950">Aperçu de la carte de livraison</h2>
                <span class="text-xs bg-amber-100 text-amber-900 px-2 py-1 rounded font-medium">OpenStreetMap</span>
            </div>
            <div id="deliveryDetailsMap" class="h-80 rounded-3xl border border-amber-200 bg-amber-50 z-10"></div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4 text-amber-950">Mettre à jour le statut</h2>
            <?php if (isFailedStatus($first['status'])) : ?>
                <p class="text-sm text-amber-700 mb-4">La livraison est déjà marquée comme échouée. Consultez la raison de l’échec ci-dessous.</p>
                <a href="index.php?action=delivery/failure-reason&order_id=<?= htmlspecialchars($_GET['order_id'] ?? '') ?>" class="inline-flex items-center justify-center rounded-full bg-amber-900 px-6 py-3 text-sm font-semibold text-white hover:bg-amber-800 transition">
                    Pourquoi la livraison a échoué ?
                </a>
            <?php else : ?>
                <!-- Delivery users may only accept via the respond endpoint -->
                <form action="index.php?action=delivery/respond" method="post" class="space-y-4">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($_GET['order_id'] ?? '') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

                    <button type="submit" name="action" value="accept" class="w-full rounded-lg bg-amber-900 text-white px-6 py-3 font-medium hover:bg-amber-800">Accepter</button>
                </form>
            <?php endif; ?>
        </div>
        <?php } ?>
    <?php endif; ?>
</div>

<?php if (!empty($delivery)): ?>
    <script src="public/lib/leaflet/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const mapContainer = document.getElementById('deliveryDetailsMap');
            if (!mapContainer || typeof L === 'undefined') return;

            const hasCoordinates = <?= json_encode($hasCoordinates) ?>;
            const latitude = <?= json_encode($latitudeRaw) ?>;
            const longitude = <?= json_encode($longitudeRaw) ?>;
            const addressText = <?= json_encode($addressText) ?>;
            const clientName = <?= json_encode((string)($first['user_name'] ?? 'Client')) ?>;
            const orderId = <?= json_encode((string)($_GET['order_id'] ?? '')) ?>;

            const showMessage = (message) => {
                mapContainer.innerHTML = `<div class="w-full h-full flex items-center justify-center text-center text-sm px-4 text-amber-700">${message}</div>`;
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
                showMessage('Aucune coordonnée GPS enregistrée. Recherche de l’adresse…');
                const geo = await geocodeAddress(addressText);
                if (!geo) {
                    showMessage('Adresse introuvable sur la carte. Vérifiez l’adresse de livraison.');
                    return;
                }
                lat = geo.lat;
                lng = geo.lng;
                approximate = true;
            } else {
                showMessage('Aucune adresse ni coordonnées GPS n’est disponible pour cette livraison.');
                return;
            }

            mapContainer.innerHTML = '';
            const map = L.map(mapContainer).setView([lat, lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const popupText = approximate
                ? `<div style="font-family: sans-serif; color: #451a03; min-width: 160px;"><strong style="color: #78350f;">Livraison #${orderId}</strong><br><span style="font-size: 13px;">Destinataire : ${clientName}</span><br><span style="color: #b45309;">Position approximative (basée sur l’adresse)</span></div>`
                : `<div style="font-family: sans-serif; color: #451a03; min-width: 160px;"><strong style="color: #78350f;">Livraison #${orderId}</strong><br><span style="font-size: 13px;">Destinataire : ${clientName}</span></div>`;

            L.marker([lat, lng]).addTo(map)
                .bindPopup(popupText)
                .openPopup();

            setTimeout(() => map.invalidateSize(), 150);
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>