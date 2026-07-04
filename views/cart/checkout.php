<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['product']['price'] * $item['quantity'];
    }
    $deliveryFeeHome = 2000;
    $total = $subtotal + $deliveryFeeHome;
?>

<div class="page-content pb-16">

    <!-- ── En-tête ────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Confirmation de commande</h1>
            <p class="page-subtitle">Vérifiez vos articles et choisissez votre mode de livraison.</p>
        </div>
        <a href="index.php?action=cart" class="btn-ghost">← Modifier le panier</a>
    </div>

    <!-- ── Barre de progression ───────────────────────── -->
    <div class="fm-steps" aria-label="Étapes de commande">
        <div class="fm-step fm-step-done">
            <div class="fm-step-dot">✓</div>
            <span class="fm-step-lbl">Panier</span>
        </div>
        <div class="fm-step-line fm-step-line-done"></div>
        <div class="fm-step fm-step-active">
            <div class="fm-step-dot">📋</div>
            <span class="fm-step-lbl">Confirmation</span>
        </div>
        <div class="fm-step-line"></div>
        <div class="fm-step fm-step-todo">
            <div class="fm-step-dot">💳</div>
            <span class="fm-step-lbl">Paiement</span>
        </div>
        <div class="fm-step-line"></div>
        <div class="fm-step fm-step-todo">
            <div class="fm-step-dot">🎉</div>
            <span class="fm-step-lbl">Terminé</span>
        </div>
    </div>

    <div class="fm-co-grid">

        <!-- ══════════════════════════
             COLONNE GAUCHE
        ══════════════════════════ -->
        <div class="fm-co-left">

            <!-- Articles -->
            <div class="fm-section-card">
                <h2 class="fm-section-title">🛒 Vos articles</h2>
                <div class="fm-items-list">
                    <?php foreach ($cart as $item) :
                        $lineTotal = $item['product']['price'] * $item['quantity'];
                    ?>
                    <div class="fm-item-row fm-reveal">
                        <div class="fm-item-info">
                            <p class="fm-item-name"><?= htmlspecialchars($item['product']['name']) ?></p>
                            <p class="fm-item-meta">
                                Qté : <strong><?= intval($item['quantity']) ?></strong>
                                &nbsp;·&nbsp;
                                P.U. : <strong><?= number_format($item['product']['price'], 0, '', ' ') ?> FCFA</strong>
                            </p>
                        </div>
                        <span class="fm-item-price"><?= number_format($lineTotal, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Mode de livraison -->
            <form id="checkoutCompleteForm" action="index.php?action=checkout/complete" method="post">

                <div class="section-card">
                    <h2 class="section-title">🚚 Mode de livraison</h2>
                    <div class="fm-delivery-grid">

                        <!-- Livraison domicile -->
                        <label class="fm-delivery-card" id="labelHome">
                            <input type="radio" name="delivery_type" value="home" class="fm-radio-hidden" checked>
                            <div class="fm-delivery-icon">🚚</div>
                            <div class="fm-delivery-info">
                                <p class="fm-delivery-name">Livraison à domicile</p>
                                <p class="fm-delivery-desc">Recevez votre commande directement chez vous.</p>
                                <span class="fm-delivery-fee fm-fee-paid">+<?= number_format($deliveryFeeHome, 0, '', ' ') ?> FCFA</span>
                            </div>
                            <span class="fm-delivery-check">✓</span>
                        </label>

                        <!-- Retrait boutique -->
                        <label class="fm-delivery-card" id="labelShop">
                            <input type="radio" name="delivery_type" value="shop" class="fm-radio-hidden">
                            <div class="fm-delivery-icon">🏪</div>
                            <div class="fm-delivery-info">
                                <p class="fm-delivery-name">Retrait en boutique</p>
                                <p class="fm-delivery-desc">Venez chercher votre commande à Hévié Akossavié.</p>
                                <span class="fm-delivery-fee fm-fee-free">Gratuit</span>
                            </div>
                            <span class="fm-delivery-check">✓</span>
                        </label>

                    </div>
                </div>

                <!-- Opérateur Mobile Money -->
                <div class="section-card">
                    <h2 class="section-title">💳 Opérateur Mobile Money</h2>
                    <div class="fm-op-pills" id="operatorPills">
                        <?php
                        $ops = [
                            'Moov Money'   => ['color'=>'#0066CC','bg'=>'#E6F0FB','logo'=>'M'],
                            'MTN mobile'   => ['color'=>'#F5A623','bg'=>'#FEF3DC','logo'=>'M'],
                            'Celtiis Cash' => ['color'=>'#E01B24','bg'=>'#FDEAEA','logo'=>'C'],
                        ];
                        foreach ($ops as $name => $op) : ?>
                        <button type="button"
                                class="fm-op-pill <?= $name === 'Moov Money' ? 'fm-op-active' : '' ?>"
                                data-op="<?= htmlspecialchars($name) ?>"
                                style="--op-color:<?= $op['color'] ?>;--op-bg:<?= $op['bg'] ?>">
                            <span class="fm-op-logo" style="background:<?= $op['color'] ?>"><?= $op['logo'] ?></span>
                            <?= htmlspecialchars($name) ?>
                            <span class="fm-op-check">✓</span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedOperator" value="Moov Money">
                </div>

                <input type="hidden" name="delivery_fee"   id="deliveryFeeInput"  value="<?= $deliveryFeeHome ?>">
                <input type="hidden" name="total_amount"   id="finalAmountInput"  value="<?= $total ?>">

                <!-- Bouton continuer -->
                <button id="continueToMobile" type="button" class="btn-primary w-full">
                    Continuer vers le paiement →
                </button>
                <p class="fm-continue-hint">Vous serez redirigé vers la page de paiement sécurisé Mobile Money.</p>

            </form>
        </div>

        <!-- ══════════════════════════
             COLONNE DROITE — Récap
        ══════════════════════════ -->
        <aside class="fm-co-right">
            <div class="fm-recap-card section-card">
                <h2 class="section-title fm-recap-title">Récapitulatif</h2>

                <div class="fm-recap-rows">
                    <div class="fm-recap-row">
                        <span>Sous-total</span>
                        <span><?= number_format($subtotal, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <div class="fm-recap-row">
                        <span>Livraison</span>
                        <span id="recapFee">+<?= number_format($deliveryFeeHome, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <div class="fm-recap-total">
                        <span>Total</span>
                        <span id="recapTotal"><?= number_format($total, 0, '', ' ') ?> FCFA</span>
                    </div>
                </div>

                <!-- Opérateur -->
                <div class="fm-recap-op" id="recapOpBlock">
                    <span class="fm-recap-op-icon" id="recapOpIcon" style="background:#0066CC">M</span>
                    <div>
                        <p class="fm-recap-op-name" id="recapOpName">Moov Money</p>
                        <p class="fm-recap-op-sub">Paiement sécurisé 🔒</p>
                    </div>
                </div>

                <!-- Garanties -->
                <ul class="fm-guarantees">
                    <li>✅ Confirmation par SMS</li>
                    <li>✅ Facture PDF disponible</li>
                    <li>✅ Livraison tracée par GPS</li>
                    <li>✅ Service client réactif</li>
                </ul>

                <!-- Résumé articles (sidebar) -->
                <div class="fm-recap-items">
                    <p class="fm-recap-items-title"><?= count($cart) ?> article<?= count($cart) > 1 ? 's' : '' ?> dans le panier</p>
                    <?php foreach ($cart as $item) : ?>
                    <div class="fm-recap-item-row">
                        <span class="fm-recap-item-name"><?= htmlspecialchars($item['product']['name']) ?> ×<?= intval($item['quantity']) ?></span>
                        <span><?= number_format($item['product']['price'] * $item['quantity'], 0, '', ' ') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

    </div>
</div>

<!-- Inline styles moved to public/css/style.css -->
<!-- views/cart/checkout.php: original <style> block consolidated into public/css/style.css -->

<script>
(function () {
    var subtotal     = <?= intval($subtotal) ?>;
    var feeHome      = <?= intval($deliveryFeeHome) ?>;
    var opData       = {
        'Moov Money':   { color: '#0066CC', logo: 'M' },
        'MTN mobile':   { color: '#F5A623', logo: 'M' },
        'Celtiis Cash': { color: '#E01B24', logo: 'C' },
    };

    /* ── Scroll reveal ──────────────────────── */
    var reveals = document.querySelectorAll('.fm-reveal');
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.1 });
    reveals.forEach(function (el) { obs.observe(el); });

    /* ── Mode livraison ─────────────────────── */
    function fmt(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '); }

    function selectDelivery(card) {
        document.querySelectorAll('.fm-delivery-card').forEach(function (c) { c.classList.remove('fm-delivery-selected'); });
        card.classList.add('fm-delivery-selected');
        var radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        var isHome  = radio && radio.value === 'home';
        var fee     = isHome ? feeHome : 0;
        var total   = subtotal + fee;

        document.getElementById('deliveryFeeInput').value  = fee;
        document.getElementById('finalAmountInput').value  = total;
        document.getElementById('recapFee').textContent    = fee > 0 ? '+' + fmt(fee) + ' FCFA' : 'Gratuit';
        document.getElementById('recapTotal').textContent  = fmt(total) + ' FCFA';
    }

    document.querySelectorAll('.fm-delivery-card').forEach(function (card) {
        card.addEventListener('click', function () { selectDelivery(card); });
    });
    // initialiser la première carte sélectionnée
    var firstCard = document.getElementById('labelHome');
    if (firstCard) selectDelivery(firstCard);

    /* ── Opérateur ──────────────────────────── */
    document.querySelectorAll('.fm-op-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.fm-op-pill').forEach(function (p) { p.classList.remove('fm-op-active'); });
            pill.classList.add('fm-op-active');

            var op = pill.dataset.op;
            document.getElementById('selectedOperator').value = op;

            var d = opData[op] || {};
            var icon = document.getElementById('recapOpIcon');
            var name = document.getElementById('recapOpName');
            if (icon) { icon.style.background = d.color || '#16a34a'; icon.textContent = d.logo || 'M'; }
            if (name) { name.textContent = op; }
        });
    });

    /* ── Bouton continuer ───────────────────── */
    document.getElementById('continueToMobile').addEventListener('click', function () {
        var deliveryType = (document.querySelector('input[name="delivery_type"]:checked') || {}).value || 'home';
        var fee          = document.getElementById('deliveryFeeInput').value;
        var operator     = document.getElementById('selectedOperator').value;
        window.location  = 'index.php?action=checkout/mobile'
            + '&delivery_type='  + encodeURIComponent(deliveryType)
            + '&delivery_fee='   + encodeURIComponent(fee)
            + '&operator='       + encodeURIComponent(operator);
    });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>