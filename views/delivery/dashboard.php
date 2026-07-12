<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Inline styles moved to public/css/style.css -->
<!-- views/delivery/dashboard.php: original <style> block consolidated into public/css/style.css -->

    <!-- ── Stats avec progress rings ── -->
    <?php
        $total = count($accepted) + count($completed) + count($failed);
        $total = $total ?: 1; // éviter division par zéro
        $pctAccepted  = round(count($accepted)  / $total * 100);
        $pctCompleted = round(count($completed) / $total * 100);
        $pctFailed    = round(count($failed)    / $total * 100);

        function ringOffset(int $pct, int $r = 30): float {
            return 2 * M_PI * $r * (1 - $pct / 100);
        }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        <!-- Acceptées -->
        <div class="stat-card">
            <svg width="72" height="72" viewBox="0 0 72 72" class="flex-shrink-0" style="transform:rotate(-90deg)">
                <circle class="ring-track" cx="36" cy="36" r="30" fill="none" stroke-width="6"/>
                <circle class="ring-fill" cx="36" cy="36" r="30" fill="none" stroke-width="6"
                    stroke-dasharray="<?= 2*M_PI*30 ?>"
                    stroke-dashoffset="<?= ringOffset($pctAccepted) ?>"
                    data-offset="<?= ringOffset($pctAccepted) ?>"/>
            </svg>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Acceptées</p>
                <p class="text-4xl font-black text-amber-950 tabular-nums"><?= count($accepted) ?></p>
                <p class="text-xs text-amber-500 mt-0.5"><?= $pctAccepted ?>% du total</p>
            </div>
        </div>

        <!-- Livrées -->
        <div class="stat-card">
            <svg width="72" height="72" viewBox="0 0 72 72" class="flex-shrink-0" style="transform:rotate(-90deg)">
                <circle class="ring-track" cx="36" cy="36" r="30" fill="none" stroke-width="6"/>
                <circle class="ring-fill ring-fill-green" cx="36" cy="36" r="30" fill="none" stroke-width="6"
                    stroke-dasharray="<?= 2*M_PI*30 ?>"
                    stroke-dashoffset="<?= ringOffset($pctCompleted) ?>"/>
            </svg>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-green-700">Livrées</p>
                <p class="text-4xl font-black text-green-900 tabular-nums"><?= count($completed) ?></p>
                <p class="text-xs text-green-500 mt-0.5"><?= $pctCompleted ?>% du total</p>
            </div>
        </div>

        <!-- Échouées -->
        <div class="stat-card">
            <svg width="72" height="72" viewBox="0 0 72 72" class="flex-shrink-0" style="transform:rotate(-90deg)">
                <circle class="ring-track" cx="36" cy="36" r="30" fill="none" stroke-width="6"/>
                <circle class="ring-fill ring-fill-red" cx="36" cy="36" r="30" fill="none" stroke-width="6"
                    stroke-dasharray="<?= 2*M_PI*30 ?>"
                    stroke-dashoffset="<?= ringOffset($pctFailed) ?>"/>
            </svg>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-red-600">Échouées</p>
                <p class="text-4xl font-black text-red-900 tabular-nums"><?= count($failed) ?></p>
                <p class="text-xs text-red-400 mt-0.5"><?= $pctFailed ?>% du total</p>
            </div>
        </div>
    </div>

    <!-- ── Sections commandes ── -->
    <div class="flex flex-col gap-6">

        <?php
        $sections = [
            [
                'title'   => 'Commandes acceptées',
                'sub'     => 'En attente de livraison.',
                'orders'  => $accepted,
                'pill'    => 'pill-transit',
                'label'   => 'Acceptée',
                'empty'   => 'Aucune commande acceptée pour le moment.',
            ],
            [
                'title'   => 'Commandes livrées',
                'sub'     => 'Livraisons complétées avec succès.',
                'orders'  => $completed,
                'pill'    => 'pill-done',
                'label'   => 'Livrée',
                'empty'   => 'Aucune commande livrée pour le moment.',
            ],
            [
                'title'   => 'Commandes échouées',
                'sub'     => 'Nécessitent un suivi ou un motif.',
                'orders'  => $failed,
                'pill'    => 'pill-failed',
                'label'   => 'Échouée',
                'empty'   => 'Aucune commande échouée.',
            ],
        ];
        foreach ($sections as $s):
        ?>
        <section class="bg-white border border-amber-100 rounded-2xl p-6 shadow-sm">
            <div class="section-header">
                <div>
                    <h2 class="text-lg font-bold text-amber-950"><?= $s['title'] ?></h2>
                    <p class="text-xs text-amber-600 mt-0.5"><?= $s['sub'] ?></p>
                </div>
                <span class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-3 py-1">
                    <?= count($s['orders']) ?> commande<?= count($s['orders']) > 1 ? 's' : '' ?>
                </span>
            </div>

            <?php if (empty($s['orders'])): ?>
                <div class="empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.5">
                        <path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0-1.5 6H5.5L4 13m16 0H4"/>
                    </svg>
                    <p class="text-sm font-medium"><?= $s['empty'] ?></p>
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-3">
                    <?php foreach ($s['orders'] as $order):
                        $initials = strtoupper(substr($order['customer_name'] ?? 'C', 0, 1));
                    ?>
                    <div class="order-card">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="avatar"><?= $initials ?></div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-amber-950 text-sm">#<?= $order['id'] ?></span>
                                    <span class="pill <?= $s['pill'] ?>"><?= $s['label'] ?></span>
                                </div>
                                <p class="text-xs text-amber-700 mt-0.5 truncate">
                                    <?= htmlspecialchars($order['customer_name']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 flex-shrink-0">
                            <span class="hidden sm:block text-sm font-bold text-amber-900 tabular-nums whitespace-nowrap">
                                <?= number_format($order['total_price'], 0, '', ' ') ?> <span class="font-normal text-amber-500 text-xs">FCFA</span>
                            </span>
                            <a href="index.php?action=delivery/order-detail&order_id=<?= $order['id'] ?>" class="btn-ghost">
                                Voir
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php endforeach; ?>

    </div>
</div>

<script>
// Top progress bar
window.addEventListener('load', () => {
    const bar = document.getElementById('topbar');
    bar.style.width = '100%';
    setTimeout(() => bar.style.opacity = '0', 900);
});
</script>

<script type="application/json" id="deliveryAssignmentsData">
    <?= json_encode(['accepted' => $accepted, 'completed' => $completed, 'failed' => $failed]) ?>
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let data = {};
    try { data = JSON.parse(document.getElementById('deliveryAssignmentsData').textContent); } catch (e) { }
    const computeLocalHash = () => {
        try {
            const combined = [].concat(data.accepted || [], data.completed || [], data.failed || []).map(o => ({ id: o.id, status: o.status, delivery_person_id: o.delivery_person_id, failed_reason: o.failed_reason, created_at: o.created_at }));
            return md5(JSON.stringify(combined));
        } catch (e) { return ''; }
    };
    const md5 = (str) => { let h = 0; for (let i = 0; i < str.length; i++) { h = ((h << 5) - h) + str.charCodeAt(i); h |= 0; } return h.toString(); };
    let localHash = null;
    setInterval(async () => {
        try {
            const res = await fetch('index.php?action=delivery/assignments-poll', { cache: 'no-store' });
            if (!res.ok) return;
            const json = await res.json();
            if (!json.hash) return;
            if (localHash === null) {
                localHash = json.hash;
                return;
            }
            if (json.hash !== localHash) {
                setTimeout(() => window.location.reload(), 200);
            }
        } catch (e) {}
    }, 8000);
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>