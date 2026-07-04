<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6 pb-10">
    <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-lg overflow-hidden">
        <div class="px-6 py-8 md:px-10">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Ajouter un livreur</h1>
            <p class="text-slate-600 mb-6">Créez un compte livreur pour l'assignation des commandes et le suivi des livraisons.</p>

            <?php if (!empty($_SESSION['success'])) : ?>
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700 font-semibold">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)) : ?>
                <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-700">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="index.php?action=farmer/add-delivery" method="post" class="space-y-5 account-form">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nom du livreur</label>
                    <input id="name" name="name" type="text" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input id="email" name="email" type="email" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Mot de passe</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="confirm" class="block text-sm font-medium text-slate-700 mb-2">Confirmez le mot de passe</label>
                        <input id="confirm" name="confirm" type="password" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="index.php?action=farmer/dashboard" class="inline-flex justify-center rounded-3xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Retour au dashboard</a>
                    <button type="submit" class="inline-flex justify-center rounded-3xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Créer le livreur</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>