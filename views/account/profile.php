<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6 pb-10 max-w-4xl">
    <div class="rounded-3xl border p-6 shadow-xl mb-6 <?= $user['role'] === 'delivery' ? 'bg-amber-900/95 border-amber-800 shadow-amber-950/40' : 'bg-slate-900/95 border-slate-700 shadow-slate-950/40' ?>">
        <h1 class="text-2xl md:text-3xl font-bold mb-2 <?= $user['role'] === 'delivery' ? 'text-amber-100' : 'text-white' ?>">Mon profil</h1>
        <p class="<?= $user['role'] === 'delivery' ? 'text-amber-200' : 'text-slate-300' ?> text-sm">Mettez à jour vos informations personnelles</p>
    </div>

    <?php if (!empty($errors)) : ?>
        <div class="rounded-lg <?= $user['role'] === 'delivery' ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?> p-4 mb-6">
            <ul class="space-y-1 text-sm">
                <?php foreach ($errors as $error) : ?>
                    <li>• <?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success) : ?>
        <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm mb-6">
            ✓ Profil mis à jour avec succès!
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6">
        <!-- Formulaire de mise à jour du profil -->
        <div class="rounded-lg bg-white p-4 md:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-6">Mettre à jour mes informations</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nom</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full rounded-lg border border-slate-700 bg-black px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full rounded-lg border border-slate-700 bg-black px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Téléphone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Numéro de téléphone *</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required class="w-full rounded-lg border border-slate-700 bg-black px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="+226 65 00 00 00" />
                    </div>

                    <!-- Statut (read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Statut</label>
                        <input type="text" value="<?php
                            switch ($user['role']) {
                                case 'farmer':
                                    echo 'Éleveur';
                                    break;
                                case 'delivery':
                                    echo 'Livreur';
                                    break;
                                case 'admin':
                                    echo 'Administrateur';
                                    break;
                                default:
                                    echo 'Client';
                                    break;
                            }
                        ?>" disabled class="w-full rounded-lg border border-slate-700 bg-slate-800 text-slate-200 px-4 py-2" />
                    </div>
                </div>

                <!-- Adresse -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Adresse *</label>
                    <textarea name="address" rows="4" required data-editable class="w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre adresse complète"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <!-- Bouton Mettre à jour -->
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-6 py-3 text-white font-semibold hover:bg-emerald-700 transition-colors">
                        ✓ Mettre à jour le profil
                    </button>
                    <a href="index.php?action=<?= $user['role'] === 'farmer' ? 'farmer/dashboard' : ($user['role'] === 'delivery' ? 'delivery/dashboard' : 'home') ?>" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-white font-semibold hover:bg-red-700 transition-colors">
                        Annuler
                    </a>
                </div>

                <p class="text-xs text-slate-500 mt-4">* Champs obligatoires</p>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
