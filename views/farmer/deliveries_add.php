<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container mx-auto p-6 max-w-2xl">
    <div class="mb-6">
        <a href="index.php?action=farmer/deliveries" class="text-blue-600 hover:text-blue-800 font-semibold">&larr; Retour</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold mb-6">Ajouter un livreur</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="index.php?action=farmer/deliveries/add" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet</label>
                <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Nom du livreur" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="email@example.com" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                <input type="tel" name="phone" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="+225 XX XX XX XX">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                <textarea name="address" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Adresse du livreur" rows="3"></textarea>
            </div>

            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg text-sm">
                <p><strong>Note :</strong> Le mot de passe temporaire est "delivery123". Le livreur pourra le modifier après sa première connexion.</p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 font-semibold">Ajouter le livreur</button>
                <a href="index.php?action=farmer/deliveries" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 font-semibold text-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
