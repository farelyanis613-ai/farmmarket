<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="space-y-6 pb-10">
    <div class="page-card p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Détails de la commande <span class="text-amber-700">#CMD-<?= htmlspecialchars($order['id']) ?></span></h1>
                <p class="text-sm text-slate-600 mt-1">Passée le <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
            </div>
            <div class="text-left lg:text-right">
                <div class="text-2xl font-bold text-emerald-700"><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</div>
                <div class="mt-2"><a href="index.php?action=order/invoice&id=<?= $order['id'] ?>" class="btn-primary">Télécharger la facture (PDF)</a></div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-slate-50 p-4 rounded">
                <h3 class="text-sm font-semibold text-slate-700">Informations client</h3>
                <p class="text-sm mt-2">Nom : <strong><?= htmlspecialchars($order['customer_name'] ?? $_SESSION['user']['name']) ?></strong></p>
                <p class="text-sm">Email : <strong><?= htmlspecialchars($order['customer_email'] ?? $_SESSION['user']['email']) ?></strong></p>
                <p class="text-sm">Téléphone : <strong><?= htmlspecialchars($order['phone'] ?? $_SESSION['user']['phone'] ?? '') ?></strong></p>
                <p class="text-sm">Adresse : <strong><?= nl2br(htmlspecialchars($order['address'] ?? 'N/A')) ?></strong></p>
            </div>
            <div class="bg-slate-50 p-4 rounded">
                <h3 class="text-sm font-semibold text-slate-700">Livraison</h3>
                <p class="text-sm mt-2">Type : <strong><?= isset($order['delivery_type']) && $order['delivery_type'] === 'home' ? 'À domicile' : 'Retrait' ?></strong></p>
                <p class="text-sm">Frais : <strong><?= number_format($order['delivery_fee'] ?? 0, 0, '', ' ') ?> FCFA</strong></p>
                <p class="text-sm">Statut : <strong><?= htmlspecialchars(formatStatusLabel($order['status'])) ?></strong></p>
                <?php if (!isDeliveredStatus($order['status']) && !isFailedStatus($order['status'])): ?>
                    <div class="mt-4">
                        <form action="index.php?action=order/mark-delivered&id=<?= $order['id'] ?>" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-white font-semibold hover:bg-emerald-700">Marquer comme livrée</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold">Articles</h3>
            <div class="mt-3 space-y-3">
                <?php foreach ($orderItems as $item): ?>
                    <div class="flex items-center justify-between p-3 rounded bg-white border border-slate-100">
                        <div>
                            <div class="font-medium"><?= htmlspecialchars($item['product_name']) ?></div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm">Qté: <strong><?= intval($item['quantity']) ?></strong></div>
                            <div class="text-sm">P.U.: <strong><?= number_format($item['unit_price'], 0, '', ' ') ?> FCFA</strong></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
