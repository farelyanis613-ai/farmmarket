<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6 pb-10">
    <a href="index.php?action=delivery/dashboard" class="text-amber-900 hover:underline mb-4 inline-block">← Retour au tableau de bord</a>

    <div class="bg-white p-6 md:p-10 rounded-3xl shadow-sm border border-amber-200 max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-3 text-amber-950">Pourquoi la commande a échoué ?</h1>
        <p class="text-amber-700 mb-6">Indiquez la raison du problème rencontré pour cette livraison.</p>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                <ul class="space-y-2 text-sm">
                    <?php foreach ($errors as $error): ?>
                        <li>• <?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php?action=delivery/failure-reason&order_id=<?= intval($_GET['order_id'] ?? 0) ?>" method="post" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-amber-900 mb-2">Raison de l’échec</label>
                <textarea name="reason" rows="6" class="w-full rounded-3xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900" placeholder="Expliquez brièvement ce qui s'est passé"><?= htmlspecialchars($reason) ?></textarea>
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-amber-900 px-6 py-3 text-sm font-semibold text-white hover:bg-amber-800 transition">
                Revenir au dashboard
            </button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>