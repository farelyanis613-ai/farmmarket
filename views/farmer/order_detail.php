<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$detailStatus = normalizeStatus($order['status']);

// Palette de statut : couleurs de badge cohérentes avec l'état réel de la commande
$statusStyles = [
    'pending'     => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
    'accepted'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'dot' => 'bg-blue-500'],
    'in_progress' => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'dot' => 'bg-indigo-500'],
    'delivered'   => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
    'failed'      => ['bg' => 'bg-red-50',     'text' => 'text-red-700',    'dot' => 'bg-red-500'],
    'rejected'    => ['bg' => 'bg-red-50',     'text' => 'text-red-700',    'dot' => 'bg-red-500'],
];
$badge = $statusStyles[$detailStatus] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'dot' => 'bg-slate-400'];

// Récap financier
$itemsSubtotal = 0;
if (!empty($order['items'])) {
    foreach ($order['items'] as $it) {
        $itemsSubtotal += (float) $it['unit_price'] * (int) $it['quantity'];
    }
}
$deliveryFee = (float) ($order['delivery_fee'] ?? 0);
?>

<div class="container mx-auto p-6 pb-10">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="index.php?action=farmer/orders" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 mb-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Retour aux commandes
            </a>
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900">Commande #<?= htmlspecialchars($order['id']) ?></h1>
            <p class="text-slate-500 mt-1">Créée le <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
        </div>
        <span id="order-status-label" data-status="<?= htmlspecialchars($detailStatus) ?>" class="inline-flex items-center gap-2 rounded-full <?= $badge['bg'] ?> <?= $badge['text'] ?> px-4 py-2 text-sm font-semibold">
            <span class="w-2 h-2 rounded-full <?= $badge['dot'] ?>"></span>
            <?= htmlspecialchars(formatStatusLabel($order['status'])) ?>
        </span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">

            <!-- Produits -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Produits
                </h2>

                <?php if (!empty($order['items'])): ?>
                    <div class="space-y-3">
                        <?php foreach ($order['items'] as $item): ?>
                            <?php $lineTotal = (float) $item['unit_price'] * (int) $item['quantity']; ?>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <div>
                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p class="text-sm text-slate-500 mt-0.5">
                                        <?= intval($item['quantity']) ?> × <?= number_format($item['unit_price'], 0, '', ' ') ?> FCFA
                                    </p>
                                </div>
                                <p class="font-semibold text-slate-900"><?= number_format($lineTotal, 0, '', ' ') ?> FCFA</p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Récap totaux -->
                    <div class="mt-5 pt-4 border-t border-slate-200 space-y-2">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Sous-total produits</span>
                            <span><?= number_format($itemsSubtotal, 0, '', ' ') ?> FCFA</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Frais de livraison</span>
                            <span><?= number_format($deliveryFee, 0, '', ' ') ?> FCFA</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                            <span>Total</span>
                            <span><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-500 italic">Aucun produit enregistré pour cette commande.</p>
                <?php endif; ?>
            </div>

            <!-- Client / Livraison -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Informations client
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Nom</dt>
                            <dd class="text-slate-800 font-medium"><?= htmlspecialchars($order['customer_name'] ?? '') ?></dd>
                        </div>
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Email</dt>
                            <dd class="text-slate-800 font-medium break-all"><?= htmlspecialchars($order['customer_email'] ?? '') ?></dd>
                        </div>
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Téléphone</dt>
                            <dd class="text-slate-800 font-medium"><?= htmlspecialchars($order['phone'] ?? '') ?></dd>
                        </div>
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Adresse</dt>
                            <dd class="text-slate-800 font-medium"><?= nl2br(htmlspecialchars($order['customer_address'] ?? $order['address'] ?? 'N/A')) ?></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l1-8h11l1 8m-13 0h13m-13 0v5a1 1 0 001 1h1m11-6v6a1 1 0 01-1 1h-1m-8 0a2 2 0 104 0m-4 0h4m4 0a2 2 0 104 0m-4 0h4"/></svg>
                        Livraison
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Type</dt>
                            <dd class="text-slate-800 font-medium"><?= htmlspecialchars($order['delivery_type'] === 'home' ? 'Domicile' : 'Retrait') ?></dd>
                        </div>
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Frais</dt>
                            <dd class="text-slate-800 font-medium"><?= number_format($deliveryFee, 0, '', ' ') ?> FCFA</dd>
                        </div>
                        <div class="flex gap-3">
                            <dt class="text-slate-400 w-24 shrink-0">Livreur</dt>
                            <dd class="text-slate-800 font-medium"><?= htmlspecialchars($order['delivery_person_name'] ?? 'Non assigné') ?></dd>
                        </div>
                    </dl>

                    <?php $failedReason = trim($order['failed_reason'] ?? ''); ?>
                    <?php if ($failedReason !== '') : ?>
                        <div class="mt-5 rounded-3xl border border-rose-200 bg-rose-50 p-4">
                            <p class="text-sm font-semibold text-rose-800 mb-2">Raison de l'échec</p>
                            <p class="text-sm leading-6 text-rose-700"><?= nl2br(htmlspecialchars($failedReason)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-3">
                <a href="index.php?action=order/invoice&id=<?= $order['id'] ?>" class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Télécharger la facture (PDF)
                </a>

                <?php if (!in_array($detailStatus, ['delivered', 'failed', 'rejected'], true)): ?>
                    <form id="order-status-form" action="index.php?action=farmer/update-order-status-api" method="post" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                        <input type="hidden" name="order_id" value="<?= intval($order['id']) ?>">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mettre à jour le statut</label>
                        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="accepted" <?= $detailStatus === 'accepted' ? 'selected' : '' ?>>Accepter la commande</option>
                            <option value="in_progress" <?= $detailStatus === 'in_progress' ? 'selected' : '' ?>>En préparation</option>
                        </select>
                        <button type="submit" class="mt-3 w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed transition">
                            <svg id="order-status-spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                            <span id="order-status-btn-label">Appliquer</span>
                        </button>
                        <div id="order-status-msg" class="mt-3 text-sm hidden"></div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Intercepte le formulaire de détail de commande pour afficher la réponse JSON et mettre à jour le statut
(function () {
    var form = document.getElementById('order-status-form');
    if (!form) return;

    var statusMap = {
        'pending': 'En attente',
        'in_progress': 'En cours',
        'accepted': 'Acceptée',
        'delivered': 'Livré',
        'failed': 'Échouée',
        'rejected': 'Rejetée'
    };

    var statusBadgeClasses = {
        'pending':     ['bg-amber-50', 'text-amber-700', 'bg-amber-500'],
        'accepted':    ['bg-blue-50', 'text-blue-700', 'bg-blue-500'],
        'in_progress': ['bg-indigo-50', 'text-indigo-700', 'bg-indigo-500'],
        'delivered':   ['bg-emerald-50', 'text-emerald-700', 'bg-emerald-500'],
        'failed':      ['bg-red-50', 'text-red-700', 'bg-red-500'],
        'rejected':    ['bg-red-50', 'text-red-700', 'bg-red-500']
    };

    var btn = form.querySelector('button[type="submit"]');
    var spinner = document.getElementById('order-status-spinner');
    var btnLabel = document.getElementById('order-status-btn-label');
    var msgBox = document.getElementById('order-status-msg');

    function setLoading(isLoading) {
        if (btn) btn.disabled = isLoading;
        if (spinner) spinner.classList.toggle('hidden', !isLoading);
        if (btnLabel) btnLabel.textContent = isLoading ? 'Application...' : 'Appliquer';
    }

    function showMessage(text, success) {
        if (!msgBox) return;
        msgBox.textContent = text;
        msgBox.classList.remove('hidden', 'text-emerald-700', 'text-red-700');
        msgBox.classList.add(success ? 'text-emerald-700' : 'text-red-700');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        setLoading(true);

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            // Signale explicitement une requête AJAX : nécessaire pour que le backend
            // renvoie du JSON au lieu d'une redirection HTML classique de formulaire.
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) {
                var contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    // Le backend n'a pas répondu en JSON (souvent une redirection ou une erreur serveur)
                    throw new Error('Réponse inattendue du serveur (HTTP ' + res.status + ').');
                }
                return res.json();
            })
            .then(function (json) {
                var msg = (json && json.message) ? json.message : (json && json.success ? 'Opération réussie.' : 'Erreur lors de la mise à jour.');
                showMessage(msg, !!(json && json.success));

                if (json && json.success && json.status) {
                    var label = statusMap[json.status] || json.status;
                    var st = document.getElementById('order-status-label');
                    if (st) {
                        var dot = st.querySelector('span');
                        var classes = statusBadgeClasses[json.status];
                        st.className = 'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold';
                        if (classes) {
                            st.classList.add(classes[0], classes[1]);
                            if (dot) { dot.className = 'w-2 h-2 rounded-full ' + classes[2]; }
                        }
                        st.lastChild.textContent = ' ' + label;
                        if (!dot) { st.textContent = label; }
                        else {
                            // reconstruit le contenu texte après le point de couleur
                            while (st.childNodes.length > 1) st.removeChild(st.lastChild);
                            st.appendChild(document.createTextNode(label));
                        }
                    }
                    // Si le nouveau statut est final, retire le formulaire de mise à jour
                    if (['delivered', 'failed', 'rejected'].indexOf(json.status) !== -1) {
                        form.remove();
                    }
                }
            })
            .catch(function (err) {
                console.error(err);
                showMessage('Impossible de mettre à jour le statut. Vérifiez votre connexion et réessayez.', false);
            })
            .finally(function () {
                setLoading(false);
            });
    });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>