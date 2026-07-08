<?php require __DIR__ . '/../partials/header.php'; ?>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg-root:       #F5F7FA;
  --bg-card:       #FFFFFF;
  --bg-card-hover: #FAFBFF;
  --border:        #E4E9F2;
  --border-hover:  #C7D4F0;
  --green:         #22A45D;
  --green-light:   #EAF7F0;
  --green-mid:     rgba(34,164,93,0.15);
  --indigo:        #4F46E5;
  --indigo-light:  #EEF2FF;
  --gold:          #D97706;
  --gold-light:    #FEF3C7;
  --red:           #DC2626;
  --red-light:     #FEE2E2;
  --slate:         #64748B;
  --slate-light:   #F1F5F9;
  --text-primary:  #0F172A;
  --text-secondary:#475569;
  --text-muted:    #94A3B8;
  --radius:        18px;
  --radius-sm:     10px;
  --shadow-card:   0 1px 3px rgba(15,23,42,0.06), 0 4px 16px rgba(15,23,42,0.05);
  --shadow-hover:  0 4px 6px rgba(15,23,42,0.04), 0 16px 40px rgba(15,23,42,0.10);
  --font-display:  'Sora', sans-serif;
  --font-body:     'Inter', sans-serif;
}

body {
  min-height: 100vh;
  background: radial-gradient(circle at top left, rgba(79,70,229,0.08), transparent 28%),
              #F5F7FA;
}

.fm-page {
  font-family: var(--font-body);
  color: var(--text-primary);
  min-height: 100vh;
  padding: 48px 20px 80px;
}

/* ── HEADER ── */
.fm-page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  max-width: 1100px;
  margin: 0 auto 42px;
  flex-wrap: wrap;
}
.fm-page-header > div {
  min-width: 0;
}
.fm-header-eyebrow {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--green);
  margin-bottom: 8px;
}
.fm-page-title {
  font-family: var(--font-display);
  font-size: clamp(28px, 4vw, 40px);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: var(--text-primary);
  line-height: 1.05;
}
.fm-page-sub {
  font-size: 15px;
  color: var(--text-secondary);
  margin-top: 8px;
  max-width: 560px;
}
.fm-page-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.fm-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--green);
  color: #fff;
  font-family: var(--font-display);
  font-size: 14px;
  font-weight: 700;
  padding: 14px 24px;
  border-radius: 999px;
  text-decoration: none;
  transition: background 0.2s, box-shadow 0.25s, transform 0.2s;
  white-space: nowrap;
}
.fm-btn-primary:hover {
  background: #1f8c51;
  transform: translateY(-1px);
  box-shadow: 0 12px 30px rgba(34,164,93,0.18);
}

/* ── ALERT ── */
.fm-alert-success {
  max-width: 1100px;
  margin: 0 auto 28px;
  background: var(--green-light);
  border: 1px solid rgba(34,164,93,0.22);
  border-radius: var(--radius-sm);
  padding: 14px 20px;
  font-size: 14px;
  color: var(--green);
  display: flex;
  align-items: center;
  gap: 10px;
}
.fm-alert-error {
  max-width: 1100px;
  margin: 0 auto 28px;
  background: var(--red-light);
  border: 1px solid rgba(220,38,38,0.22);
  border-radius: var(--radius-sm);
  padding: 14px 20px;
  font-size: 14px;
  color: var(--red);
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ── EMPTY STATE ── */
.fm-empty-state {
  max-width: 480px;
  margin: 90px auto;
  text-align: center;
}
.fm-empty-icon { font-size: 68px; margin-bottom: 22px; }
.fm-empty-title {
  font-family: var(--font-display);
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 10px;
}
.fm-empty-sub {
  font-size: 15px;
  color: var(--text-secondary);
  line-height: 1.75;
  margin-bottom: 30px;
}

/* ── GRID ── */
.fm-orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
  max-width: 1100px;
  margin: 0 auto;
}

/* ── CARD ── */
.fm-order-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow-card);
  transition: box-shadow 0.3s, transform 0.3s, border-color 0.3s;
  position: relative;
}
.fm-order-card:hover {
  box-shadow: var(--shadow-hover);
  transform: translateY(-3px);
  border-color: var(--border-hover);
}
/* top accent bar */
.fm-order-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--progress-color, var(--border));
  border-radius: 18px 18px 0 0;
  width: var(--progress-width, 15%);
  transition: width 0.5s ease, background 0.3s;
}

/* ── CARD HEAD ── */
.fm-order-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 22px 22px 14px;
  gap: 12px;
}
.fm-order-num {
  font-family: var(--font-display);
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.02em;
}
.fm-order-date {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 3px;
}
.fm-order-subhead {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
  margin-top: 14px;
  font-size: 13px;
  color: var(--text-secondary);
}
.fm-order-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 999px;
  background: var(--slate-light);
  color: var(--slate);
}
.fm-order-total-summary {
  margin-left: auto;
  font-weight: 700;
  color: var(--text-primary);
}

/* ── BADGE ── */
.fm-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  font-family: var(--font-display);
  letter-spacing: 0.03em;
  padding: 4px 11px;
  border-radius: 50px;
  white-space: nowrap;
  text-transform: uppercase;
}
.fm-badge::before {
  content: '';
  width: 5px; height: 5px;
  border-radius: 50%;
  background: currentColor;
}
.fm-badge-pending   { background: var(--slate-light);  color: var(--slate); }
.fm-badge-confirmed { background: var(--indigo-light); color: var(--indigo); }
.fm-badge-delivering{ background: var(--gold-light);   color: var(--gold); }
.fm-badge-delivered { background: var(--green-light);  color: var(--green); }
.fm-badge-cancelled { background: var(--red-light);    color: var(--red); }
.fm-badge-reported  { background: #FFF1E6; color: #C2410C; }

/* ── PROGRESS STEPS ── */
.fm-progress-steps {
  display: flex;
  align-items: center;
  padding: 4px 22px 18px;
}
.fm-step {
  display: flex;
  align-items: center;
  flex: 1;
}
.fm-step-dot {
  width: 22px; height: 22px;
  border-radius: 50%;
  border: 2px solid var(--border);
  background: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 9px; font-weight: 700;
  color: var(--text-muted);
  flex-shrink: 0;
  transition: all 0.25s;
}
.fm-step-dot.done {
  border-color: var(--step-color, var(--green));
  background: var(--step-color, var(--green));
  color: #fff;
}
.fm-step-dot.active {
  border-color: var(--step-color, var(--green));
  background: #fff;
  color: var(--step-color, var(--green));
  box-shadow: 0 0 0 4px var(--step-glow, rgba(34,164,93,0.15));
}
.fm-step-line {
  flex: 1; height: 2px;
  background: var(--border);
  margin: 0 3px;
  border-radius: 2px;
  transition: background 0.3s;
}
.fm-step-line.done { background: var(--step-color, var(--green)); }

/* ── INFO ROWS ── */
.fm-order-divider {
  height: 1px;
  background: var(--border);
  margin: 0;
}
.fm-order-info {
  padding: 20px 24px;
  display: grid;
  gap: 14px;
  background: #F8FAFD;
}
.fm-order-info-row {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 10px;
  align-items: flex-start;
  font-size: 14px;
  color: var(--text-secondary);
  line-height: 1.6;
}
.fm-info-icon {
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(79,70,229,0.08);
  color: var(--indigo);
  font-size: 14px;
  flex-shrink: 0;
}

/* ── CARD FOOTER ── */
.fm-order-foot {
  border-top: 1px solid var(--border);
  padding: 18px 24px 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
}
.fm-foot-left {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.fm-total-label {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-muted);
}
.fm-total-amount {
  font-family: var(--font-display);
  font-size: 20px;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.03em;
  line-height: 1.1;
}
.fm-total-amount small {
  font-size: 12px;
  font-weight: 500;
  color: var(--text-secondary);
}
.fm-foot-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* Shared small button base */
.fm-btn-sm {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 600;
  padding: 9px 15px;
  border-radius: 50px;
  text-decoration: none;
  transition: all 0.2s;
  white-space: nowrap;
  cursor: pointer;
  border: none;
}
.fm-btn-sm svg {
  width: 13px; height: 13px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2.2;
  stroke-linecap: round;
  stroke-linejoin: round;
  flex-shrink: 0;
}
.fm-btn-sm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* View button */
.fm-btn-view {
  background: var(--slate-light);
  color: var(--text-primary);
  border: 1px solid var(--border);
}
.fm-btn-view:hover {
  background: var(--indigo-light);
  border-color: rgba(79,70,229,0.2);
  color: var(--indigo);
}

/* PDF button */
.fm-btn-pdf {
  background: #FFF7ED;
  color: #C2410C;
  border: 1px solid #FDDCBB;
}
.fm-btn-pdf:hover {
  background: #FFEDD5;
  border-color: #FDBA74;
  box-shadow: 0 4px 12px rgba(194,65,12,0.15);
}

/* Failure button */
.fm-btn-danger {
  background: #FEF2F2;
  color: #B91C1C;
  border: 1px solid #FECACA;
}
.fm-btn-danger:hover {
  background: #FEE2E2;
  border-color: #FCA5A5;
  box-shadow: 0 4px 12px rgba(185,28,28,0.12);
  color: #991B1B;
}

/* ── RESPONSIVE ── */
@media (max-width: 600px) {
  .fm-page { padding: 28px 16px 60px; }
  .fm-page-header { flex-direction: column; align-items: flex-start; }
  .fm-orders-grid { grid-template-columns: 1fr; }
}
</style>

<div class="fm-page">

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

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.fm-btn-report').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      const orderId = this.dataset.orderId;
      const csrf    = this.dataset.csrf;

      const reason = prompt('Expliquez brièvement la raison de l\'échec (facultatif) :');
      if (reason === null) return; // annulé par l'utilisateur

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