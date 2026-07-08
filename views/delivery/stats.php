<?php require __DIR__ . '/../partials/header.php'; ?>
<link rel="stylesheet" href="/css/stats.css">

<div class="page-wrap">
    <?php
        $totalDeliveries = (int)($stats['total_deliveries'] ?? 0);
        $completed = (int)($stats['completed'] ?? 0);
        $failed = (int)($stats['failed'] ?? 0);
        $pending = (int)($stats['pending'] ?? 0);
        $earnings = (float)($stats['total_earnings'] ?? 0);
        $total = $totalDeliveries > 0 ? $totalDeliveries : 1;
        $pctCompleted = round($completed / $total * 100);
        $pctPending = round($pending / $total * 100);
        $pctFailed = round($failed / $total * 100);

        // Couleurs du parcours d'une livraison : bleu = acceptée/en cours,
        // vert = livrée (gain acquis), rouge = échouée (gain perdu).
        $colorInProgress = '#2563EB';
        $colorDone       = '#16A34A';
        $colorFailed     = '#DC2626';
    ?>

    <header class="page-header">
        <div>
            <p class="eyebrow">Tableau de bord livreur</p>
            <h1 class="page-title">Statistiques de livraison</h1>
            <p class="page-subtitle">Voici un aperçu rapide de votre activité, de vos gains et du taux de réussite de vos livraisons.</p>
        </div>
        <a href="index.php?action=delivery/dashboard" class="btn-back">← Retour au tableau de bord</a>
    </header>

    <div class="stat-grid">
        <div class="stat-card stat-card--done">
            <div class="stat-icon">✅</div>
            <div>
                <div class="label">Livraisons réussies</div>
                <div class="value"><?= $completed ?></div>
                <div class="sub"><?= $pctCompleted ?>% du total</div>
            </div>
        </div>

        <div class="stat-card stat-card--inprogress">
            <div class="stat-icon">🚴</div>
            <div>
                <div class="label">En cours</div>
                <div class="value"><?= $pending ?></div>
                <div class="sub"><?= $pctPending ?>% du total</div>
            </div>
        </div>

        <div class="stat-card stat-card--failed">
            <div class="stat-icon">⚠️</div>
            <div>
                <div class="label">Échecs</div>
                <div class="value"><?= $failed ?></div>
                <div class="sub"><?= $pctFailed ?>% du total</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div>
                <div class="label">Total affecté</div>
                <div class="value"><?= $totalDeliveries ?></div>
                <div class="sub">Commandes prises en charge</div>
            </div>
        </div>
    </div>

    <!-- ── Gains + barre de progression colorée par statut ── -->
    <div class="earnings-card">
        <div class="deco-circle deco-circle--1"></div>
        <div class="deco-circle deco-circle--2"></div>
        <div class="earnings-content">
            <div class="label">Gains cumulés</div>
            <div class="earnings-amount">
                <div class="amount"><?= number_format($earnings, 0, '', ' ') ?></div>
                <div class="unit">FCFA</div>
            </div>
            <div class="per">Basé sur les commandes enregistrées pour votre activité.</div>

            <!-- Barre de progression segmentée -->
            <div class="earnings-progress">
                <div class="earnings-progress-track">
                    <?php if ($pctPending > 0): ?>
                        <div class="earnings-progress-seg" style="width:<?= $pctPending ?>%;background:<?= $colorInProgress ?>;" title="En cours : <?= $pctPending ?>%"></div>
                    <?php endif; ?>
                    <?php if ($pctCompleted > 0): ?>
                        <div class="earnings-progress-seg" style="width:<?= $pctCompleted ?>%;background:<?= $colorDone ?>;" title="Livrées : <?= $pctCompleted ?>%"></div>
                    <?php endif; ?>
                    <?php if ($pctFailed > 0): ?>
                        <div class="earnings-progress-seg" style="width:<?= $pctFailed ?>%;background:<?= $colorFailed ?>;" title="Échouées : <?= $pctFailed ?>%"></div>
                    <?php endif; ?>
                </div>
                <div class="earnings-progress-legend">
                    <span class="legend-chip"><i style="background:<?= $colorInProgress ?>"></i>Acceptée — en cours</span>
                    <span class="legend-chip"><i style="background:<?= $colorDone ?>"></i>Livrée — gain acquis</span>
                    <span class="legend-chip"><i style="background:<?= $colorFailed ?>"></i>Échouée — gain perdu</span>
                </div>
            </div>
        </div>
    </div>

    <div class="charts-row">
        <section class="section-card">
            <h2 class="section-title">📈 Répartition</h2>
            <div class="bar-row">
                <div class="bar-name">En cours</div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $pctPending ?>%; background: <?= $colorInProgress ?>;"></div></div>
                <div class="bar-val"><?= $pctPending ?>%</div>
            </div>
            <div class="bar-row">
                <div class="bar-name">Réussies</div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $pctCompleted ?>%; background: <?= $colorDone ?>;"></div></div>
                <div class="bar-val"><?= $pctCompleted ?>%</div>
            </div>
            <div class="bar-row">
                <div class="bar-name">Échecs</div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $pctFailed ?>%; background: <?= $colorFailed ?>;"></div></div>
                <div class="bar-val"><?= $pctFailed ?>%</div>
            </div>
        </section>

        <section class="section-card section-card--center">
            <h2 class="section-title">🎯 Résultat global</h2>
            <p class="success-note">
                <?php if ($failed === 0): ?>
                    Excellent travail : aucune livraison échouée n'a été enregistrée pour le moment.
                <?php elseif ($completed >= $failed): ?>
                    Votre taux de réussite reste solide. Continuez à renforcer votre qualité de service.
                <?php else: ?>
                    Quelques livraisons ont rencontré un souci. Un suivi rapide peut améliorer votre taux de réussite.
                <?php endif; ?>
            </p>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>