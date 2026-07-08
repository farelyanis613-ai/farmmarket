<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
    if (!function_exists('old')) {
        /**
         * Re-affiche une valeur soumise précédemment (après une erreur de validation),
         * échappée pour un affichage HTML sûr.
         */
        function old(string $key, string $default = ''): string {
            return htmlspecialchars((string) ($_POST[$key] ?? $default));
        }
    }

    $selectedImage = old('image');

    // Images disponibles dans le dossier farmers (sélection, pas de téléversement)
    $farmerImages = [];
    $farmerDir = __DIR__ . '/../../public/images/farmers';
    if (is_dir($farmerDir)) {
        foreach (scandir($farmerDir) as $file) {
            $fullPath = $farmerDir . '/' . $file;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (is_file($fullPath) && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $farmerImages[] = 'farmers/' . $file;
            }
        }
    }

    // Catégories : utilise celles fournies par le contrôleur si disponibles,
    // sinon une liste par défaut (à remplacer dès que farmer/categories alimente cette vue).
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

<!-- Inline styles moved to public/css/style.css -->

<div class="mx-auto max-w-2xl p-6 font-body text-slate-800">

    <a href="index.php?action=farmer/products" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-emerald-600 mb-4">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Retour aux produits
    </a>

    <div class="rounded-2xl bg-white p-8 shadow-sm border border-slate-100">
        <h1 class="text-2xl font-display font-bold text-slate-900 mb-6">Ajouter un produit</h1>

        <?php if (!empty($errors)) : ?>
            <div class="mb-6 rounded-xl bg-rose-50 border border-rose-100 p-4 text-sm text-rose-700">
                <ul class="space-y-1.5 list-disc list-inside">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php?action=farmer/add-product" method="post" class="space-y-4 account-form">
            <div>
                <label for="product-name" class="text-sm font-medium text-slate-700">Nom du produit <span class="text-rose-500">*</span></label>
                <input id="product-name" type="text" name="name" value="<?= old('name') ?>" required maxlength="120"
                       class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-emerald-500">
            </div>

            <div>
                <label for="product-description" class="text-sm font-medium text-slate-700">Description</label>
                <textarea id="product-description" name="description" rows="4" maxlength="1000"
                          class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-emerald-500"><?= old('description') ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="product-price" class="text-sm font-medium text-slate-700">Prix (FCFA) <span class="text-rose-500">*</span></label>
                    <input id="product-price" type="number" name="price" step="1" min="0" value="<?= old('price') ?>" required
                           class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-emerald-500">
                    <p class="text-xs text-slate-400 mt-1">Montant en francs CFA, sans centimes</p>
                </div>

                <div>
                    <label for="product-stock" class="text-sm font-medium text-slate-700">Stock <span class="text-rose-500">*</span></label>
                    <input id="product-stock" type="number" name="stock" min="0" value="<?= old('stock') ?>" required
                           class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label for="product-category" class="text-sm font-medium text-slate-700">Catégorie</label>
                <select id="product-category" name="category_id" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-emerald-500">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($categoryOptions as $id => $label) : ?>
                        <option value="<?= htmlspecialchars((string) $id) ?>" <?= old('category_id') === (string) $id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Image du produit</label>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <button type="button" id="select-image-btn" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                        Sélectionner une image
                    </button>
                    <span id="selected-image-name" class="text-sm text-slate-600"><?= $selectedImage ? basename($selectedImage) : 'Aucune image sélectionnée' ?></span>
                </div>
                <noscript><p class="text-xs text-rose-500 mt-2">Activez JavaScript pour sélectionner une image.</p></noscript>
                <input type="hidden" name="image" id="product-image-input" value="<?= $selectedImage ?>">
                <div id="image-preview-container" class="mt-4 overflow-hidden rounded-lg border border-slate-200 <?= $selectedImage ? '' : 'hidden' ?> w-full max-w-sm">
                    <img src="<?= $selectedImage ? 'public/images/' . $selectedImage : '' ?>" id="image-preview" alt="Aperçu de l'image du produit" class="h-40 w-full object-cover">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 rounded-lg bg-emerald-600 px-6 py-2.5 font-medium text-white hover:bg-emerald-700 transition-colors">Créer</button>
                <a href="index.php?action=farmer/products" class="flex-1 rounded-lg border border-slate-200 px-6 py-2.5 text-center font-medium text-slate-600 hover:bg-slate-50 transition-colors">Annuler</a>
            </div>
        </form>
    </div>

    <!-- Sélecteur d'image : en dehors du <form> pour éviter toute interférence avec sa soumission -->
    <div id="image-picker-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-labelledby="image-picker-title">
        <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 id="image-picker-title" class="text-xl font-display font-bold text-slate-900">Choisir une image</h2>
                    <p class="text-sm text-slate-500">Sélectionnez une image depuis le dossier farmers.</p>
                </div>
                <button type="button" id="close-image-picker" aria-label="Fermer" class="rounded-full bg-slate-100 p-2 text-slate-700 hover:bg-slate-200">✕</button>
            </div>
            <?php if (empty($farmerImages)): ?>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Aucune image trouvée dans le dossier farmers.</div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[60vh] overflow-y-auto">
                    <?php foreach ($farmerImages as $image):
                        $imageEscaped = htmlspecialchars($image);
                        $isSelected = $imageEscaped === $selectedImage;
                    ?>
                        <button type="button"
                                class="product-image-option group relative overflow-hidden rounded-xl border-2 <?= $isSelected ? 'border-emerald-500' : 'border-slate-200' ?> bg-slate-50 transition hover:border-emerald-500"
                                data-image="<?= $imageEscaped ?>">
                            <?php if ($isSelected): ?>
                                <span class="absolute top-1.5 right-1.5 z-10 rounded-full bg-emerald-500 text-white p-1" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M20 6L9 17l-5-5"/></svg>
                                </span>
                            <?php endif; ?>
                            <img src="public/images/<?= $imageEscaped ?>" alt="<?= htmlspecialchars(basename($image)) ?>" loading="lazy" decoding="async" class="h-32 w-full object-cover transition duration-200 group-hover:scale-105">
                            <div class="px-3 py-2 text-sm text-slate-700 text-center truncate"><?= htmlspecialchars(basename($image)) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var picker = document.getElementById('image-picker-modal');
        var openButton = document.getElementById('select-image-btn');
        var closeButton = document.getElementById('close-image-picker');
        var imageInput = document.getElementById('product-image-input');
        var selectedLabel = document.getElementById('selected-image-name');
        var previewContainer = document.getElementById('image-preview-container');
        var preview = document.getElementById('image-preview');
        var lastFocusedElement = null;

        function getFocusable() {
            return Array.prototype.slice.call(picker.querySelectorAll('button:not([disabled])'));
        }

        function openModal() {
            lastFocusedElement = document.activeElement;
            picker.classList.remove('hidden');
            picker.classList.add('flex');
            var focusable = getFocusable();
            if (focusable.length) focusable[0].focus();
            document.addEventListener('keydown', handleKeydown);
        }

        function closeModal() {
            picker.classList.add('hidden');
            picker.classList.remove('flex');
            document.removeEventListener('keydown', handleKeydown);
            if (lastFocusedElement) lastFocusedElement.focus();
        }

        function handleKeydown(event) {
            if (event.key === 'Escape') {
                closeModal();
                return;
            }
            if (event.key === 'Tab') {
                var focusable = getFocusable();
                if (!focusable.length) return;
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        }

        if (openButton && picker) {
            openButton.addEventListener('click', openModal);
        }
        if (closeButton && picker) {
            closeButton.addEventListener('click', closeModal);
        }
        if (picker) {
            picker.addEventListener('click', function (event) {
                if (event.target === picker) closeModal();
            });
        }

        document.querySelectorAll('.product-image-option').forEach(function (button) {
            button.addEventListener('click', function () {
                var image = this.dataset.image;
                imageInput.value = image;
                selectedLabel.textContent = image.split('/').pop();
                preview.src = 'public/images/' + image;
                previewContainer.classList.remove('hidden');
                closeModal();
            });
        });

        var form = document.querySelector('.account-form');
        if (form) {
            form.addEventListener('submit', function () {
                var submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    submitBtn.textContent = 'Création...';
                }
            });
        }
    });
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>