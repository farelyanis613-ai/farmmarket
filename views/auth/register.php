<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="mx-auto max-w-xl rounded-3xl bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold">Inscription</h1>
    <?php if (!empty($errors)) : ?>
        <div class="mt-4 rounded-3xl bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="space-y-2">
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="index.php?action=register" method="post" class="mt-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <input type="hidden" name="role" value="client">
        <?php if (!empty($next)) : ?>
            <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
        <?php endif; ?>

        <div>
            <label class="text-sm font-medium text-slate-700">Nom</label>
            <input type="text" name="name" required class="mt-2 w-full rounded-3xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">Email</label>
            <input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Mot de passe</label>
            <input type="password" name="password" required class="mt-2 w-full rounded-3xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Confirmer le mot de passe</label>
            <input type="password" name="confirm" required class="mt-2 w-full rounded-3xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full rounded-3xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Créer mon compte</button>
    </form>
    <p class="mt-4 text-sm text-slate-600">Déjà inscrit ? <a href="index.php?action=login<?= !empty($next) ? '&next=' . urlencode($next) : '' ?>" class="text-emerald-600">Connexion</a>.</p>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>