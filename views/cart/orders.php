<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="fm-page page-orders">

  <div class="fm-page-header">
    <div>
      <div class="fm-header-eyebrow">Farmmarket</div>
      <h1 class="fm-page-title">Mes commandes</h1>
      <p class="fm-page-sub">Historique de toutes vos commandes</p>
    </div>
    <div class="fm-page-header-actions">
      <a href="index.php?action=products" class="fm-btn-primary">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle commande
      </a>
      <?php if (!empty($orders)) : ?>
        <form method="post" action="index.php?action=order/clear-history">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
          <button type="submit" class="fm-btn-sm fm-btn-danger" onclick="return confirm('Supprimer tout l\'historique de commandes ? Cette action est irréversible.');">
            Effacer l'historique de commande
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($_SESSION['error'])) : ?>
    <div class="fm-alert-error">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><line x1="12" y1="5" x2="12" y2="19"/></svg>
      <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['success'])) : ?>
    <div class="fm-alert-success">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (empty($orders)) : ?>
    <div class="fm-empty-state">
      <div class="fm-empty-icon">🛒</div>
      <h2 class="fm-empty-title">Aucune commande pour le moment</h2>
      <p class="fm-empty-sub">Vous n'avez pas encore passé de commande.<br>Découvrez nos produits frais d'élevage.</p>
      <a href="index.php?action=products" class="fm-btn-primary">Découvrir nos produits</a>
    </div>

  <?php else : ?>
    <div class="fm-orders-grid">
      <?php foreach ($orders as $order) : ?>
        <?php
          $orderId   = intval($order['id'] ?? 0);
          $status    = normalizeStatus($order['status'] ?? 'pending');
          $statusLabel = formatStatusLabel($order['status'] ?? 'pending');
          $totalPrice  = number_format((float)($order['total_price'] ?? 0), 0, '', ' ');
          $createdAt   = !empty($order['created_at']) ? date('d/m/Y · H:i', strtotime($order['created_at'])) : 'Date inconnue';
          $deliveryTypeLabel = isset($order['delivery_type']) && $order['delivery_type'] === 'shop'
            ? 'Retrait en boutique' : 'Livraison à domicile';
          $addressText = trim($order['delivery_address'] ?? $order['address'] ?? '');
          if ($addressText === '') $addressText = 'Adresse non renseignée';

          $badgeClass = 'fm-badge-pending';
          if ($status === 'accepted')    $badgeClass = 'fm-badge-confirmed';
          elseif ($status === 'in_progress') $badgeClass = 'fm-badge-delivering';
          elseif ($status === 'delivered')   $badgeClass = 'fm-badge-delivered';
          elseif (in_array($status, ['failed','rejected','cancelled'], true)) $badgeClass = 'fm-badge-cancelled';

          $isCancelled = in_array($status, ['failed','rejected','cancelled'], true);
          $canReportFailure = in_array($status, ['accepted','in_progress'], true) && empty($order['failure_reported']);
          $isReported = $status === 'failed' || !empty($order['failure_reported']);

          $stepIndex = 0;
          if ($status === 'accepted')    $stepIndex = 1;
          if ($status === 'in_progress') $stepIndex = 2;
          if ($status === 'delivered')   $stepIndex = 3;

          $stepColors  = ['#94A3B8','#4F46E5','#D97706','#22A45D'];
          $stepGlows   = ['rgba(148,163,184,0.15)','rgba(79,70,229,0.15)','rgba(217,119,6,0.15)','rgba(34,164,93,0.15)'];
          $progWidths  = ['15%','42%','72%','100%'];
          $stepColor = $isCancelled ? '#DC2626' : ($stepColors[$stepIndex] ?? '#94A3B8');
          $stepGlow  = $isCancelled ? 'rgba(220,38,38,0.15)' : ($stepGlows[$stepIndex] ?? 'rgba(148,163,184,0.15)');
          $progW     = $isCancelled ? '100%' : ($progWidths[$stepIndex] ?? '15%');
        ?>
        <article class="fm-order-card"
          style="--progress-color:<?= $stepColor ?>; --progress-width:<?= $progW ?>; --step-color:<?= $stepColor ?>; --step-glow:<?= $stepGlow ?>">

          <div class="fm-order-head">
            <div>
              <div class="fm-order-num">Commande #<?= htmlspecialchars((string)($order['order_number'] ?? $orderId)) ?></div>
              <div class="fm-order-date"><?= htmlspecialchars($createdAt) ?></div>
              <div class="fm-order-subhead">
                <span class="fm-order-pill"><?= htmlspecialchars($deliveryTypeLabel) ?></span>
                <span class="fm-order-total-summary"><?= htmlspecialchars($totalPrice) ?> FCFA</span>
              </div>
            </div>
            <span class="fm-badge <?= $badgeClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
          </div>

          <div class="fm-order-divider"></div>
          <div class="fm-order-info">
            <div class="fm-order-info-row">
              <span class="fm-info-icon">📍</span>
              <span><?= htmlspecialchars($deliveryTypeLabel) ?></span>
            </div>
            <div class="fm-order-info-row">
              <span class="fm-info-icon">🏠</span>
              <span><?= htmlspecialchars($addressText) ?></span>
            </div>
          </div>

          <div class="fm-order-foot">
            <div class="fm-foot-left">
              <span class="fm-total-label">Total</span>
              <span class="fm-total-amount"><?= htmlspecialchars($totalPrice) ?> <small>FCFA</small></span>
            </div>
            <div class="fm-foot-actions">
              <a href="index.php?action=order/invoice&id=<?= $orderId ?>" class="fm-btn-sm fm-btn-pdf" title="Télécharger la facture PDF" target="_blank">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                PDF
              </a>
              <a href="index.php?action=order/view&id=<?= $orderId ?>" class="fm-btn-sm fm-btn-view">
                Voir
                <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
              </a>
              <?php if ($canReportFailure) : ?>
                <button type="button"
                        class="fm-btn-sm fm-btn-danger fm-btn-report"
                        data-order-id="<?= $orderId ?>"
                        data-csrf="<?= htmlspecialchars(getCsrfToken()) ?>"
                        title="Signaler un échec de commande">
                  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                  <span class="fm-btn-report-label">Échec</span>
                </button>
              <?php elseif ($isReported) : ?>
                <span class="fm-badge fm-badge-reported" title="Cette commande a déjà été signalée comme échouée">
                  Signalé
                </span>
              <?php endif; ?>
            </div>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="fm-page page-orders">
      const confirmed = confirm('Confirmer le signalement d\'échec pour cette commande ?');
      if (!confirmed) return;

      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="fm-btn-report-label">Envoi…</span>';

      const form = new FormData();
      form.append('order_id', orderId);
      form.append('reason', reason.trim() !== '' ? reason.trim() : 'Signalé par le client');
      form.append('csrf_token', csrf);

      try {
        const res = await fetch('index.php?action=order/report-failure', {
          method: 'POST',
          body: form
        });

        if (res.redirected) {
          window.location = res.url;
          return;
        }
        if (!res.ok) {
          throw new Error('Erreur réseau (HTTP ' + res.status + ')');
        }

        window.location.reload();
      } catch (e) {
        alert('Impossible de signaler l\'échec pour le moment. Veuillez réessayer.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    });
  });
});
</script>