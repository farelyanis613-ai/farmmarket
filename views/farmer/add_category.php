<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Ajouter une catégorie</h1>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars(implode('<br>', $errors)) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?action=farmer/add-category" method="post" class="max-w-md">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <label class="block mb-2 text-sm font-medium text-slate-700">Nom de la catégorie</label>
        <input type="text" name="name" class="w-full bg-black text-white placeholder-gray-400 border border-slate-700 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
        <div>
            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Enregistrer</button>
            <a href="index.php?action=farmer/categories" class="ml-3 text-sm text-slate-600">Annuler</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
