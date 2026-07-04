<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Ce lien de polices apparaît maintenant dans 4 vues : le bon réflexe est de le déplacer
     une bonne fois dans le <head> de partials/header.php pour éviter la répétition. -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Inline styles moved to public/css/style.css -->
<!-- views/farmer/orders.php: original <style> block consolidated into public/css/style.css -->

<div class="container mx-auto p-6 pb-10 font-body text-slate-800">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-slate-900">Mes commandes</h1>
        <p class="text-slate-500 text-sm mt-1">
            <?= count($orders) ?> commande<?= count($orders) > 1 ? 's' : '' ?> &middot; Gérez vos commandes et assignez des livreurs
        </p>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-4" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M2.5 3h2l2.8 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/></svg>
            </div>
            <p class="text-slate-600 font-medium">Aucune commande pour le moment</p>
            <p class="text-sm text-slate-400 mt-1">Vos commandes apparaîtront ici dès qu'un client passera une commande.</p>
        </div>
    <?php else: ?>

        <?php
            // Options de filtre construites à partir des statuts réellement présents,
            // pour rester valable quelle que soit la liste de statuts gérée par l'application.
            $statusFilterOptions = [];
            foreach ($orders as $o) {
                $normalized = normalizeStatus($o['status']);
                if (!isset($statusFilterOptions[$normalized])) {
                    $statusFilterOptions[$normalized] = formatStatusLabel($o['status']);
                }
            }
        ?>

        <!-- Recherche et filtre -->
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="search" id="order-search" placeholder="Rechercher par client ou n° de commande..."
                       class="w-full rounded-lg border border-slate-200 pl-9 pr-4 py-2.5 text-sm focus:border-emerald-500" aria-label="Rechercher une commande">
            </div>
            <select id="status-filter" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500" aria-label="Filtrer par statut">
                <option value="">Tous les statuts</option>
                <?php foreach ($statusFilterOptions as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-4" id="orders-list">
            <?php foreach ($orders as $order):
                $statusLabel = formatStatusLabel($order['status']);
                $statusClass = getStatusBadgeClasses($order['status']);
                $orderId = (int) $order['id'];
                $hasDriver = !empty($order['delivery_person_name']);
                $searchKey = strtolower(($order['customer_name'] ?? '') . ' ' . $orderId);

                $createdTimestamp  = !empty($order['created_at']) ? strtotime($order['created_at']) : false;
                $deliveryTimestamp = !empty($order['delivery_time']) ? strtotime($order['delivery_time']) : false;
            ?>
                <div class="order-card bg-white rounded-2xl shadow-sm border border-slate-100 border-l-4 <?= $hasDriver ? 'border-l-emerald-400' : 'border-l-amber-400' ?> overflow-hidden hover:shadow-md transition"
                     data-search="<?= htmlspecialchars($searchKey) ?>" data-status="<?= htmlspecialchars(normalizeStatus($order['status'])) ?>">

                    <!-- En-tête : N° commande, client, statut, total -->
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 p-6 border-b border-slate-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Commande</p>
                                <p class="text-2xl font-display font-bold text-slate-900">#<?= $orderId ?></p>
                            </div>

                            <div class="lg:col-span-2">
                                <p class="text-xs uppercase font-semibold text-slate-500">Client</p>
                                <p class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($order['customer_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= htmlspecialchars($order['customer_email'] ?? '') ?></p>
                            </div>

                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500 mb-2">Statut</p>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?= $statusClass ?>">
                                    <?= htmlspecialchars($statusLabel) ?>
                                </span>
                            </div>

                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Total</p>
                                <p class="text-2xl font-display font-bold text-emerald-600"><?= number_format((float) $order['total_price'], 0, '', ' ') ?> FCFA</p>
                            </div>
                        </div>
                    </div>

                    <!-- Détails : produits, livraison, livreur -->
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-sm uppercase font-semibold text-slate-700 mb-3">Produits commandés</h3>
                                <?php if (!empty($order['items'])): ?>
                                    <ul class="space-y-2">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li class="flex justify-between text-sm text-slate-700 bg-slate-50 p-2 rounded-lg">
                                                <span><?= htmlspecialchars($item['product_name']) ?></span>
                                                <span class="font-semibold text-slate-900">x<?= (int) $item['quantity'] ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-slate-400 italic text-sm">Aucun produit</p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h3 class="text-sm uppercase font-semibold text-slate-700 mb-3">Livraison</h3>
                                <div class="space-y-2 text-sm text-slate-700 bg-slate-50 p-3 rounded-lg">
                                    <?php if (!empty($order['delivery_time'])): ?>
                                        <div>
                                            <span class="font-semibold">Prévu :</span>
                                            <span><?= $deliveryTimestamp !== false ? date('d/m/Y H:i', $deliveryTimestamp) : 'Date invalide' ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="break-words">
                                        <span class="font-semibold">Adresse :</span>
                                        <div class="mt-1"><?= nl2br(htmlspecialchars($order['customer_address'] ?? 'N/A')) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm uppercase font-semibold text-slate-700 mb-3">Livreur</h3>
                                <?php if ($hasDriver): ?>
                                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 text-sm">
                                        <p class="font-semibold text-emerald-900"><?= htmlspecialchars($order['delivery_person_name']) ?></p>
                                        <p class="inline-flex items-center gap-1 text-xs text-emerald-700 mt-1">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                            Livreur assigné
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 text-sm">
                                        <p class="font-semibold text-amber-900">Non assigné</p>
                                        <p class="text-xs text-amber-700 mt-1">En attente d'assignation</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 text-xs text-slate-500 border-t border-slate-100 pt-4">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            <span>Créée le <?= $createdTimestamp !== false ? date('d/m/Y à H:i', $createdTimestamp) : 'date inconnue' ?></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex flex-wrap gap-2">
                        <?php if (!$hasDriver): ?>
                            <a href="index.php?action=farmer/assign-delivery&order_id=<?= $orderId ?>"
                               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4" aria-hidden="true"><path d="M3 7h11v8H3z"/><path d="M14 11h4l3 3v1h-7z"/><circle cx="7" cy="17" r="1.6"/><circle cx="17.5" cy="17" r="1.6"/></svg>
                                Assigner un livreur
                            </a>
                        <?php endif; ?>

                        <a href="index.php?action=farmer/orders&view=<?= $orderId ?>"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Voir détails
                        </a>

                        <?php if (isset($order['delivery_type']) && $order['delivery_type'] === 'shop' && normalizeStatus($order['status']) !== 'delivered'): ?>
                            <form action="index.php?action=farmer/mark-pickup" method="post" class="inline">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                    Commande récupérée
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p id="no-results-message" class="hidden text-center text-sm text-slate-400 py-10">Aucune commande ne correspond à votre recherche.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('order-search');
        var statusFilter = document.getElementById('status-filter');
        var cards = document.querySelectorAll('.order-card');
        var noResultsMessage = document.getElementById('no-results-message');

        function applyFilters() {
            var term = (searchInput.value || '').trim().toLowerCase();
            var status = statusFilter.value;
            var visibleCount = 0;

            cards.forEach(function (card) {
                var matchesSearch = card.dataset.search.includes(term);
                var matchesStatus = !status || card.dataset.status === status;
                var visible = matchesSearch && matchesStatus;
                card.classList.toggle('hidden', !visible);
                if (visible) visibleCount++;
            });

            if (noResultsMessage) {
                noResultsMessage.classList.toggle('hidden', visibleCount !== 0);
            }
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    });
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>