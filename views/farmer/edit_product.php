<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
    $selectedImage = htmlspecialchars($_POST['image'] ?? $product['image'] ?? '');
    $farmerImages = [];
    $farmerDir = __DIR__ . '/../../public/images/farmers';
    if (is_dir($farmerDir)) {
        foreach (scandir($farmerDir) as $file) {
            if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp'])) {
                $farmerImages[] = 'farmers/' . $file;
            }
        }
    }

    // Catégories : utilise celles fournies par le contrôleur si disponibles,
    // sinon une liste par défaut.
    $defaultCategories = [1 => 'Lapins', 2 => 'Poulets', 3 => 'Viande', 4 => 'Oeuf'];
    $categoryOptions = [];
    if (!empty($categories) && is_array($categories)) {
        foreach ($categories as $key => $cat) {
            if (is_array($cat)) {
                $id = $cat['id'] ?? $key;
                $label = $cat['name'] ?? $cat['label'] ?? (string) $id;
            } else {
                $id = $key;
                $label = $cat;
            }
            $categoryOptions[$id] = $label;
        }
    } else {
        $categoryOptions = $defaultCategories;
    }
?>
<div class="mx-auto max-w-2xl rounded-lg bg-white p-8 shadow">
    <h1 class="text-2xl font-semibold mb-6">Modifier le produit</h1>

    <?php if (!empty($errors)) : ?>
        <div class="mb-4 rounded bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="space-y-2">
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="index.php?action=farmer/edit-product&id=<?= $product['id'] ?>" method="post" class="space-y-4 account-form">
        <div>
            <label class="text-sm font-medium text-slate-700">Nom du produit</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required class="mt-2 w-full rounded border border-slate-200 px-4 py-2">
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" rows="4" class="mt-2 w-full rounded border border-slate-200 px-4 py-2"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-slate-700">Prix (FCFA )</label>
                <input type="number" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required class="mt-2 w-full rounded border border-slate-200 px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Stock</label>
                <input type="number" name="stock" min="0" value="<?= $product['stock'] ?>" required class="mt-2 w-full rounded border border-slate-200 px-4 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">Catégorie</label>
            <select name="category_id" class="mt-2 w-full rounded border border-slate-200 px-4 py-2">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($categoryOptions as $id => $label) : ?>
                    <option value="<?= htmlspecialchars((string) $id) ?>" <?= $product['category_id'] == $id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">Image du produit</label>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <button type="button" id="select-image-btn" class="rounded-lg bg-blue-600 border border-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Sélectionner une image
                </button>
                <span id="selected-image-name" class="text-sm text-slate-700"><?= $selectedImage ? basename($selectedImage) : 'Aucune image sélectionnée' ?></span>
            </div>
            <input type="hidden" name="image" id="product-image-input" value="<?= $selectedImage ?>">
            <div id="image-preview-container" class="mt-4 overflow-hidden rounded-lg border border-slate-200 <?= $selectedImage ? '' : 'hidden' ?> w-full max-w-sm">
                <img src="<?= $selectedImage ? 'public/images/' . $selectedImage : '' ?>" id="image-preview" alt="Aperçu image produit" class="h-40 w-full object-cover">
            </div>
        </div>

        <div class="pt-4 flex gap-2">
            <button type="submit" class="w-full rounded bg-emerald-600 px-6 py-2 text-white hover:bg-emerald-700">Mettre à jour</button>
            <a href="index.php?action=farmer/products" class="w-full rounded bg-red-600 px-6 py-2 text-center text-white font-semibold hover:bg-red-700">Annuler</a>
        </div>

        <div id="image-picker-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-3xl rounded-3xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold">Choisir une image</h2>
                        <p class="text-sm text-slate-500">Sélectionnez une image depuis le dossier farmers.</p>
                    </div>
                    <button type="button" id="close-image-picker" class="rounded-full bg-slate-100 p-2 text-slate-700 hover:bg-slate-200">✕</button>
                </div>
                <?php if (empty($farmerImages)): ?>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Aucune image trouvée dans le dossier farmers.</div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($farmerImages as $image): ?>
                            <button type="button" class="product-image-option group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition hover:border-emerald-500" data-image="<?= htmlspecialchars($image) ?>">
                                <img src="public/images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars(basename($image)) ?>" class="h-32 w-full object-cover transition duration-200 group-hover:scale-105">
                                <div class="px-3 py-2 text-sm text-slate-700 text-center"><?= htmlspecialchars(basename($image)) ?></div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var picker = document.getElementById('image-picker-modal');
            var openButton = document.getElementById('select-image-btn');
            var closeButton = document.getElementById('close-image-picker');
            var imageInput = document.getElementById('product-image-input');
            var selectedLabel = document.getElementById('selected-image-name');
            var previewContainer = document.getElementById('image-preview-container');
            var preview = document.getElementById('image-preview');

            if (openButton && picker) {
                openButton.addEventListener('click', function () {
                    picker.classList.remove('hidden');
                });
            }
            if (closeButton && picker) {
                closeButton.addEventListener('click', function () {
                    picker.classList.add('hidden');
                });
            }
            if (picker) {
                picker.addEventListener('click', function (event) {
                    if (event.target === picker) {
                        picker.classList.add('hidden');
                    }
                });
            }

            document.querySelectorAll('.product-image-option').forEach(function (button) {
                button.addEventListener('click', function () {
                    var image = this.dataset.image;
                    imageInput.value = image;
                    selectedLabel.textContent = image.split('/').pop();
                    preview.src = 'public/images/' + image;
                    previewContainer.classList.remove('hidden');
                    picker.classList.add('hidden');
                });
            });
        });
    </script>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
