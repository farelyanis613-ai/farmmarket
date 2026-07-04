<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Modifier la catégorie</h1>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars(implode('<br>', $errors)) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?action=farmer/edit-category&id=<?= intval($category['id'] ?? 0) ?>" method="post" class="max-w-md">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <label class="block mb-2 text-sm font-medium">Nom de la catégorie</label>
        <input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" class="w-full border rounded px-3 py-2 mb-4" required>
        <div>
            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Mettre à jour</button>
            <a href="index.php?action=farmer/categories" class="ml-3 text-sm text-slate-600">Annuler</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
