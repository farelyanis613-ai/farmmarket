<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container mx-auto p-6 max-w-2xl">
    <div class="mb-6">
        <a href="index.php?action=farmer/deliveries" class="text-blue-600 hover:text-blue-800 font-semibold">&larr; Retour</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold mb-6">Modifier le livreur</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($deliverer): ?>
            <form action="index.php?action=farmer/deliveries/edit" method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?= $deliverer['id'] ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet</label>
                    <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Nom du livreur" value="<?= htmlspecialchars($deliverer['name']) ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="email@example.com" value="<?= htmlspecialchars($deliverer['email']) ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                    <input type="tel" name="phone" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="+225 XX XX XX XX" value="<?= htmlspecialchars($deliverer['phone'] ?? '') ?>">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                    <textarea name="address" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Adresse du livreur" rows="3"><?= htmlspecialchars($deliverer['address'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 font-semibold">Enregistrer les modifications</button>
                    <a href="index.php?action=farmer/deliveries" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 font-semibold text-center">Annuler</a>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <p>Livreur non trouvé.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
