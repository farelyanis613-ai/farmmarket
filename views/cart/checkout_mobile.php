<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$operator        = trim($_GET['operator'] ?? ($_SESSION['old_operator'] ?? 'Moov Money'));
$deliveryAddress = trim($_GET['address'] ?? ($_SESSION['old_delivery_address'] ?? $_SESSION['user']['address'] ?? ''));
$deliveryType    = trim($_GET['delivery_type'] ?? 'home');
$deliveryFee     = intval($_GET['delivery_fee'] ?? 0);
$phoneNumber     = trim($_SESSION['old_phone_number'] ?? '');
$subtotal        = floatval($subtotal ?? 0);
$total           = floatval($total ?? ($subtotal + $deliveryFee));

if (isset($_SESSION['old_operator'])) {
    unset($_SESSION['old_operator']);
}
if (isset($_SESSION['old_delivery_address'])) {
    unset($_SESSION['old_delivery_address']);
}
if (isset($_SESSION['old_phone_number'])) {
    unset($_SESSION['old_phone_number']);
}

$operators = [
    'Moov Money'   => ['color' => '#0066CC', 'bg' => '#E6F0FB', 'logo' => 'M'],
    'MTN mobile'   => ['color' => '#F5A623', 'bg' => '#FEF3DC', 'logo' => 'M'],
    'Celtiis Cash' => ['color' => '#E01B24', 'bg' => '#FDEAEA', 'logo' => 'C'],
];
$operatorMeta   = $operators[$operator] ?? ['color' => '#16a34a', 'bg' => '#E5F3E9', 'logo' => 'M'];
$isHomeDelivery = $deliveryType === 'home';
?>

<div class="fm-pay-wrap pb-16">
    <div class="fm-pay-header">
        <a href="index.php?action=checkout" class="fm-back-link">← Retour</a>
        <div>
            <h1 class="fm-page-title">Paiement Mobile Money</h1>
            <p class="fm-page-sub">Choisissez votre livraison et confirmez votre paiement mobile.</p>
        </div>
    </div>

    <div class="fm-pay-grid">
        <main>
            <?php if (!empty($_SESSION['error'])) : ?>
                <div class="fm-field-card" style="border-color:#f87171; background:#fef2f2; color:#b91c1c;">
                    <p><strong>Erreur :</strong> <?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <div class="fm-field-card">
                <h2 class="section-title">Résumé de la commande</h2>
                <div class="fm-recap-rows">
                    <div class="fm-recap-row">
                        <span class="fm-recap-label">Sous-total</span>
                        <span class="fm-recap-val"><?= number_format($subtotal, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <div class="fm-recap-row">
                        <span class="fm-recap-label">Livraison</span>
                        <span class="fm-recap-val"><?= $deliveryFee > 0 ? number_format($deliveryFee, 0, '', ' ') . ' FCFA' : '<span class="fm-free">Gratuit</span>' ?></span>
                    </div>
                    <div class="fm-recap-row fm-recap-total">
                        <span>Total à payer</span>
                        <span><?= number_format($total, 0, '', ' ') ?> FCFA</span>
                    </div>
                </div>
            </div>

            <form id="mobilePayForm" action="index.php?action=checkout/complete" method="post" class="fm-field-card">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                <input type="hidden" name="delivery_type" value="<?= htmlspecialchars($deliveryType) ?>">
                <input type="hidden" name="delivery_fee" value="<?= $deliveryFee ?>">
                <input type="hidden" name="delivery_latitude" value="<?= htmlspecialchars($deliveryLatitude ?? '') ?>">
                <input type="hidden" name="delivery_longitude" value="<?= htmlspecialchars($deliveryLongitude ?? '') ?>">
                <input type="hidden" name="operator" id="operatorInput" value="<?= htmlspecialchars($operator) ?>">

                <h2 class="section-title">Mode de livraison</h2>
                <?php if ($isHomeDelivery) : ?>
                    <p class="fm-field-hint">Adresse de livraison sélectionnée pour cette commande.</p>
                    <label class="fm-field-label" for="deliveryAddress">Adresse de livraison</label>
                    <input id="deliveryAddress" name="delivery_address" type="text" class="fm-input" value="<?= htmlspecialchars($deliveryAddress) ?>" placeholder="Ex : Cotonou, Akossavié" readonly>
                    <?php if ($deliveryAddress === '') : ?>
                        <p class="fm-field-hint" style="margin-top:0.75rem;">Aucune adresse fournie. Retournez à l'étape précédente pour choisir votre lieu de livraison.</p>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="fm-delivery-card fm-delivery-selected" style="border-color:#16a34a;background:#f0fdf4;">
                        <div class="fm-delivery-icon">🏪</div>
                        <div class="fm-delivery-info">
                            <p class="fm-delivery-name">Retrait en boutique</p>
                            <p class="fm-delivery-desc">Récupérez votre commande à Hévié Akossavié.</p>
                        </div>
                    </div>
                    <p class="fm-field-hint" style="margin-top:0.85rem;">Retrait en boutique choisi : aucun frais de livraison.</p>
                <?php endif; ?>

                <h2 class="section-title" style="margin-top:1.5rem;">Opérateur Mobile Money</h2>
                <div class="fm-operator-pills">
                    <?php foreach ($operators as $name => $data) : ?>
                        <button type="button" class="fm-op-pill <?= $name === $operator ? 'fm-op-active' : '' ?>" data-op="<?= htmlspecialchars($name) ?>" style="--op-color:<?= $data['color'] ?>; --op-bg:<?= $data['bg'] ?>;">
                            <span class="fm-op-logo" style="background:<?= $data['color'] ?>"><?= htmlspecialchars($data['logo']) ?></span>
                            <?= htmlspecialchars($name) ?>
                            <span class="fm-op-check">✓</span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <label class="fm-field-label" for="phoneNumber" style="margin-top:1rem;">Numéro Mobile Money</label>
                <p class="fm-field-hint" style="margin-top:0.5rem;">Format : +229 01 XX XX XX XX</p>
                <div class="fm-phone-wrap">
                    <input id="phoneNumber" name="phone_number" type="tel" class="fm-input fm-input-phone" placeholder="+229 01 XX XX XX XX" pattern="^\+229(?:\s?\d){10}$" title="Format attendu : +229 01 XX XX XX XX" value="<?= htmlspecialchars(formatPhoneDisplay($phoneNumber)) ?>" required>
                </div>

                <button id="payBtn" type="submit" class="btn-primary" style="margin-top:1.5rem;width:100%;">Payer <?= number_format($total, 0, '', ' ') ?> FCFA</button>
            </form>
        </main>

        <aside class="fm-co-right">
            <div class="fm-recap-card section-card">
                <h2 class="section-title fm-recap-title">Récapitulatif</h2>
                <div class="fm-recap-rows">
                    <div class="fm-recap-row"><span>Sous-total</span><span><?= number_format($subtotal, 0, '', ' ') ?> FCFA</span></div>
                    <div class="fm-recap-row"><span>Livraison</span><span><?= $deliveryFee > 0 ? number_format($deliveryFee, 0, '', ' ') . ' FCFA' : 'Gratuit' ?></span></div>
                    <div class="fm-recap-row fm-recap-total"><span>Total</span><span><?= number_format($total, 0, '', ' ') ?> FCFA</span></div>
                </div>
                <div class="fm-recap-op" style="margin-top:1rem;">
                    <span class="fm-recap-op-icon" id="recapOpIcon" style="background:<?= $operatorMeta['color'] ?>;"><?= htmlspecialchars($operatorMeta['logo']) ?></span>
                    <div>
                        <p class="fm-recap-op-name" id="recapOpName"><?= htmlspecialchars($operator) ?></p>
                        <p class="fm-recap-op-sub">Paiement sécurisé</p>
                    </div>
                    <span class="fm-secure-icon">🔒</span>
                </div>
                <ul class="fm-guarantees" style="margin-top:1rem;">
                    <li>✅ Paiement sécurisé</li>
                    <li>✅ Confirmation par SMS</li>
                    <li>✅ Facture disponible</li>
                    <li>✅ Suivi de la commande</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<script>
(function () {
    var operatorButtons = document.querySelectorAll('.fm-op-pill');
    operatorButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            operatorButtons.forEach(function (btn) { btn.classList.remove('fm-op-active'); });
            button.classList.add('fm-op-active');
            var selected = button.dataset.op;
            var operatorInput = document.getElementById('operatorInput');
            if (operatorInput) operatorInput.value = selected;
            var recapName = document.getElementById('recapOpName');
            if (recapName) recapName.textContent = selected;
        });
    });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
