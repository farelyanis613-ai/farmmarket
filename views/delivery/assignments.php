<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Inline styles moved to public/css/style.css -->
<!-- views/delivery/assignments.php: original <style> block consolidated into public/css/style.css -->

<div class="page-wrap">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 mb-1">Livreur</p>
            <h1 class="text-3xl font-black text-amber-950">Mes livraisons</h1>
            <p class="text-sm text-amber-600 mt-1">Toutes vos commandes assignées, passées et en cours.</p>
        </div>
        <a href="index.php?action=delivery/dashboard"
           class="inline-flex items-center gap-2 self-start sm:self-auto px-4 py-2 rounded-xl bg-amber-950 text-white text-sm font-semibold hover:bg-amber-800 transition">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12l9-9 9 9M4 10v10a1 1 0 0 0 1 1h5v-6h4v6h5a1 1 0 0 0 1-1V10"/></svg>
            Tableau de bord
        </a>
    </div>

    <?php if (empty($assignments)): ?>
        <!-- État vide -->
        <div class="table-wrap">
            <div class="empty-state">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.3">
                    <path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0-1.5 6H5.5L4 13m16 0H4"/>
                </svg>
                <p>Aucune livraison assignée pour le moment.</p>
                <p style="font-size:12px;color:#D97706;">Les commandes qui vous sont attribuées apparaîtront ici.</p>
            </div>
        </div>

    <?php else: ?>

        <!-- Toolbar : recherche + filtres -->
        <div class="toolbar" id="toolbar">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="searchInput" placeholder="Rechercher par client ou #commande…">
            </div>
            <button class="filter-btn active" data-filter="all">Toutes <span class="count-badge" id="cnt-all"><?= count($assignments) ?></span></button>
            <button class="filter-btn" data-filter="pending">En attente</button>
            <button class="filter-btn" data-filter="delivered">Livrées</button>
            <button class="filter-btn" data-filter="failed">Échouées</button>
        </div>

        <!-- Tableau -->
        <div class="table-wrap">
            <table class="del-table" id="deliveryTable">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th class="hide-mobile">Total</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $a):
                        $initials  = strtoupper(substr($a['user_name'] ?? 'C', 0, 1));
                        $badgeClass = getStatusBadgeClasses($a['status']);
                        $label      = formatStatusLabel($a['status']);
                        // Map status to filter key
                        $filterKey = match(true) {
                            str_contains(strtolower($a['status']), 'livr')   => 'delivered',
                            str_contains(strtolower($a['status']), 'échou')  => 'failed',
                            str_contains(strtolower($a['status']), 'echec')  => 'failed',
                            default                                           => 'pending',
                        };
                    ?>
                    <tr data-filter="<?= $filterKey ?>"
                        data-search="<?= strtolower(htmlspecialchars($a['user_name'] . ' ' . $a['id'])) ?>">

                        <td>
                            <span class="order-id">#<?= $a['id'] ?></span>
                        </td>
                        <td>
                            <span class="avatar"><?= $initials ?></span><?= htmlspecialchars($a['user_name']) ?>
                        </td>
                        <td class="hide-mobile">
                            <span class="price"><?= number_format($a['total_price'], 0, '', ' ') ?></span>
                            <span class="price-unit"> FCFA</span>
                        </td>
                        <td>
                            <?php
                            // Map original badge class → our pill class
                            if (str_contains($badgeClass, 'green')) $pillClass = 'pill-done';
                            elseif (str_contains($badgeClass, 'red')) $pillClass = 'pill-failed';
                            elseif (str_contains($badgeClass, 'blue')) $pillClass = 'pill-transit';
                            else $pillClass = 'pill-pending';
                            ?>
                            <span class="pill <?= $pillClass ?>"><?= htmlspecialchars($label) ?></span>
                        </td>
                        <td>
                            <a href="index.php?action=delivery/details&order_id=<?= $a['id'] ?>" class="detail-link">
                                Voir
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pied de tableau -->
            <div style="padding:12px 18px;border-top:1px solid #FEF3C7;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:12px;color:#A16207;" id="visibleCount">
                    <?= count($assignments) ?> livraison<?= count($assignments) > 1 ? 's' : '' ?>
                </span>
                <span style="font-size:11px;color:#D97706;">Plateforme de livraison</span>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
(function () {
    const rows    = document.querySelectorAll('#deliveryTable tbody tr');
    const search  = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const countEl = document.getElementById('visibleCount');
    let activeFilter = 'all';

    function applyFilters() {
        const q = (search?.value ?? '').toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => {
            const matchFilter = activeFilter === 'all' || row.dataset.filter === activeFilter;
            const matchSearch = !q || row.dataset.search.includes(q);
            const show = matchFilter && matchSearch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.textContent = `${visible} livraison${visible > 1 ? 's' : ''}`;
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            applyFilters();
        });
    });

    search?.addEventListener('input', applyFilters);
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>