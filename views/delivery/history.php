<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6 pb-10">
    <a href="index.php?action=delivery/dashboard" class="text-amber-900 hover:underline mb-4 inline-block">← Retour au tableau de bord</a>

    <div class="space-y-8">
        <div class="bg-amber-50 p-6 rounded-3xl shadow-sm border border-amber-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-amber-950">Historique des livraisons</h1>
                    <p class="text-amber-700 mt-2">Consultez les livraisons acceptées, livrées et échouées.</p>
                </div>
                <div class="flex gap-3">
                    <a href="index.php?action=delivery/dashboard" class="rounded-full bg-amber-900 px-5 py-3 text-white hover:bg-amber-800 transition">Tableau de bord</a>
                </div>
            </div>

            <div class="grid gap-6">
                <section class="rounded-3xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="text-xl font-bold mb-3 text-amber-950">Acceptées</h2>
                    <?php if (empty($accepted)): ?>
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-amber-700">Aucune livraison acceptée pour le moment.</div>
                    <?php else: ?>
                        <div class="grid gap-4">
                            <?php foreach ($accepted as $order): ?>
                                <div class="rounded-3xl bg-white p-5 shadow-sm border border-amber-200">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div>
                                            <h3 class="font-semibold">Commande #<?= $order['id'] ?></h3>
                                            <p class="text-sm text-amber-700">Client: <?= htmlspecialchars($order['customer_name']) ?></p>
                                            <p class="text-sm text-amber-700">Total: <?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</p>
                                        </div>
                                        <a href="index.php?action=delivery/order-detail&order_id=<?= $order['id'] ?>" class="rounded-full bg-amber-900 px-5 py-2 text-sm text-white hover:bg-amber-800 transition">Voir détails</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    <?php endif; ?>
                </section>

                <section class="rounded-3xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="text-xl font-bold mb-3 text-amber-950">Livrées</h2>
                    <?php if (empty($completed)): ?>
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-amber-700">Aucune livraison livrée pour le moment.</div>
                    <?php else: ?>
                        <div class="grid gap-4">
                            <?php foreach ($completed as $order): ?>
                                <div class="rounded-3xl bg-white p-5 shadow-sm border border-amber-200">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div>
                                            <h3 class="font-semibold">Commande #<?= $order['id'] ?></h3>
                                            <p class="text-sm text-amber-700">Client: <?= htmlspecialchars($order['customer_name']) ?></p>
                                            <p class="text-sm text-amber-700">Total: <?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</p>
                                        </div>
                                        <a href="index.php?action=delivery/order-detail&order_id=<?= $order['id'] ?>" class="rounded-full bg-amber-900 px-5 py-2 text-sm text-white hover:bg-amber-800 transition">Voir détails</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    <?php endif; ?>
                </section>

                <section class="rounded-3xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="text-xl font-bold mb-3 text-amber-950">Échouées</h2>
                    <?php if (empty($failed)): ?>
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-amber-700">Aucune livraison échouée pour le moment.</div>
                    <?php else: ?>
                        <div class="grid gap-4">
                            <?php foreach ($failed as $order): ?>
                                <div class="rounded-3xl bg-white p-5 shadow-sm border border-amber-200">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div>
                                            <h3 class="font-semibold">Commande #<?= $order['id'] ?></h3>
                                            <p class="text-sm text-amber-700">Client: <?= htmlspecialchars($order['customer_name']) ?></p>
                                            <p class="text-sm text-amber-700">Total: <?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</p>
                                            <?php if (!empty($order['failed_reason'])): ?>
                                                <p class="text-sm text-red-700 mt-2">Raison: <?= htmlspecialchars($order['failed_reason']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <a href="index.php?action=delivery/order-detail&order_id=<?= $order['id'] ?>" class="rounded-full bg-amber-900 px-5 py-2 text-sm text-white hover:bg-amber-800 transition">Voir détails</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>