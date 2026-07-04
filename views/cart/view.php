<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="max-w-6xl mx-auto pb-12">

  <?php if (empty($cartItems)): ?>
  <!-- ── Panier vide ── -->
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-20 h-20 rounded-2xl bg-emerald-50 flex items-center justify-center mb-5">
      <svg class="w-10 h-10 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
    </div>
    <h1 class="text-xl font-semibold text-slate-800 mb-2">Votre panier est vide</h1>
    <p class="text-slate-500 text-sm mb-6 max-w-xs">Parcourez notre catalogue de produits frais issus de nos éleveurs locaux.</p>
    <a href="index.php?action=products"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M19 11H7.83l4.88-4.88c.39-.39.39-1.03 0-1.42-.39-.39-1.02-.39-1.41 0l-6.59 6.59c-.39.39-.39 1.02 0 1.41l6.59 6.59c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L7.83 13H19c.55 0 1-.45 1-1s-.45-1-1-1z"/>
      </svg>
      Voir les produits
    </a>
  </div>

  <?php else: ?>
  <!-- ── Panier avec articles ── -->
  <div class="flex items-center justify-between mb-5">
    <h1 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      Mon panier
      <span class="text-xs font-medium bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full">
        <?= array_sum(array_column($cartItems, 'quantity')) ?> article<?= array_sum(array_column($cartItems, 'quantity')) > 1 ? 's' : '' ?>
      </span>
    </h1>
    <form method="post" action="index.php?action=cart&action2=clear" onsubmit="return confirm('Vider le panier ?')">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
      <button type="submit" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
        Vider le panier
      </button>
    </form>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

    <!-- ── Colonne articles (2/3) ── -->
    <div class="lg:col-span-2 flex flex-col gap-4">

      <!-- Liste des articles -->
      <div class="page-card overflow-hidden">
        <?php foreach ($cartItems as $index => $item):
          $lineTotal = $item['quantity'] * $item['price'];
          $isLast    = $index === count($cartItems) - 1;
        ?>
        <div class="flex items-center gap-3 px-5 py-4 <?= $isLast ? '' : 'border-b border-slate-100' ?> hover:bg-slate-50/50 transition-colors group">

          <!-- Icône catégorie -->
          <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 text-lg
            <?= match(strtolower($item['category'] ?? '')) {
              'lapin'   => 'bg-violet-50',
              'volaille'=> 'bg-amber-50',
              default   => 'bg-emerald-50',
            } ?>">
            <?= match(strtolower($item['category'] ?? '')) {
              'lapin'   => '🐇',
              'volaille'=> '🐔',
              'oeuf', 'oeufs' => '🥚',
              default   => '🌿',
            } ?>
          </div>

          <!-- Infos produit -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-900 truncate"><?= htmlspecialchars($item['product_name']) ?></p>
            <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($item['category'] ?? '') ?> · <?= number_format($item['price'], 0, '', ' ') ?> FCFA / unité</p>
            <?php if (!empty($item['farmer_name'])): ?>
              <p class="text-xs text-emerald-600 mt-0.5">
                <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <?= htmlspecialchars($item['farmer_name']) ?>
              </p>
            <?php endif; ?>
          </div>

          <!-- Contrôle quantité -->
          <form method="POST" action="index.php?action=cart&sub=update" class="flex items-center">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
            <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
              <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>"
                      class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors text-base"
                      <?= $item['quantity'] <= 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : '' ?>>
                −
              </button>
              <span class="w-8 h-8 flex items-center justify-center text-sm font-medium text-slate-900 border-x border-slate-200">
                <?= $item['quantity'] ?>
              </span>
              <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>"
                      class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors text-base">
                +
              </button>
            </div>
          </form>

          <!-- Prix de ligne -->
          <div class="text-right min-w-[90px] flex-shrink-0">
            <p class="text-sm font-semibold text-emerald-700"><?= number_format($lineTotal, 0, '', ' ') ?> FCFA</p>
          </div>

          <!-- Supprimer -->
          <form method="post" action="index.php?action=cart&sub=remove" class="opacity-0 group-hover:opacity-100 ml-1 flex-shrink-0" title="Retirer du panier">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
            <button type="submit" class="text-slate-300 hover:text-red-400 transition-all bg-transparent border-0 p-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </form>

        </div>
        <?php endforeach; ?>
      </div>

      <!-- Bandeau certifications -->
      <div class="section-card px-5 py-4">
        <p class="text-xs font-medium text-slate-500 mb-3 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          Produits certifiés élevage local
        </p>
        <div class="flex flex-wrap gap-2">
          <?php foreach (['Élevage local Bénin', 'Sans antibiotiques', 'Fraîcheur garantie', 'Filière tracée'] as $tag): ?>
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-100 px-3 py-1 rounded-full"><?= $tag ?></span>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- ── Colonne récapitulatif (1/3) ── -->
    <div class="lg:col-span-1">
      <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden sticky top-20">

        <div class="px-5 py-4 border-b border-slate-100">
          <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Récapitulatif
          </h2>
        </div>

        <form method="POST" action="index.php?action=checkout" class="p-5 flex flex-col gap-4">

          <div class="bg-slate-50 rounded-lg p-3.5 flex flex-col gap-2">
            <div class="flex justify-between text-xs text-slate-500">
              <span>Sous-total</span>
              <span class="font-medium text-slate-700"><?= number_format($subtotal, 0, '', ' ') ?> FCFA</span>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
              <span>Frais de livraison</span>
              <span id="delivery-display" class="font-medium text-slate-700">
                <?= $deliveryFee > 0 ? number_format($deliveryFee, 0, '', ' ') . ' FCFA' : 'Gratuit' ?>
              </span>
            </div>
            <div class="border-t border-slate-200 pt-2 mt-1 flex justify-between items-baseline">
              <span class="text-sm font-medium text-slate-800">Total</span>
              <span class="text-lg font-semibold text-emerald-700" id="total-display">
                <?= number_format($totalPrice, 0, '', ' ') ?> FCFA
              </span>
            </div>
          </div>

          <!-- CTA -->
          <button type="submit"
                  class="btn-primary w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Passer la commande
          </button>

          <a href="index.php?action=products"
             class="btn-secondary w-full">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Continuer mes achats
          </a>

          <p class="text-[11px] text-slate-400 text-center flex items-center justify-center gap-1">
            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Paiement sécurisé · Données protégées
          </p>

        </form>
      </div>
    </div>

  </div>
  <?php endif; ?>
</div>

<script>
const subtotalBase  = <?= (int)($subtotal ?? 0) ?>;
let   currentFee    = <?= (int)($deliveryFee ?? 1500) ?>;
let   promoDiscount = <?= (int)($promoDiscount ?? 0) ?>;

function recalcTotal() {
  const total = Math.max(0, subtotalBase + currentFee - promoDiscount);
  const el = document.getElementById('total-display');
  if (el) el.textContent = total.toLocaleString('fr-FR') + ' FCFA';
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>