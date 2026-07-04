<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6 pb-10">
    <div class="mb-8">
        <h1 class="text-4xl font-bold">Détails de la commande #<?= htmlspecialchars($order['id']) ?></h1>
        <p class="text-slate-600 mt-2">Consultez les informations de la commande, les produits et les informations livraison.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs uppercase text-slate-500 font-semibold">Statut</p>
                    <p class="mt-2 text-lg font-semibold text-emerald-700"><?= htmlspecialchars(formatStatusLabel($order['status'])) ?></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs uppercase text-slate-500 font-semibold">Total</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900"><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs uppercase text-slate-500 font-semibold">Créée le</p>
                    <p class="mt-2 text-sm text-slate-700"><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                    <h2 class="font-semibold text-slate-900 mb-3">Produits</h2>
                    <?php if (!empty($order['items'])): ?>
                        <div class="space-y-3">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="flex items-center justify-between rounded-2xl bg-white p-4 border border-slate-200">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($item['product_name']) ?></p>
                                        <p class="text-sm text-slate-500">Quantité : <?= intval($item['quantity']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-slate-500">Prix unitaire</p>
                                        <p class="font-semibold text-slate-900"><?= number_format($item['unit_price'], 0, '', ' ') ?> FCFA</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-slate-500 italic">Aucun produit enregistré pour cette commande.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-900 mb-3">Informations client</h2>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Nom :</span> <?= htmlspecialchars($order['customer_name'] ?? '') ?></p>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Email :</span> <?= htmlspecialchars($order['customer_email'] ?? '') ?></p>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Téléphone :</span> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Adresse :</span><br><?= nl2br(htmlspecialchars($order['customer_address'] ?? $order['address'] ?? 'N/A')) ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-900 mb-3">Livraison</h2>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Type :</span> <?= htmlspecialchars($order['delivery_type'] === 'home' ? 'Domicile' : 'Retrait') ?></p>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Frais :</span> <?= number_format($order['delivery_fee'] ?? 0, 0, '', ' ') ?> FCFA</p>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Livreur :</span> <?= htmlspecialchars($order['delivery_person_name'] ?? 'Non assigné') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <div class="space-y-4">
                <a href="index.php?action=farmer/orders" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">← Retour aux commandes</a>
                <a href="index.php?action=order/invoice&id=<?= $order['id'] ?>" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition">Télécharger la facture (PDF)</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
