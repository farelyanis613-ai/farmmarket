<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="page-content pb-16">

    <!-- ── En-tête ────────────────────────────────────── -->
    <div class="page-header">
        <div class="fm-filter-bar">
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
        <div class="empty-state">
            <div class="fm-empty-icon">📦</div>
            <h2 class="fm-empty-title">Aucun produit trouvé</h2>
            <p class="fm-empty-sub">Aucun produit disponible pour cette catégorie pour le moment.</p>
            <a href="index.php?action=products" class="btn-primary">Voir tous les produits</a>
        </div>

    <?php else : ?>

        <!-- ── Grille produits ─────────────────────────── -->
        <div class="grid-cards" id="productsGrid">
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

                $inStock     = intval($product['stock'] ?? 0) > 0;
                $stockClass  = $inStock ? 'fm-stock-ok' : 'fm-stock-out';
                $stockLabel  = $inStock ? $product['stock'] . ' en stock' : 'Rupture de stock';
                $catLabel    = htmlspecialchars($product['category_name'] ?? 'Produit');
                $catEmoji    = $catIcons[$product['category_name'] ?? ''] ?? '🌿';
            ?>
                <article class="section-card fm-product-card fm-reveal"
                         data-price="<?= intval($product['price']) ?>"
                         data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
                         data-stock="<?= $inStock ? 1 : 0 ?>">

                    <!-- Image -->
                    <div class="fm-product-img-wrap">
                        <img src="<?= htmlspecialchars($imgUrl) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="fm-product-img"
                             loading="lazy">
                        <!-- Badge catégorie -->
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
                            <!-- Prix + stock -->
                            <div class="fm-price-row">
                                <span class="fm-price"><?= number_format($product['price'], 0, '', ' ') ?> <span class="fm-fcfa">FCFA</span></span>
                                <span class="fm-stock-badge <?= $stockClass ?>"><?= $stockLabel ?></span>
                            </div>

                            <!-- Actions -->
                            <div class="fm-product-actions">
                                <a href="index.php?action=product&id=<?= intval($product['id']) ?>" class="btn-secondary fm-btn-details">
                                    Détails
                                </a>
                                <?php if ($inStock) : ?>
                                    <form action="index.php?action=cart&op=add" method="post" class="fm-add-form">
                                        <input type="hidden" name="product_id" value="<?= intval($product['id']) ?>">
                                        <input type="hidden" name="quantity"   value="1">
                                        <button type="submit" class="fm-btn-add">🛒 Ajouter</button>
                                    </form>
                                <?php else : ?>
                                    <button type="button" class="fm-btn-disabled" disabled>Indisponible</button>
                                <?php endif; ?>
                            </div>

                        </div><!-- .fm-product-foot -->
                    </div><!-- .fm-product-body -->

                </article>
            <?php endforeach; ?>
        </div><!-- #productsGrid -->

    <?php endif; ?>

</div><!-- .page-content -->

<style>
/* ── Pilules catégorie ────────────────────────── */
.fm-cat-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.35rem 0.9rem;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 160ms;
}
.fm-cat-pill:hover { border-color: #16a34a; color: #15803d; background: #f0fdf4; }
.fm-cat-active { background: #16a34a; border-color: #16a34a; color: #fff; font-weight: 700; }
.fm-cat-active:hover { background: #15803d; color: #fff; }

/* ── Filtre bar ───────────────────────────────── */
.fm-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: flex-end;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem 1.25rem;
}
.fm-filter-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; flex: 1; }
.fm-sort { display: flex; align-items: center; gap: 8px; }
.fm-sort-label { font-size: 0.82rem; color: #64748b; white-space: nowrap; }
.fm-select {
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 0.5rem 0.9rem;
    font-size: 0.88rem;
    color: #0f172a;
    background: #f8fafc;
    cursor: pointer;
    transition: border-color 150ms;
    appearance: none;
}
.fm-select:focus { outline: none; border-color: #16a34a; }
.fm-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.5rem 1.3rem;
    border-radius: 9999px;
    background: #16a34a;
    color: #fff;
    font-weight: 700;
    font-size: 0.88rem;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background 150ms, transform 150ms;
}
.fm-btn-primary:hover { background: #15803d; transform: translateY(-1px); }
.fm-btn-outline {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 150ms;
}
.fm-btn-outline:hover { border-color: #94a3b8; color: #0f172a; }

/* ── État vide ────────────────────────────────── */
.fm-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    padding: 4rem 2rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
}
.fm-empty-icon  { font-size: 3.5rem; }
.fm-empty-title { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin: 0; }
.fm-empty-sub   { font-size: 0.88rem; color: #64748b; max-width: 30ch; margin: 0; }

/* ── Grille produits ──────────────────────────── */
.fm-products-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: 1fr;
}
@media (min-width: 540px)  { .fm-products-grid { grid-template-columns: repeat(2,1fr); } }
@media (min-width: 900px)  { .fm-products-grid { grid-template-columns: repeat(3,1fr); } }
@media (min-width: 1200px) { .fm-products-grid { grid-template-columns: repeat(4,1fr); } }

/* ── Carte produit ────────────────────────────── */
.fm-product-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 200ms, transform 200ms;
}
.fm-product-card:hover { box-shadow: 0 10px 32px rgba(0,0,0,0.10); transform: translateY(-3px); }

/* Image */
.fm-product-img-wrap {
    position: relative;
    height: 190px;
    overflow: hidden;
    background: #f1f5f9;
}
.fm-product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 350ms ease;
}
.fm-product-card:hover .fm-product-img { transform: scale(1.04); }
.fm-img-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(4px);
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 700;
    color: #0f172a;
    padding: 0.25rem 0.65rem;
    border: 1px solid rgba(0,0,0,0.06);
}
.fm-rupture-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

/* Corps */
.fm-product-body {
    padding: 1rem 1.1rem 1.1rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 6px;
}
.fm-product-name {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}
.fm-product-desc {
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
    flex: 1;
}

/* Pied */
.fm-product-foot {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.fm-price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    flex-wrap: wrap;
}
.fm-price {
    font-size: 1.15rem;
    font-weight: 800;
    color: #15803d;
    line-height: 1;
}
.fm-fcfa { font-size: 0.72rem; font-weight: 500; color: #64748b; }
.fm-stock-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
}
.fm-stock-ok  { background: #dcfce7; color: #166534; }
.fm-stock-out { background: #fee2e2; color: #991b1b; }

/* Boutons action */
.fm-product-actions {
    display: flex;
    gap: 8px;
}
.fm-btn-details {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.75rem;
    border-radius: 9px;
    background: #f1f5f9;
    color: #334155;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 150ms;
}
.fm-btn-details:hover { background: #e2e8f0; }
.fm-add-form { flex: 1; }
.fm-btn-add {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 0.5rem 0.75rem;
    border-radius: 9px;
    background: #16a34a;
    color: #fff;
    font-size: 0.82rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: background 150ms, transform 150ms;
}
.fm-btn-add:hover { background: #15803d; transform: translateY(-1px); }
.fm-btn-add:active { transform: scale(0.97); }
.fm-btn-disabled {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border-radius: 9px;
    background: #f1f5f9;
    color: #94a3b8;
    font-size: 0.82rem;
    font-weight: 600;
    border: none;
    cursor: not-allowed;
}

/* ── Reveal ───────────────────────────────────── */
.fm-reveal {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity 0.42s ease, transform 0.42s ease;
}
.fm-reveal.visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) {
    .fm-reveal { opacity: 1; transform: none; transition: none; }
}
</style>

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