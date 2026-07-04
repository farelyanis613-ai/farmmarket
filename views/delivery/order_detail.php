<?php require __DIR__ . '/../partials/header.php'; ?>

<?php if (!empty($order) && normalizeStatus($order['status']) === 'accepted'): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Inline Leaflet overrides moved to public/css/style.css -->
<?php endif; ?>

<div class="space-y-6 pb-10">
    <a href="index.php?action=delivery/dashboard" class="text-amber-900 hover:text-amber-950">← Retour au tableau de bord</a>

    <?php if (empty($order)): ?>
        <div class="rounded-lg bg-white p-4 md:p-6 shadow-sm text-center">
            <p class="text-amber-700">Commande non trouvée</p>
        </div>
    <?php else: ?>
        <div class="rounded-lg bg-white p-4 md:p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">Commande #<?= $order['id'] ?></h1>
                    <?php
                        $statusLabel = formatStatusLabel($order['status']);
                        $statusClass = getStatusBadgeClasses($order['status']);
                    ?>
                    <p class="text-amber-700 text-sm">Statut: <span class="font-bold <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></p>
                </div>
                <div class="text-right">
                    <p class="text-amber-700">Total</p>
                    <p class="text-3xl font-bold text-amber-950">
                        <?= number_format($order['total_price'], 0, '', ' ') ?> FCFA
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                    <h2 class="text-lg font-bold text-amber-950 mb-4">Informations client</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs uppercase text-amber-700 font-semibold">Nom</p>
                            <p class="text-lg font-bold text-amber-950"><?= htmlspecialchars($order['customer_name']) ?></p>
                        </div>
                        
                        <?php if (normalizeStatus($order['status']) === 'accepted'): ?>
                            <div class="border-t border-amber-200 pt-3">
                                <p class="text-xs uppercase text-amber-700 font-semibold mb-1">Téléphone</p>
                                <p class="text-lg font-bold text-amber-950">
                                    <?php if (!empty($order['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($order['phone']) ?>" class="hover:underline">
                                            <?= htmlspecialchars($order['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-amber-700 text-sm">Non fourni</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="border-t border-amber-200 pt-3">
                                <p class="text-xs uppercase text-amber-700 font-semibold mb-1">Adresse de livraison</p>
                                <p class="text-sm leading-relaxed font-semibold text-amber-950">
                                    <?php if (!empty($order['address'])): ?>
                                        <?= nl2br(htmlspecialchars($order['address'])) ?>
                                    <?php else: ?>
                                        <span class="text-amber-700">Non fournie</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="border-t border-amber-200 pt-3 text-center">
                                <p class="text-sm text-amber-700 italic">
                                    Les informations de livraison seront visibles après acceptation
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 border border-amber-200">
                    <h2 class="text-lg font-bold mb-4">Produits à livrer</h2>
                    <div class="space-y-2">
                        <?php if (empty($items)): ?>
                            <p class="text-amber-700 text-sm">Aucun article</p>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <div class="flex justify-between py-2 border-b last:border-b-0">
                                    <div>
                                        <p class="font-medium"><?= htmlspecialchars($item['product_name']) ?></p>
                                        <p class="text-xs text-amber-700">Qté: <?= $item['quantity'] ?></p>
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

            <?php if (normalizeStatus($order['status']) === 'accepted'): ?>
                <div class="rounded-lg border border-amber-200 p-4 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-bold text-amber-950">Itinéraire et géolocalisation</h2>
                        <span class="text-xs bg-amber-100 text-amber-900 px-2 py-0.5 rounded font-medium">OpenStreetMap</span>
                    </div>
                    <div id="orderMap" class="w-full h-80 rounded-lg bg-amber-50 border border-amber-100 z-10"></div>
                </div>
            <?php endif; ?>

            <?php if (normalizeStatus($order['status']) === 'pending'): ?>
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 md:p-6">
                    <h3 class="text-lg font-bold text-amber-950 mb-4">Vous devez d'abord accepter ou refuser cette commande</h3>
                    
                    <form action="index.php?action=delivery/respond" method="post" class="flex flex-col md:flex-row gap-3">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        
                        <button type="submit" name="action" value="accept" class="flex-1 rounded-lg bg-amber-900 px-6 py-3 font-bold text-white hover:bg-amber-800 transition-colors">
                            ✓ Accepter cette commande
                        </button>
                        
                        <button type="submit" name="action" value="reject" class="flex-1 rounded-lg bg-red-600 px-6 py-3 font-bold text-white hover:bg-red-700 transition-colors">
                            ✕ Refuser
                        </button>
                    </form>
                </div>
            <?php elseif (normalizeStatus($order['status']) === 'accepted'): ?>
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 md:p-6 text-center">
                    <p class="text-amber-950 font-bold">✓ Commande acceptée - Vous avez accès aux informations de livraison</p>
                </div>
            <?php elseif (normalizeStatus($order['status']) === 'delivered'): ?>
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 md:p-6 text-center">
                    <p class="text-amber-950 font-bold">✓ Commande livrée</p>
                </div>
            <?php elseif (isFailedStatus($order['status'])): ?>
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 md:p-6 text-center">
                    <p class="text-amber-950 font-bold">✕ Commande échouée</p>
                    <?php if (!empty($order['failed_reason'])): ?>
                        <p class="mt-2 text-sm text-red-800">Raison : <?= htmlspecialchars($order['failed_reason']) ?></p>
                    <?php endif; ?>
                </div>
            <?php elseif (normalizeStatus($order['status']) === 'rejected'): ?>
                <div class="rounded-lg bg-red-50 border border-red-200 p-4 md:p-6 text-center">
                    <p class="text-red-900 font-bold">✕ Commande refusée</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($order) && normalizeStatus($order['status']) === 'accepted'): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Récupération sécurisée des coordonnées depuis PHP (avec fallback)
            const lat = parseFloat("<?= $order['latitude'] ?? 6.37 ?>");
            const lng = parseFloat("<?= $order['longitude'] ?? 2.43 ?>");
            const orderId = "<?= $order['id'] ?>";
            const clientName = <?= json_encode($order['customer_name']) ?>;

            // Initialisation de la carte centrée sur le point de livraison
            const map = L.map('orderMap').setView([lat, lng], 14);

            // Ajout du fond de carte OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Ajout du marqueur personnalisé sur l'adresse client
            L.marker([lat, lng]).addTo(map)
                .bindPopup(`
                    <div style="font-family: sans-serif; color: #451a03;">
                        <strong style="color: #78350f;">Livraison #${orderId}</strong><br>
                        <span>Client : ${clientName}</span>
                    </div>
                `)
                .openPopup();
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>