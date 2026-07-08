<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="space-y-6 pb-10">
    <div class="page-card p-6">
        <div class="order-detail-header">
            <div>
                <h1 class="page-title">Détails de la commande <span class="text-emerald-700">#<?= htmlspecialchars((string)($orderNumber ?? $order['id'])) ?></span></h1>
                <p class="page-subtitle">Passée le <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
            </div>
            <div class="order-detail-actions">
                <div class="order-detail-total">
                    <div class="text-3xl font-bold text-emerald-700"><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</div>
                    <div class="mt-3"><a href="index.php?action=order/invoice&id=<?= $order['id'] ?>" class="btn-primary">Télécharger la facture (PDF)</a></div>
                </div>
            </div>
        </div>

        <div class="order-detail-status-bar">
            <span class="order-detail-status"><?= htmlspecialchars(formatStatusLabel($order['status'])) ?></span>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <section class="section-card p-5">
                <h2 class="section-title">Informations client</h2>
                <div class="order-detail-info mt-4">
                    <div class="info-row"><span>Nom</span><strong><?= htmlspecialchars($order['customer_name'] ?? $_SESSION['user']['name']) ?></strong></div>
                    <div class="info-row"><span>Email</span><strong><?= htmlspecialchars($order['customer_email'] ?? $_SESSION['user']['email']) ?></strong></div>
                    <div class="info-row"><span>Téléphone</span><strong><?= htmlspecialchars($order['phone'] ?? $_SESSION['user']['phone'] ?? '') ?></strong></div>
                    <div class="info-row"><span>Adresse</span><strong><?= nl2br(htmlspecialchars($order['address'] ?? 'N/A')) ?></strong></div>
                </div>
            </section>

            <section class="section-card p-5">
                <h2 class="section-title">Livraison</h2>
                <div class="order-detail-info mt-4">
                    <div class="info-row"><span>Type</span><strong><?= isset($order['delivery_type']) && $order['delivery_type'] === 'home' ? 'À domicile' : 'Retrait' ?></strong></div>
                    <div class="info-row"><span>Temps de livraison</span><strong>45 minutes</strong></div>
                    <div class="info-row"><span>Frais</span><strong><?= number_format($order['delivery_fee'] ?? 0, 0, '', ' ') ?> FCFA</strong></div>
                    <div class="info-row"><span>Statut</span><strong><?= htmlspecialchars(formatStatusLabel($order['status'])) ?></strong></div>
                </div>

                <?php if (isFailedStatus($order['status']) && !empty($order['failed_reason'])): ?>
                    <div class="mt-4 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800">
                        <span class="font-semibold">Raison de l'échec :</span>
                        <span class="block mt-1"><?= htmlspecialchars($order['failed_reason']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order['delivery_person_name']) || !empty($order['delivery_person_phone'])): ?>
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <h3 class="section-subtitle">Livreur</h3>
                        <div class="order-detail-info mt-3">
                            <div class="info-row"><span>Nom</span><strong><?= htmlspecialchars($order['delivery_person_name'] ?? '—') ?></strong></div>
                            <div class="info-row"><span>Téléphone</span><strong><?php if (!empty($order['delivery_person_phone'])): ?><a href="tel:<?= htmlspecialchars($order['delivery_person_phone']) ?>" class="underline"><?= htmlspecialchars($order['delivery_person_phone']) ?></a><?php else: ?>—<?php endif; ?></strong></div>
                            <div class="info-row"><span>Email</span><strong><?= !empty($order['delivery_person_email']) ? htmlspecialchars($order['delivery_person_email']) : '—' ?></strong></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!isDeliveredStatus($order['status']) && !isFailedStatus($order['status'])): ?>
                    <div class="mt-5">
                        <form action="index.php?action=order/mark-delivered&id=<?= $order['id'] ?>" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                            <button type="submit" class="btn-secondary">Marquer comme livrée</button>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="mt-6 order-detail-items">
            <h2 class="section-title">Articles</h2>
            <div class="mt-4 space-y-3">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item-row">
                        <div>
                            <div class="font-medium text-slate-900"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div class="text-sm text-slate-500 mt-1">Qté: <strong><?= intval($item['quantity']) ?></strong></div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-slate-700">P.U.: <strong><?= number_format($item['unit_price'], 0, '', ' ') ?> FCFA</strong></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
