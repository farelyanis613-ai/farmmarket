<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="space-y-6">
    <div class="rounded-3xl bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold">Tableau de bord admin</h1>
        <p class="mt-2 text-slate-600">Liste des produits disponibles.</p>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700">Produit</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Catégorie</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Prix</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td class="px-4 py-4"><?= htmlspecialchars($product['name']) ?></td>
                            <td class="px-4 py-4"><?= htmlspecialchars($product['category_name']) ?></td>
                            <td class="px-4 py-4 text-emerald-700"><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</td>
                            <td class="px-4 py-4"><?= htmlspecialchars($product['stock']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>