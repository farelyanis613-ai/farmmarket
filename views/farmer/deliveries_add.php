<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
    $photoFolder = __DIR__ . '/../../public/images/livreurt';
    $photoFiles = [];
    if (is_dir($photoFolder)) {
        foreach (scandir($photoFolder) as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $photoFiles[] = 'livreurt/' . $file;
            }
        }
    }
    $currentPhotoUrl = htmlspecialchars($_POST['photo_url'] ?? '');
?>

<div class="container mx-auto p-6 max-w-2xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="index.php?action=farmer/deliveries" class="text-blue-600 hover:text-blue-800 font-semibold">&larr; Retour</a>
        <a href="index.php?action=farmer/deliveries" class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
            Voir la liste des livreurs
        </a>
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet</label>
                <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Nom du livreur" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                <input type="tel" name="phone" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="+229 01 XX XX XX XX" pattern="^\+229(?:\s?\d){10}$" title="Format attendu : +229 01 XX XX XX XX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Photo du livreur</label>
                <input id="photo_url" name="photo_url" type="hidden" value="<?= $currentPhotoUrl ?>">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                    <div id="selected-photo-preview" class="flex-1 rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-700">
                        <?php if (!empty($currentPhotoUrl)) : ?>
                            <span class="block truncate"><?= htmlspecialchars($currentPhotoUrl) ?></span>
                        <?php else : ?>
                            <span class="text-slate-500">Aucune photo sélectionnée</span>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="select-photo-btn" class="inline-flex justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Choisir photo</button>
                </div>
                <p class="text-sm text-slate-500 mt-2">Sélectionnez une image depuis <code>public/images/livreurt</code>.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                <textarea name="address" class="w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="Adresse du livreur" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg text-sm">
                <p><strong>Note :</strong> Le mot de passe temporaire est "delivery123". Le livreur pourra le modifier après sa première connexion.</p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 font-semibold">Appliquer</button>
                <a href="index.php?action=farmer/deliveries" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 font-semibold text-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

<div id="photo-picker-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-3xl rounded-3xl bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Choisir une photo de livreur</h2>
                <p class="text-sm text-slate-500">Sélectionnez une image depuis le dossier <code>public/images/livreurt</code>.</p>
            </div>
            <button type="button" id="close-photo-picker" class="rounded-full bg-slate-100 p-2 text-slate-700 hover:bg-slate-200">✕</button>
        </div>
        <?php if (empty($photoFiles)): ?>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Aucune image trouvée dans le dossier <code>public/images/livreurt</code>.</div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <?php foreach ($photoFiles as $photo): ?>
                    <button type="button" class="photo-option group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition hover:border-emerald-500" data-photo="<?= htmlspecialchars($photo) ?>">
                        <img src="public/images/<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars(basename($photo)) ?>" class="h-32 w-full object-cover transition duration-200 group-hover:scale-105">
                        <div class="px-3 py-2 text-sm text-slate-700 text-center truncate"><?= htmlspecialchars(basename($photo)) ?></div>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var photoInput = document.getElementById('photo_url');
        var preview = document.getElementById('selected-photo-preview');
        var openBtn = document.getElementById('select-photo-btn');
        var modal = document.getElementById('photo-picker-modal');
        var closeBtn = document.getElementById('close-photo-picker');

        if (openBtn && modal) {
            openBtn.addEventListener('click', function () {
                modal.classList.remove('hidden');
            });
        }
        if (closeBtn && modal) {
            closeBtn.addEventListener('click', function () {
                modal.classList.add('hidden');
            });
        }
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        document.querySelectorAll('.photo-option').forEach(function (button) {
            button.addEventListener('click', function () {
                var photo = this.dataset.photo;
                if (photoInput) {
                    photoInput.value = window.location.origin + '/public/images/' + photo;
                }
                if (preview) {
                    preview.innerHTML = '<span class="block truncate">' + photo + '</span>';
                }
                if (modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    });
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
