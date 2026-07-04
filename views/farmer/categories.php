<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Mes catégories</h1>
        <a href="index.php?action=farmer/add-category" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Ajouter une catégorie</a>
    </div>

    <?php if (empty($categories)): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">Aucune catégorie définie.</div>
    <?php else: ?>
        <div class="rounded-lg shadow bg-white overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Nom</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr class="border-b">
                            <td class="px-6 py-3 text-sm"><?= $cat['id'] ?></td>
                            <td class="px-6 py-3 text-sm"><?= htmlspecialchars($cat['name']) ?></td>
                            <td class="px-6 py-3 text-sm">
                                <a href="index.php?action=farmer/edit-category&id=<?= $cat['id'] ?>" class="text-blue-600 hover:underline mr-3">Modifier</a>
                                <form action="index.php?action=farmer/delete-category" method="post" class="inline-block" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <button type="submit" class="text-red-600 hover:underline bg-transparent border-0 p-0 cursor-pointer">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
