<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="min-h-screen bg-slate-100 py-10 px-4 flex items-center justify-center">
    <div class="w-full max-w-xl rounded-3xl bg-white/95 p-8 shadow-sm backdrop-blur-sm">
        <h1 class="text-2xl font-semibold">Connexion</h1>
    <?php if (isset($_GET['as']) && $_GET['as'] === 'farmer') : ?>
        <div class="mt-3 inline-block rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">Connexion éleveur</div>
    <?php endif; ?>
    <?php if (!empty($errors)) : ?>
        <div class="mt-4 rounded-3xl bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="space-y-2">
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="index.php?action=login" method="post" class="mt-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <?php if (isset($_GET['as'])) : ?>
            <input type="hidden" name="as" value="<?= htmlspecialchars($_GET['as']) ?>">
        <?php endif; ?>
        <?php if (!empty($next)) : ?>
            <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
        <?php endif; ?>
        <div>
            <label class="text-sm font-medium text-slate-700">Email</label>
            <input type="email" name="email" required class="mt-2 w-full rounded-3xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Mot de passe</label>
            <input type="password" name="password" required class="mt-2 w-full rounded-3xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full rounded-3xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Se connecter</button>
    </form>
    <p class="mt-4 text-sm text-slate-600">Pas encore de compte ? <a href="index.php?action=register<?= !empty($next) ? '&next=' . urlencode($next) : '' ?>" class="text-emerald-600">Inscrivez-vous</a>.</p>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>