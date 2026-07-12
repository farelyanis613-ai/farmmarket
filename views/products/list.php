<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
/* ── Icônes par défaut si le contrôleur ne les fournit pas ── */
$defaultCatIcons = [
    'Tout'    => '🌿',
    'Lapins'  => '🐇',
    'Lapin'   => '🐇',
    'Poulets' => '🐔',
    'Poulet'  => '🐔',
    'Poussin' => '🐣',
    'Poussins'=> '🐣',
    'Viande'  => '🥩',
    'Œufs'    => '🥚',
    'Oeufs'   => '🥚',
];
$catIcons = $catIcons ?? [];
?>

<div class="page-content page-products-list pb-16">

    <!-- ── En-tête catalogue ─────────────────────────── -->
    <div class="fm-catalogue-header">
        <div class="fm-catalogue-title-row">
            <div>
                <h1 class="fm-catalogue-title">Notre catalogue</h1>
                <p class="fm-catalogue-sub">Produits frais d'élevage, directement de l'éleveur</p>
            </div>
            <?php if (!empty($products)) : ?>
                <span class="fm-catalogue-count"><?= count($products) ?> produit<?= count($products) > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>

        <!-- ── Pilules catégories ──────────────────────── -->
        <?php if (!empty($categoryLinks)) : ?>
        <div class="fm-cat-pills">
            <?php foreach ($categoryLinks as $cat) :
                $emoji = $cat['emoji']
                      ?? $defaultCatIcons[$cat['label']]
                      ?? '🌿';
            ?>
                <a href="<?= htmlspecialchars($cat['url']) ?>"
                   class="fm-cat-pill <?= !empty($cat['active']) ? 'fm-cat-active' : '' ?>">
                    <?= $emoji ?> <?= htmlspecialchars($cat['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── Barre filtre + tri ──────────────────────── -->
        <div class="fm-filter-bar">
            <div class="fm-filter-left">
                <a href="index.php?action=products" class="fm-btn-outline">Toutes les catégories</a>
                <button type="button" class="fm-btn-primary-sm">Filtrer</button>
            </div>
            <div class="fm-sort">
                <label for="sortSelect" class="fm-sort-label">Trier par :</label>
                <select id="sortSelect" class="fm-select">
                    <option value="">Par défaut</option>
                    <option value="price-asc">Prix croissant</option>
                    <option value="price-desc">Prix décroissant</option>
                    <option value="name">Nom (A–Z)</option>
                    <option value="stock">Disponibilité</option>
                </select>
            </div>
        </div>
    </div>

    <?php if (empty($products)) : ?>

        <!-- ── État vide ───────────────────────────────── -->
        <div class="fm-empty-state">
            <div class="fm-empty-icon">📦</div>
            <h2 class="fm-empty-title">Aucun produit trouvé</h2>
            <p class="fm-empty-sub">Aucun produit disponible pour cette catégorie pour le moment.</p>
            <a href="index.php?action=products" class="fm-btn-primary-sm">Voir tous les produits</a>
        </div>

    <?php else : ?>

        <!-- ── Grille produits ─────────────────────────── -->
        <div class="fm-products-grid" id="productsGrid">
            <?php foreach ($products as $product) :

                /* ── Résolution de l'image (logique originale conservée) ── */
                $imgUrl = 'public/images/placeholder.png';
                $found  = false;
                if (!empty($product['image'])) {
                    $images = is_array($product['image']) ? $product['image'] : explode(',', $product['image']);
                    $first  = trim($images[0]);
                    if (!empty($first)) {
                        if (filter_var($first, FILTER_VALIDATE_URL)) {
                            $imgUrl = $first; $found = true;
                        } else {
                            foreach ([$first, basename($first)] as $cand) {
                                $imgPath = __DIR__ . '/../../public/images/' . $cand;
                                if (file_exists($imgPath)) { $imgUrl = 'public/images/' . $cand; $found = true; break; }
                            }
                        }
                    }
                }
                if (!$found) {
                    $nl = strtolower($product['name'] ?? '');
                    $cl = strtolower($product['category_name'] ?? '');
                    $base = '';
                    if (strpos($nl,'lapin')!==false||strpos($cl,'lapin')!==false) $base='lapin';
                    elseif (strpos($nl,'poule')!==false||strpos($cl,'poule')!==false||strpos($nl,'poulet')!==false) $base='poulet';
                    if ($base) {
                        foreach (['svg','png','jpg','jpeg'] as $ext) {
                            $p = __DIR__.'/../../public/images/'.$base.'.'.$ext;
                            if (file_exists($p)) { $imgUrl='public/images/'.$base.'.'.$ext; break; }
                        }
                    }
                }

                $cartQty    = isset($_SESSION['cart'][$product['id']]) ? max(0, intval($_SESSION['cart'][$product['id']]['quantity'] ?? 0)) : 0;
                $remainingStock = max(0, intval($product['stock'] ?? 0) - $cartQty);
                $inStock    = $remainingStock > 0;
                $stockClass = $inStock ? 'fm-stock-ok' : 'fm-stock-out';
                $stockLabel = $inStock ? $remainingStock . ' restant' . ($remainingStock > 1 ? 's' : '') : 'Rupture de stock';
                $catLabel   = htmlspecialchars($product['category_name'] ?? 'Produit');
                $catEmoji   = $catIcons[$product['category_name'] ?? '']
                           ?? $defaultCatIcons[$product['category_name'] ?? '']
                           ?? '🌿';
            ?>
                <article class="fm-product-card fm-reveal"
                         data-product-id="<?= intval($product['id']) ?>"
                         data-price="<?= intval($product['price']) ?>"
                         data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
                         data-stock="<?= $inStock ? 1 : 0 ?>"
                         data-base-stock="<?= intval($product['stock'] ?? 0) ?>"
                         data-current-cart-qty="<?= $cartQty ?>">

                    <!-- Image -->
                    <div class="fm-product-img-wrap">
                        <img src="<?= htmlspecialchars($imgUrl) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="fm-product-img"
                             loading="lazy">
                        <span class="fm-img-badge"><?= $catEmoji ?> <?= $catLabel ?></span>
                        <?php if (!$inStock) : ?>
                            <div class="fm-rupture-overlay">Rupture</div>
                        <?php endif; ?>
                    </div>

                    <!-- Corps -->
                    <div class="fm-product-body">
                        <h2 class="fm-product-name"><?= htmlspecialchars($product['name']) ?></h2>
                        <p class="fm-product-desc"><?= htmlspecialchars($product['description'] ?? '') ?></p>

                        <div class="fm-product-foot">
                            <div class="fm-price-row">
                                <span class="fm-price"><?= number_format($product['price'], 0, '', ' ') ?> <span class="fm-fcfa">FCFA</span></span>
                                <span class="fm-stock-badge <?= $stockClass ?>" data-stock-badge><?= $stockLabel ?></span>
                            </div>

                            <div class="fm-product-actions">
                                <a href="index.php?action=product&id=<?= intval($product['id']) ?>" class="fm-btn-details">
                                    Détails
                                </a>
                                <?php if ($inStock) : ?>
                                    <form action="index.php?action=cart/add" method="post" class="fm-add-form" data-product-form>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                                        <input type="hidden" name="product_id" value="<?= intval($product['id']) ?>">
                                        <input type="hidden" name="quantity"   value="1">
                                        <button type="submit" class="fm-btn-add">🛒 Ajouter</button>
                                    </form>
                                <?php else : ?>
                                    <button type="button" class="fm-btn-disabled" disabled>Indisponible</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>
        </div><!-- #productsGrid -->

    <?php endif; ?>

</div><!-- .page-content -->

<script>
(function(){
    /* ── Scroll reveal ──────────────────────── */
    var cards = document.querySelectorAll('.fm-reveal');
    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e, i){
            if(e.isIntersecting){
                setTimeout(function(){ e.target.classList.add('visible'); }, i * 60);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    cards.forEach(function(c){ obs.observe(c); });

    /* ── Tri côté client ────────────────────── */
    var sortSelect = document.getElementById('sortSelect');
    var grid       = document.getElementById('productsGrid');
    if(sortSelect && grid){
        sortSelect.addEventListener('change', function(){
            var val   = this.value;
            var items = Array.from(grid.querySelectorAll('.fm-product-card'));
            items.sort(function(a, b){
                if(val === 'price-asc')  return parseInt(a.dataset.price) - parseInt(b.dataset.price);
                if(val === 'price-desc') return parseInt(b.dataset.price) - parseInt(a.dataset.price);
                if(val === 'name')       return a.dataset.name.localeCompare(b.dataset.name);
                if(val === 'stock')      return parseInt(b.dataset.stock) - parseInt(a.dataset.stock);
                return 0;
            });
            items.forEach(function(item){
                item.classList.remove('visible');
                grid.appendChild(item);
            });
            setTimeout(function(){
                items.forEach(function(item, i){
                    setTimeout(function(){ item.classList.add('visible'); }, i * 50);
                });
            }, 50);
        });
    }

    /* ── Feedback visuel ajout panier ──────── */
    document.querySelectorAll('.fm-add-form').forEach(function(form){
        form.addEventListener('submit', function(){
            var card = form.closest('.fm-product-card');
            var badge = card ? card.querySelector('[data-stock-badge]') : null;
            var qtyInput = form.querySelector('input[name="quantity"]');
            var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
            var currentCartQty = parseInt(card ? card.getAttribute('data-current-cart-qty') : 0, 10) || 0;
            var baseStock = parseInt(card ? card.getAttribute('data-base-stock') : 0, 10) || 0;
            var remaining = Math.max(0, baseStock - currentCartQty - qty);

            if (badge) {
                badge.textContent = remaining > 0 ? remaining + ' restant' + (remaining > 1 ? 's' : '') : 'Rupture de stock';
                badge.classList.remove('fm-stock-ok', 'fm-stock-out');
                badge.classList.add(remaining > 0 ? 'fm-stock-ok' : 'fm-stock-out');
            }
            if (card) {
                card.setAttribute('data-current-cart-qty', String(currentCartQty + qty));
            }

            var btn = form.querySelector('.fm-btn-add');
            if(btn){
                btn.textContent = '✓ Ajouté !';
                btn.style.background = '#166534';
                btn.disabled = true;
            }
        });
    });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>