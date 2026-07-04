</main>
<?php
$isFarmer = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'farmer';
$isDelivery = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'delivery';
$currentAction = $_GET['action'] ?? '';
$hideFooter = $isFarmer || $isDelivery || $currentAction === 'profile' || str_starts_with($currentAction, 'farmer/') || str_starts_with($currentAction, 'delivery/');
$farmerEmail = 'contact@farmmarket.test';
$farmerPhone = '+237650123456';
?>
<?php if (!$hideFooter):
    try {
        $umPath = __DIR__ . '/../../models/UserModel.php';
        if (file_exists($umPath)) {
            require_once $umPath;
            if (class_exists('UserModel')) {
                $userModel = new UserModel();
                $farmers = $userModel->findByRole('farmer') ?? [];
                if (!empty($farmers)) {
                    $f = $farmers[0];
                    $farmerEmail = !empty($f['email']) && filter_var($f['email'], FILTER_VALIDATE_EMAIL) ? $f['email'] : $farmerEmail;
                    $farmerPhone = !empty($f['phone']) ? preg_replace('/\s+/', '', $f['phone']) : $farmerPhone;
                }
            }
        }
    } catch (Throwable $e) {
        // ignore and use defaults
    }
?>
<footer class="site-footer text-slate-200 mt-auto">
    <div class="mx-auto max-w-6xl px-4 py-12 md:px-6">
        <div class="grid gap-10 md:grid-cols-3">
            <div>
                <h3 class="text-lg font-semibold text-white mb-3">Farmmarket</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Farmmarket connecte des producteurs locaux, des livreurs et des clients pour une livraison rapide et transparente de produits frais.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-3">Laissez-nous un message</h3>
                <p class="text-slate-400 text-sm mb-3">Vous avez une question sur une commande, un produit ou une livraison ? Envoyez-nous un message et nous vous répondrons rapidement.</p>
                <a href="mailto:<?= htmlspecialchars($farmerEmail) ?>" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors"><?= htmlspecialchars($farmerEmail) ?></a>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-3">Contact rapide</h3>
                <p class="text-slate-400 text-sm mb-3">Appelez le fermier pour tout renseignement sur la production ou la disponibilité des produits.</p>
                <a href="tel:<?= htmlspecialchars($farmerPhone) ?>" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors"><?= htmlspecialchars($farmerPhone) ?></a>
            </div>
        </div>
        <div class="mt-10 border-t border-slate-800 pt-6 text-xs text-slate-500">&copy; 2026 Farmmarket. Tous droits réservés.</div>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
