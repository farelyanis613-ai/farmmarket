<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
$avatarColors = ['#2D7A4F','#1A3C2B','#D4A853','#5B8A6E','#8B6914','#3D6B52'];
?>


<div class="page-wrap page-assign-delivery">

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