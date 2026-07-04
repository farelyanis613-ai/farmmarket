<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Inline styles moved to public/css/style.css -->

<div class="page-wrap">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 mb-1">Livreur</p>
            <h1 class="text-3xl font-black text-amber-950">Mes statistiques</h1>
            <p class="text-sm text-amber-600 mt-1">Vue d'ensemble de vos performances de livraison.</p>
        </div>
        <a href="index.php?action=delivery/dashboard"
           class="inline-flex items-center gap-2 self-start px-4 py-2 rounded-xl bg-amber-950 text-white text-sm font-semibold hover:bg-amber-800 transition">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M3 12l9-9 9 9M4 10v10a1 1 0 0 0 1 1h5v-6h4v6h5a1 1 0 0 0 1-1V10"/>
            </svg>
            Tableau de bord
        </a>
    </div>

    <?php if ($stats): ?>

    <?php
        $total     = (int)($stats['total_deliveries'] ?? 0);
        $completed = (int)($stats['completed']        ?? 0);
        $pending   = (int)($stats['pending']          ?? 0);
        $failed    = (int)($stats['failed']           ?? 0);
        $earnings  = (float)($stats['total_earnings'] ?? 0);

        $denom     = $total ?: 1;
        $pctDone   = round($completed / $denom * 100);
        $pctPend   = round($pending   / $denom * 100);
        $pctFail   = round($failed    / $denom * 100);
        $successRate = $pctDone;

        // Donut segments (r=54, circumference=339.3)
        $circ = 2 * M_PI * 54;
        function seg(float $pct, float $circ): string {
            $len = $pct / 100 * $circ;
            return round($len, 2) . ' ' . round($circ - $len, 2);
        }
        // Offsets: done starts at 0, pending after done, failed after pending
        $offDone = 0;
        $offPend = -($completed / $denom * $circ);
        $offFail = -(($completed + $pending) / $denom * $circ);

        // Per-delivery earnings
        $perDelivery = $total > 0 ? $earnings / $total : 0;
    ?>

    <!-- ── 4 stat cards ── -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="label">Total</div>
            <div class="value" data-target="<?= $total ?>">0</div>
            <div class="sub">livraisons assignées</div>
            <div class="deco">📦</div>
        </div>
        <div class="stat-card">
            <div class="label">Livrées</div>
            <div class="value" style="color:#15803D" data-target="<?= $completed ?>">0</div>
            <div class="sub"><?= $pctDone ?>% du total</div>
            <div class="deco">✅</div>
        </div>
        <div class="stat-card">
            <div class="label">En attente</div>
            <div class="value" style="color:#B45309" data-target="<?= $pending ?>">0</div>
            <div class="sub"><?= $pctPend ?>% du total</div>
            <div class="deco">⏳</div>
        </div>
        <div class="stat-card">
            <div class="label">Échouées</div>
            <div class="value" style="color:#B91C1C" data-target="<?= $failed ?>">0</div>
            <div class="sub"><?= $pctFail ?>% du total</div>
            <div class="deco">⚠️</div>
        </div>
    </div>

    <!-- ── Gains ── -->
    <div class="earnings-card">
        <div class="deco-circle" style="width:200px;height:200px;top:-60px;right:-60px;"></div>
        <div class="deco-circle" style="width:120px;height:120px;bottom:-40px;left:20px;"></div>
        <div style="position:relative">
            <div class="label">Gains totaux</div>
            <div>
                <span class="amount" data-earnings="<?= $earnings ?>"><?= number_format($earnings, 0, '', ' ') ?></span>
                <span class="unit">FCFA</span>
            </div>
            <div class="per">
                soit ~<?= number_format($perDelivery, 0, '', ' ') ?> FCFA par livraison
            </div>
        </div>
    </div>

    <!-- ── Répartition + taux de réussite ── -->
    <div style="display:grid;grid-template-columns:1fr;gap:14px;margin-bottom:14px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

            <!-- Donut -->
            <div class="section-card">
                <h2>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                    Répartition
                </h2>
                <div class="donut-wrap">
                    <svg class="donut-svg" width="130" height="130" viewBox="0 0 130 130">
                        <circle cx="65" cy="65" r="54" fill="none" stroke="#FEF3C7" stroke-width="14"/>
                        <!-- Failed -->
                        <circle cx="65" cy="65" r="54" fill="none" stroke="#FCA5A5" stroke-width="14"
                            stroke-dasharray="<?= seg($pctFail, $circ) ?>"
                            stroke-dashoffset="<?= $offFail ?>"
                            style="transform:rotate(-90deg);transform-origin:65px 65px"/>
                        <!-- Pending -->
                        <circle cx="65" cy="65" r="54" fill="none" stroke="#FCD34D" stroke-width="14"
                            stroke-dasharray="<?= seg($pctPend, $circ) ?>"
                            stroke-dashoffset="<?= $offPend ?>"
                            style="transform:rotate(-90deg);transform-origin:65px 65px"/>
                        <!-- Completed -->
                        <circle cx="65" cy="65" r="54" fill="none" stroke="#4ADE80" stroke-width="14"
                            stroke-dasharray="<?= seg($pctDone, $circ) ?>"
                            stroke-dashoffset="<?= $offDone ?>"
                            style="transform:rotate(-90deg);transform-origin:65px 65px"/>
                        <text x="65" y="60" text-anchor="middle" font-size="20" font-weight="900" fill="#78350F" font-family="Inter,sans-serif"><?= $total ?></text>
                        <text x="65" y="76" text-anchor="middle" font-size="9" fill="#A16207" font-family="Inter,sans-serif">TOTAL</text>
                    </svg>
                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-dot" style="background:#4ADE80"></div>
                            <span class="legend-label">Livrées</span>
                            <span class="legend-pct" style="color:#15803D"><?= $pctDone ?>%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot" style="background:#FCD34D"></div>
                            <span class="legend-label">En attente</span>
                            <span class="legend-pct" style="color:#B45309"><?= $pctPend ?>%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot" style="background:#FCA5A5"></div>
                            <span class="legend-label">Échouées</span>
                            <span class="legend-pct" style="color:#B91C1C"><?= $pctFail ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Taux de réussite (arc) -->
            <div class="section-card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <h2 style="margin-bottom:14px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Taux de réussite
                </h2>
                <?php
                    $arcR    = 50;
                    $arcCirc = M_PI * $arcR; // demi-cercle
                    $arcFill = $successRate / 100 * $arcCirc;
                ?>
                <svg width="130" height="75" viewBox="0 0 130 75">
                    <!-- track demi-cercle -->
                    <path d="M10,70 A55,55 0 0,1 120,70" fill="none" stroke="#FEF3C7" stroke-width="12" stroke-linecap="round"/>
                    <!-- fill -->
                    <path d="M10,70 A55,55 0 0,1 120,70" fill="none"
                        stroke="<?= $successRate >= 80 ? '#16A34A' : ($successRate >= 50 ? '#D97706' : '#DC2626') ?>"
                        stroke-width="12" stroke-linecap="round"
                        stroke-dasharray="<?= round($arcFill,1) ?> 999"
                        id="arcFill"/>
                    <text x="65" y="62" text-anchor="middle" font-size="24" font-weight="900"
                        fill="<?= $successRate >= 80 ? '#15803D' : ($successRate >= 50 ? '#92400E' : '#B91C1C') ?>"
                        font-family="Inter,sans-serif"><?= $successRate ?>%</text>
                </svg>
                <div style="font-size:12px;color:#A16207;text-align:center;margin-top:4px;">
                    <?php if ($successRate >= 80): ?>
                        🏆 Excellente performance !
                    <?php elseif ($successRate >= 50): ?>
                        📈 Performance correcte
                    <?php else: ?>
                        ⚠️ À améliorer
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Barres comparatives ── -->
    <div class="section-card">
        <h2>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Comparatif détaillé
        </h2>
        <?php
            $bars = [
                ['label' => 'Livrées',   'val' => $completed, 'color' => '#4ADE80', 'pct' => $pctDone],
                ['label' => 'Attente',   'val' => $pending,   'color' => '#FCD34D', 'pct' => $pctPend],
                ['label' => 'Échouées', 'val' => $failed,    'color' => '#FCA5A5', 'pct' => $pctFail],
            ];
            foreach ($bars as $b):
        ?>
        <div class="bar-row">
            <div class="bar-name"><?= $b['label'] ?></div>
            <div class="bar-track">
                <div class="bar-fill" style="width:<?= $b['pct'] ?>%;background:<?= $b['color'] ?>"></div>
            </div>
            <div class="bar-val"><?= $b['val'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>

    <!-- État vide -->
    <div class="empty">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.3">
            <path d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/>
        </svg>
        <p>Aucune donnée disponible</p>
        <span>Vos statistiques apparaîtront une fois vos premières livraisons effectuées.</span>
    </div>

    <?php endif; ?>
</div>

<script>
// Counter animation
document.querySelectorAll('.value[data-target]').forEach(el => {
    const target = parseInt(el.dataset.target, 10);
    if (!target) { el.textContent = '0'; return; }
    let start = null;
    const duration = 900;
    function step(ts) {
        if (!start) start = ts;
        const progress = Math.min((ts - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(ease * target);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>