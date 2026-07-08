<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
$product = $product ?? null;
$imgUrl = 'public/images/placeholder.png';
$found = false;
if (!empty($product['image'])) {
    $images = is_array($product['image']) ? $product['image'] : explode(',', $product['image']);
    $first = trim($images[0] ?? '');
    if (!empty($first)) {
        if (filter_var($first, FILTER_VALIDATE_URL)) {
            $imgUrl = $first;
            $found = true;
        } else {
            foreach ([$first, basename($first)] as $cand) {
                $imgPath = __DIR__ . '/../../public/images/' . $cand;
                if (file_exists($imgPath)) {
                    $imgUrl = 'public/images/' . $cand;
                    $found = true;
                    break;
                }
            }
        }
    }
}
if (!$found) {
    $nameLower = strtolower($product['name'] ?? '');
    $categoryLower = strtolower($product['category_name'] ?? '');
    $base = '';
    if (strpos($nameLower, 'lapin') !== false || strpos($categoryLower, 'lapin') !== false) {
        $base = 'lapin';
    } elseif (strpos($nameLower, 'poule') !== false || strpos($categoryLower, 'poule') !== false || strpos($nameLower, 'poulet') !== false) {
        $base = 'poulet';
    }
    if ($base) {
        foreach (['svg', 'png', 'jpg', 'jpeg'] as $ext) {
            $p = __DIR__ . '/../../public/images/' . $base . '.' . $ext;
            if (file_exists($p)) {
                $imgUrl = 'public/images/' . $base . '.' . $ext;
                break;
            }
        }
    }
}
$cartQty = !empty($_SESSION['cart'][$product['id']]) ? max(0, intval($_SESSION['cart'][$product['id']]['quantity'] ?? 0)) : 0;
$remainingStock = !empty($product) ? max(0, intval($product['stock'] ?? 0) - $cartQty) : 0;
$inStock = !empty($product) && $remainingStock > 0;
$stockLabel = $inStock ? $remainingStock . ' restant' . ($remainingStock > 1 ? 's' : '') : 'Rupture de stock';
$stockClass = $inStock ? 'fm-stock-ok' : 'fm-stock-out';
$categoryLabel = htmlspecialchars($product['category_name'] ?? 'Produit');
?>

<div class="page-content pb-16">
    <?php if (empty($product)) : ?>
        <div class="fm-empty-state">
            <div class="fm-empty-icon">📦</div>
            <h1 class="fm-empty-title">Produit introuvable</h1>
            <p class="fm-empty-sub">Ce produit n’existe plus ou n’est plus disponible.</p>
            <a href="index.php?action=products" class="fm-btn-primary">Voir tous les produits</a>
        </div>
    <?php else : ?>
        <div class="fm-product-detail-card" data-product-id="<?= intval($product['id'] ?? 0) ?>" data-base-stock="<?= intval($product['stock'] ?? 0) ?>" data-current-cart-qty="<?= $cartQty ?>">
            <div class="fm-product-detail-grid">
                <div class="fm-product-detail-media">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($product['name'] ?? 'Produit') ?>" class="fm-product-detail-image">
                </div>
                <div class="fm-product-detail-info">
                    <div>
                        <p class="fm-product-detail-eyebrow">Produit</p>
                        <h1 class="fm-product-detail-title"><?= htmlspecialchars($product['name'] ?? 'Produit') ?></h1>
                        <p class="fm-product-detail-subtitle">Catégorie : <?= $categoryLabel ?></p>
                    </div>

                    <div class="fm-product-detail-price-box">
                        <div class="fm-product-detail-price-row">
                            <span class="fm-product-detail-price-label">Prix</span>
                            <span class="fm-product-detail-price-value"><?= number_format((float)($product['price'] ?? 0), 0, '', ' ') ?> FCFA</span>
                        </div>
                        <div class="fm-product-detail-price-row fm-product-detail-price-row--secondary">
                            <span class="fm-product-detail-price-label">Disponibilité</span>
                            <span class="fm-stock-badge <?= $stockClass ?>" data-stock-badge><?= $stockLabel ?></span>
                        </div>
                    </div>

                    <div>
                        <h2 class="fm-product-detail-section-title">Description</h2>
                        <p class="fm-product-detail-description">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'Aucune description disponible.')) ?>
                        </p>
                    </div>

                    <div class="fm-product-detail-actions">
                        <a href="index.php?action=products" class="fm-product-detail-link">← Retour au catalogue</a>
                        <?php if ($inStock) : ?>
                            <form action="index.php?action=cart/add" method="post" class="fm-product-detail-form" data-product-form>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                                <input type="hidden" name="product_id" value="<?= intval($product['id'] ?? 0) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="fm-product-detail-btn-primary">🛒 Ajouter au panier</button>
                            </form>
                        <?php else : ?>
                            <button type="button" class="fm-product-detail-btn-disabled" disabled>Indisponible</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>