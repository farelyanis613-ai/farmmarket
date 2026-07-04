<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Idéalement à déplacer dans le <head> de partials/header.php pour de meilleures perfs -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Inline styles moved to public/css/style.css -->
<!-- views/farmer/products.php: original <style> block consolidated into public/css/style.css -->

<div class="container mx-auto p-6 font-body text-slate-800">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-slate-900">Mes produits</h1>
            <p class="text-sm text-slate-500 mt-1"><?= count($products) ?> produit<?= count($products) > 1 ? 's' : '' ?> au catalogue</p>
        </div>
        <a href="index.php?action=farmer/add-product" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Ajouter
        </a>
    </div>

    <?php if (empty($products)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-4" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
            </div>
            <p class="text-slate-600 font-medium mb-1">Aucun produit pour le moment</p>
            <p class="text-sm text-slate-400 mb-5">Ajoutez votre premier produit pour qu'il apparaisse ici.</p>
            <a href="index.php?action=farmer/add-product" class="bg-emerald-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-emerald-700 transition-colors">Ajouter un produit</a>
        </div>
    <?php else: ?>

        <!-- Recherche et filtre -->
        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="search" id="product-search" placeholder="Rechercher un produit..."
                       class="w-full rounded-lg border border-slate-200 pl-9 pr-4 py-2.5 text-sm focus:border-emerald-500" aria-label="Rechercher un produit par nom">
            </div>
            <select id="stock-filter" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500" aria-label="Filtrer par niveau de stock">
                <option value="">Tous les stocks</option>
                <option value="out">Rupture de stock</option>
                <option value="low">Stock faible</option>
                <option value="medium">Stock moyen</option>
                <option value="high">En stock</option>
            </select>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="products-table">
                    <caption class="sr-only">Liste de vos produits avec prix, stock et actions disponibles</caption>
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-xs">Produit</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-xs">Prix</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-xs">Stock</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-xs">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product):
                            $stock = (int) $product['stock'];
                            if ($stock === 0) {
                                $stockStatus = 'out';
                                $badgeClass = 'bg-red-50 text-red-600';
                                $badgeLabel = 'Rupture de stock';
                            } elseif ($stock <= 5) {
                                $stockStatus = 'low';
                                $badgeClass = 'bg-red-50 text-red-600';
                                $badgeLabel = 'Stock faible';
                            } elseif ($stock <= 20) {
                                $stockStatus = 'medium';
                                $badgeClass = 'bg-amber-50 text-amber-600';
                                $badgeLabel = 'Stock moyen';
                            } else {
                                $stockStatus = 'high';
                                $badgeClass = 'bg-emerald-50 text-emerald-600';
                                $badgeLabel = 'En stock';
                            }
                            $productName = htmlspecialchars($product['name']);
                            $productId = (int) $product['id'];
                        ?>
                            <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60 transition-colors"
                                data-name="<?= $productName ?>" data-stock-status="<?= $stockStatus ?>">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="public/images/<?= htmlspecialchars($product['image']) ?>" alt="" loading="lazy"
                                                 class="w-10 h-10 rounded-lg object-cover border border-slate-100 shrink-0">
                                        <?php else: ?>
                                            <span class="w-10 h-10 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center shrink-0" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
                                            </span>
                                        <?php endif; ?>
                                        <span class="font-medium text-slate-800"><?= $productName ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-slate-700"><?= number_format((float) $product['price'], 0, ',', ' ') ?> FCFA</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full <?= $badgeClass ?>">
                                        <?= $badgeLabel ?> (<?= $stock ?>)
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-4">
                                        <a href="index.php?action=farmer/edit-product&id=<?= $productId ?>"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-medium"
                                           aria-label="Modifier <?= $productName ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                            Modifier
                                        </a>
                                        <form method="post" action="index.php?action=farmer/delete-product" class="inline"
                                              onsubmit="return confirm('Supprimer « <?= $productName ?> » ? Cette action est irréversible.');">
                                            <input type="hidden" name="id" value="<?= $productId ?>">
                                            <?php if (isset($csrf_token)): ?>
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <?php endif; ?>
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 font-medium"
                                                    aria-label="Supprimer <?= $productName ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p id="no-results-message" class="hidden text-center text-sm text-slate-400 py-10">Aucun produit ne correspond à votre recherche.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('product-search');
        var stockFilter = document.getElementById('stock-filter');
        var rows = document.querySelectorAll('#products-table tbody tr');
        var noResultsMessage = document.getElementById('no-results-message');

        function applyFilters() {
            var term = (searchInput.value || '').trim().toLowerCase();
            var status = stockFilter.value;
            var visibleCount = 0;

            rows.forEach(function (row) {
                var matchesName = row.dataset.name.toLowerCase().includes(term);
                var matchesStatus = !status || row.dataset.stockStatus === status;
                var visible = matchesName && matchesStatus;
                row.classList.toggle('hidden', !visible);
                if (visible) visibleCount++;
            });

            if (noResultsMessage) {
                noResultsMessage.classList.toggle('hidden', visibleCount !== 0);
            }
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (stockFilter) stockFilter.addEventListener('change', applyFilters);
    });
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>