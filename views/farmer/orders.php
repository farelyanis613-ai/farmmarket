<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
/*
 * NOTE : les fonts Google sont déjà dans header.php via style.css (.font-display / .font-body).
 * Pas besoin de <link> supplémentaire ici.
 *
 * Ce fichier utilise des classes CSS préfixées "fo-" (farmer orders) définies
 * dans public/css/style.css (voir orders-farmer.css à importer).
 * Ces classes ne sont PAS écrasées par les overrides farmer-theme de style.css.
 */

$stats = ['total' => count($orders), 'pending' => 0, 'assigned' => 0, 'delivered' => 0];
foreach ($orders as $o) {
    $s = normalizeStatus($o['status']);
    if      ($s === 'pending')                                 { $stats['pending']++;   }
    elseif  ($s === 'delivered')                               { $stats['delivered']++; }
    elseif  (in_array($s, ['accepted', 'in_progress'], true)) { $stats['assigned']++;  }
}

$filterOptions = [];
foreach ($orders as $o) {
    $n = normalizeStatus($o['status']);
    if (!isset($filterOptions[$n])) $filterOptions[$n] = formatStatusLabel($o['status']);
}
?>

<div class="fo-wrap">

    <!-- En-tête -->
    <div class="fo-page-header">
        <div>
            <h1 class="fo-page-title">Mes commandes</h1>
            <p class="fo-page-sub">
                <?= $stats['total'] ?> commande<?= $stats['total'] > 1 ? 's' : '' ?>
                &middot; Gérez vos commandes et assignez des livreurs
            </p>
        </div>
        <div class="fo-badge-dashboard" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
            Tableau de bord commandes
        </div>
    </div>

    <!-- Statistiques -->
    <div class="fo-stats">
        <div class="fo-stat fo-stat--total">
            <p class="fo-stat-label">Total</p>
            <p class="fo-stat-value"><?= $stats['total'] ?></p>
        </div>
        <div class="fo-stat fo-stat--pending">
            <p class="fo-stat-label">En attente</p>
            <p class="fo-stat-value"><?= $stats['pending'] ?></p>
        </div>
        <div class="fo-stat fo-stat--assigned">
            <p class="fo-stat-label">Assignées</p>
            <p class="fo-stat-value"><?= $stats['assigned'] ?></p>
        </div>
        <div class="fo-stat fo-stat--delivered">
            <p class="fo-stat-label">Livrées</p>
            <p class="fo-stat-value"><?= $stats['delivered'] ?></p>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="fo-flash fo-flash--success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="fo-flash fo-flash--error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- État vide -->
    <?php if (empty($orders)): ?>
        <div class="fo-empty">
            <div class="fo-empty-icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
                    <path d="M2.5 3h2l2.8 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/>
                </svg>
            </div>
            <p class="fo-empty-title">Aucune commande pour le moment</p>
            <p class="fo-empty-sub">Vos commandes apparaîtront ici dès qu'un client passera une commande.</p>
        </div>

    <?php else: ?>

        <!-- Filtres -->
        <div class="fo-filters">
            <div class="fo-search-wrap">
                <svg class="fo-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="search" id="fo-search" class="fo-search-input"
                       placeholder="Rechercher par client ou n° de commande…"
                       aria-label="Rechercher une commande">
            </div>
            <select id="fo-status-filter" class="fo-status-select" aria-label="Filtrer par statut">
                <option value="">Tous les statuts</option>
                <?php foreach ($filterOptions as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Liste commandes -->
        <div id="fo-list">
        <?php foreach ($orders as $order):
            /* ✅ $statusValue déclaré EN PREMIER, avant toute utilisation */
            $orderId           = (int) $order['id'];
            $statusValue       = normalizeStatus($order['status']);
            $statusLabel       = formatStatusLabel($order['status']);
            $hasDriver         = !empty($order['delivery_person_name']);
            $canUpdateStatus   = !in_array($statusValue, ['delivered', 'failed', 'rejected'], true);
            $searchKey         = strtolower(($order['customer_name'] ?? '') . ' ' . $orderId);
            $createdTimestamp  = !empty($order['created_at'])    ? strtotime($order['created_at'])    : false;
            $deliveryTimestamp = !empty($order['delivery_time']) ? strtotime($order['delivery_time']) : false;
            $isPickup          = isset($order['delivery_type']) && $order['delivery_type'] === 'shop';
        ?>
            <div class="fo-card"
                 data-search="<?= htmlspecialchars($searchKey) ?>"
                 data-status="<?= htmlspecialchars($statusValue) ?>"
                 style="margin-bottom:1rem">

                <!-- En-tête carte -->
                <div class="fo-card-head fo-card-head--<?= htmlspecialchars($statusValue) ?>">
                    <div>
                        <p class="fo-order-id-label">Commande</p>
                        <p class="fo-order-id">#<?= $orderId ?></p>
                    </div>
                    <div>
                        <p class="fo-order-id-label">Client</p>
                        <p class="fo-customer-name"><?= htmlspecialchars($order['customer_name']) ?></p>
                        <?php if (!empty($order['customer_email'])): ?>
                            <p class="fo-customer-email"><?= htmlspecialchars($order['customer_email']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="fo-order-id-label" style="margin-bottom:.4rem">Statut</p>
                        <span class="fo-status-badge fo-status--<?= htmlspecialchars($statusValue) ?>">
                            <span class="fo-dot" aria-hidden="true"></span>
                            <?= htmlspecialchars($statusLabel) ?>
                        </span>
                    </div>
                    <div>
                        <p class="fo-total-label">Total</p>
                        <p class="fo-total"><?= number_format((float) $order['total_price'], 0, '', ' ') ?> FCFA</p>
                    </div>
                </div>

                <!-- Corps carte -->
                <div class="fo-card-body">

                    <!-- Produits commandés -->
                    <div>
                        <p class="fo-section-label">Produits commandés</p>
                        <?php if (!empty($order['items'])): ?>
                            <ul class="fo-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="fo-item">
                                        <span><?= htmlspecialchars($item['product_name']) ?></span>
                                        <span class="fo-item-qty">x<?= (int) $item['quantity'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="font-size:.825rem;color:#94a3b8;font-style:italic;margin:0">Aucun produit</p>
                        <?php endif; ?>
                    </div>

                    <!-- Livraison -->
                    <div>
                        <p class="fo-section-label">Livraison</p>
                        <div class="fo-delivery-info">
                            <?php if (!empty($order['delivery_time'])): ?>
                                <div>
                                    <strong>Prévu :</strong>
                                    <?= $deliveryTimestamp !== false ? date('d/m/Y H:i', $deliveryTimestamp) : 'Date invalide' ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <strong>Adresse :</strong><br>
                                <?= nl2br(htmlspecialchars($order['customer_address'] ?? 'N/A')) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Livreur -->
                    <div>
                        <p class="fo-section-label">Livreur</p>
                        <?php if ($hasDriver): ?>
                            <div class="fo-driver fo-driver--assigned">
                                <p class="fo-driver-name"><?= htmlspecialchars($order['delivery_person_name']) ?></p>
                                <p class="fo-driver-badge">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                    Livreur assigné
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="fo-driver fo-driver--none">
                                <p class="fo-driver-name">
                                    <span style="display:inline-block;width:.55rem;height:.55rem;border-radius:50%;background:#f59e0b;flex-shrink:0" aria-hidden="true"></span>
                                    Non assigné
                                </p>
                                <p class="fo-driver-badge">En attente d'assignation</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action rapide -->
                <div class="fo-quick-action">
                    <div>
                        <p class="fo-quick-label">Action rapide</p>
                        <p class="fo-quick-sub">Mettez à jour la progression visible par le client.</p>
                    </div>
                    <?php if ($canUpdateStatus): ?>
                        <form action="index.php?action=farmer/update-order-status-api" method="post" class="fo-quick-form">
                            <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars(getCsrfToken()) ?>">
                            <input type="hidden" name="order_id"    value="<?= $orderId ?>">
                            <input type="hidden" name="redirect_to" value="orders">
                            <select name="status" class="fo-quick-select">
                                <option value="accepted"    <?= $statusValue === 'accepted'    ? 'selected' : '' ?>>Accepter la commande</option>
                                <option value="in_progress" <?= $statusValue === 'in_progress' ? 'selected' : '' ?>>Marquer en préparation</option>
                            </select>
                            <button type="submit" class="fo-btn-apply">Appliquer</button>
                        </form>
                    <?php else: ?>
                        <span class="fo-finalized">Suivi finalisé</span>
                    <?php endif; ?>
                </div>

                <!-- Date création -->
                <div class="fo-date">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    Créée le <?= $createdTimestamp !== false ? date('d/m/Y à H:i', $createdTimestamp) : 'date inconnue' ?>
                </div>

                <!-- Actions (pied de carte) -->
                <div class="fo-card-footer">
                    <?php if (!$hasDriver): ?>
                        <a href="index.php?action=farmer/assign-delivery&order_id=<?= $orderId ?>" class="fo-btn fo-btn--blue">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 7h11v8H3z"/><path d="M14 11h4l3 3v1h-7z"/>
                                <circle cx="7" cy="17" r="1.6"/><circle cx="17.5" cy="17" r="1.6"/>
                            </svg>
                            Assigner un livreur
                        </a>
                    <?php endif; ?>

                    <a href="index.php?action=farmer/order/view&id=<?= $orderId ?>" class="fo-btn fo-btn--ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        Voir détails
                    </a>

                    <?php if ($isPickup && $canUpdateStatus): ?>
                        <!-- ✅ CSRF token présent + condition cohérente via $canUpdateStatus -->
                        <form action="index.php?action=farmer/mark-pickup" method="post" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                            <input type="hidden" name="order_id"   value="<?= $orderId ?>">
                            <button type="submit" class="fo-btn fo-btn--green">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                Commande récupérée
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
        </div>

        <p id="fo-no-results" class="fo-no-results" style="display:none">
            Aucune commande ne correspond à votre recherche.
        </p>

    <?php endif; ?>
</div>

<script>
(function () {
    var search   = document.getElementById('fo-search');
    var filter   = document.getElementById('fo-status-filter');
    var cards    = document.querySelectorAll('#fo-list > .fo-card');
    var noResult = document.getElementById('fo-no-results');

    function applyFilters() {
        var term   = search ? search.value.trim().toLowerCase() : '';
        var status = filter ? filter.value : '';
        var visible = 0;
        cards.forEach(function (card) {
            var show = card.dataset.search.includes(term) && (!status || card.dataset.status === status);
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (noResult) noResult.style.display = visible === 0 ? '' : 'none';
    }

    if (search) search.addEventListener('input',  applyFilters);
    if (filter) filter.addEventListener('change', applyFilters);
})();
</script>

<script>
// Intercepte les formulaires rapides de mise à jour de statut et affiche le message JSON
(function () {
    var statusLabels = {
        'pending': 'En attente',
        'in_progress': 'En cours',
        'accepted': 'Acceptée',
        'delivered': 'Livré',
        'failed': 'Échouée',
        'rejected': 'Rejetée'
    };

    document.querySelectorAll('.fo-quick-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            fetch(form.action, { method: 'POST', body: new FormData(form) })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    var msg = (json && json.message) ? json.message : (json.success ? 'Opération réussie.' : 'Erreur');
                    // afficher message inline
                    var existing = form.querySelector('.fo-inline-msg');
                    if (!existing) {
                        existing = document.createElement('div');
                        existing.className = 'fo-inline-msg';
                        existing.style.marginTop = '8px';
                        existing.style.fontSize = '0.95rem';
                        form.appendChild(existing);
                    }
                    existing.textContent = msg;
                    existing.style.color = json.success ? '#065f46' : '#991b1b';

                    if (json.success && json.status) {
                        // Met à jour le badge de statut de la carte
                        var card = form.closest('.fo-card');
                        if (card) {
                            var badge = card.querySelector('.fo-status-badge');
                            if (badge) {
                                var label = statusLabels[json.status] || json.status;
                                badge.textContent = label;
                            }
                        }
                    }
                })
                .catch(function (err) {
                    console.error(err);
                })
                .finally(function () { if (btn) btn.disabled = false; });
        });
    });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>