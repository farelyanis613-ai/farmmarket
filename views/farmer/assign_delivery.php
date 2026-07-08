<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
$avatarColors = ['#2D7A4F','#1A3C2B','#D4A853','#5B8A6E','#8B6914','#3D6B52'];
?>

<style>
.page-wrap {
  max-width: 860px;
  margin: 0 auto;
  padding: 1.75rem 1.25rem 3rem;
  font-family: 'Inter', sans-serif;
}

/* Breadcrumb */
.breadcrumb {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #5f5e5a;
  text-decoration: none;
  margin-bottom: 1.75rem;
  padding: 6px 12px 6px 8px;
  border: 0.5px solid #d3d1c7;
  border-radius: 8px;
  background: #f1efe8;
  transition: background .15s;
}
.breadcrumb:hover { background: #e8e6df; }
.breadcrumb .arrow { font-size: 15px; line-height: 1; }

/* Header commande */
.cmd-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 1.75rem;
}
.cmd-icon {
  width: 50px;
  height: 50px;
  background: #eaf3de;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}
.cmd-header h1 {
  font-size: 20px;
  font-weight: 600;
  color: #2c2c2a;
  margin: 0 0 6px;
}
.cmd-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  background: #f1efe8;
  border: 0.5px solid #d3d1c7;
  border-radius: 20px;
  padding: 3px 10px;
  color: #5f5e5a;
}
.cmd-badge .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #3b6d11;
  display: inline-block;
}

/* Erreurs */
.errors-box {
  background: #fcebeb;
  border: 0.5px solid #f09595;
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 1.25rem;
  color: #791f1f;
  font-size: 13px;
}
.errors-box ul {
  margin: 0;
  padding-left: 18px;
}
.errors-box li { margin: 4px 0; }

/* Grille principale */
.main-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 14px;
  margin-bottom: 1.25rem;
}

/* Cartes */
.card {
  background: #ffffff;
  border: 0.5px solid #d3d1c7;
  border-radius: 14px;
  overflow: hidden;
}
.card-head {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  border-bottom: 0.5px solid #d3d1c7;
  background: #f1efe8;
}
.card-head .ico {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #ffffff;
  border: 0.5px solid #d3d1c7;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}
.card-head h2 {
  font-size: 14px;
  font-weight: 600;
  color: #2c2c2a;
  margin: 0;
}
.card-body { padding: 18px; }

/* Lignes détail */
.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 0.5px solid #e8e6df;
}
.detail-row:last-child { border-bottom: none; }
.detail-label {
  font-size: 12px;
  color: #888780;
}
.price-value {
  font-size: 16px;
  font-weight: 600;
  color: #3b6d11;
}
.status-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  background: #eaf3de;
  color: #3b6d11;
  border-radius: 20px;
  padding: 3px 10px;
}
.detail-value {
  font-family: 'DM Mono', monospace;
  font-size: 12px;
  font-weight: 500;
  color: #2c2c2a;
  text-align: right;
  line-height: 1.6;
}
.detail-value .time {
  display: block;
  color: #888780;
  font-weight: 400;
  font-size: 11px;
}

/* Liste livreurs — radio accessible via label wrapper */
.delivery-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}
.delivery-option-label {
  display: block;
  cursor: pointer;
}
/* Cacher le radio natif tout en gardant l'accessibilité */
.delivery-option-label input[type="radio"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.delivery-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  border: 0.5px solid #d3d1c7;
  border-radius: 10px;
  background: #f1efe8;
  transition: border-color .15s, background .15s;
  position: relative;
}
.delivery-option-label:hover .delivery-card {
  background: #e8e6df;
  border-color: #b4b2a9;
}
/* État sélectionné */
.delivery-option-label input[type="radio"]:checked + .delivery-card {
  border-color: #185fa5;
  background: #e6f1fb;
}
.delivery-option-label input[type="radio"]:checked + .delivery-card .delivery-name {
  color: #185fa5;
}
/* Focus visible clavier */
.delivery-option-label input[type="radio"]:focus-visible + .delivery-card {
  outline: 2px solid #185fa5;
  outline-offset: 2px;
}
/* Indicateur de sélection */
.delivery-check {
  margin-left: auto;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid #b4b2a9;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: border-color .15s, background .15s;
  font-size: 10px;
  color: transparent;
}
.delivery-option-label input[type="radio"]:checked + .delivery-card .delivery-check {
  border-color: #185fa5;
  background: #185fa5;
  color: #ffffff;
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
  flex-shrink: 0;
}
.delivery-info .delivery-name {
  font-size: 13px;
  font-weight: 500;
  color: #2c2c2a;
  transition: color .15s;
}
.delivery-info .delivery-phone {
  font-size: 12px;
  color: #888780;
  margin-top: 2px;
}

/* État vide */
.empty-box {
  text-align: center;
  padding: 24px;
  border: 0.5px dashed #b4b2a9;
  border-radius: 10px;
  color: #5f5e5a;
}
.empty-box .ico { font-size: 24px; margin-bottom: 8px; }
.empty-box p { font-size: 13px; margin: 4px 0 0; }

/* Boutons */
.btn-submit {
  width: 100%;
  padding: 11px;
  border-radius: 8px;
  border: none;
  background: #185fa5;
  color: #ffffff;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: opacity .15s;
  font-family: 'Inter', sans-serif;
}
.btn-submit:hover { opacity: .88; }
.btn-submit:active { opacity: .75; }

.btn-cancel {
  width: 100%;
  padding: 11px;
  border-radius: 8px;
  border: 0.5px solid #b4b2a9;
  background: transparent;
  color: #5f5e5a;
  font-size: 13px;
  font-weight: 400;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  transition: background .15s;
  font-family: 'Inter', sans-serif;
}
.btn-cancel:hover { background: #f1efe8; }

.actions { display: flex; flex-direction: column; gap: 8px; }

/* Bannière info processus */
.info-banner {
  background: #eaf3de;
  border: 0.5px solid #c0dd97;
  border-radius: 14px;
  padding: 18px 20px;
}
.info-banner h3 {
  font-size: 13px;
  font-weight: 600;
  color: #3b6d11;
  margin: 0 0 14px;
}
.steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 10px;
}
.step {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #ffffff;
  border: 0.5px solid #c0dd97;
  border-radius: 10px;
  padding: 12px;
}
.step-num {
  width: 22px;
  height: 22px;
  min-width: 22px;
  border-radius: 50%;
  background: #3b6d11;
  color: #ffffff;
  font-size: 11px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.step span {
  font-size: 12px;
  color: #444441;
  line-height: 1.5;
}
.step span strong {
  color: #2c2c2a;
  font-weight: 600;
}
</style>

<div class="page-wrap">

  <!-- Breadcrumb -->
  <a href="index.php?action=farmer/orders" class="breadcrumb">
    <span class="arrow">←</span> Retour aux commandes
  </a>

  <!-- Header commande -->
  <div class="cmd-header">
    <div class="cmd-icon">🐔</div>
    <div>
      <h1>Assigner une livraison</h1>
      <span class="cmd-badge">
        <span class="dot"></span>
        Commande #<?= htmlspecialchars($order['id']) ?>
      </span>
    </div>
  </div>

  <!-- Erreurs -->
  <?php if (!empty($errors)) : ?>
    <div class="errors-box" role="alert">
      <ul>
        <?php foreach ($errors as $error) : ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Grille principale -->
  <div class="main-grid">

    <!-- Carte détails commande -->
    <div class="card">
      <div class="card-head">
        <div class="ico">📋</div>
        <h2>Détails de la commande</h2>
      </div>
      <div class="card-body">
        <div class="detail-row">
          <span class="detail-label">Montant total</span>
          <span class="price-value"><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Statut actuel</span>
          <span class="status-chip">
            <span style="width:6px;height:6px;border-radius:50%;background:#3b6d11;display:inline-block;"></span>
            <?= htmlspecialchars(formatStatusLabel($order['status'])) ?>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date de commande</span>
          <span class="detail-value">
            <?= date('d/m/Y', strtotime($order['created_at'])) ?>
            <span class="time"><?= date('H:i', strtotime($order['created_at'])) ?></span>
          </span>
        </div>
      </div>
    </div>

    <!-- Carte sélection livreur -->
    <div class="card">
      <div class="card-head">
        <div class="ico">🛵</div>
        <h2>Choisir un livreur</h2>
      </div>
      <div class="card-body">
        <form action="index.php?action=farmer/assign-delivery&order_id=<?= (int) $order['id'] ?>" method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

          <div class="delivery-list">
            <?php if (empty($deliveries)) : ?>
              <div class="empty-box">
                <div class="ico">🔍</div>
                <p style="font-weight:600;">Aucun livreur disponible</p>
                <p>Réessayez plus tard ou contactez votre équipe.</p>
              </div>
            <?php else : ?>
              <?php foreach ($deliveries as $i => $delivery) :
                $words = explode(' ', trim($delivery['name']));
                $initials = mb_strtoupper(mb_substr($words[0], 0, 1));
                if (count($words) >= 2) {
                  $initials .= mb_strtoupper(mb_substr($words[1], 0, 1));
                }
                $color = $avatarColors[$i % count($avatarColors)];
              ?>
                <!-- Label wrappant le radio : toute la carte est cliquable -->
                <label class="delivery-option-label">
                  <input
                    type="radio"
                    name="delivery_id"
                    value="<?= (int) $delivery['id'] ?>"
                    required
                    <?= ($i === 0) ? 'checked' : '' ?>
                  >
                  <div class="delivery-card">
                    <div class="avatar" style="background:<?= htmlspecialchars($color) ?>;">
                      <?= htmlspecialchars($initials) ?>
                    </div>
                    <div class="delivery-info">
                      <div class="delivery-name"><?= htmlspecialchars($delivery['name']) ?></div>
                      <?php if (!empty($delivery['phone'])) : ?>
                        <div class="delivery-phone"><?= htmlspecialchars($delivery['phone']) ?></div>
                      <?php endif; ?>
                    </div>
                    <!-- Indicateur visuel de sélection -->
                    <span class="delivery-check" aria-hidden="true">✓</span>
                  </div>
                </label>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="actions">
            <?php if (!empty($deliveries)) : ?>
              <button type="submit" class="btn-submit">
                🚀 Confirmer l'assignation
              </button>
            <?php endif; ?>
            <a href="index.php?action=farmer/orders" class="btn-cancel">
              ✕ Annuler
            </a>
          </div>

        </form>
      </div>
    </div>

  </div><!-- /main-grid -->

  <!-- Info processus -->
  <div class="info-banner">
    <h3>🌾 Ce qui se passe après confirmation</h3>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <span>Commande assignée au livreur sélectionné</span>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <span>Statut passe à <strong>En cours</strong> automatiquement</span>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <span>Le livreur reçoit une notification et peut accepter ou refuser</span>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <span>Après acceptation, il accède aux coordonnées du client</span>
      </div>
    </div>
  </div>

</div><!-- /page-wrap -->

<?php require __DIR__ . '/../partials/footer.php'; ?>