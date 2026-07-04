<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- Consider moving Google Fonts link to partials/header.php -->
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

<!-- Inline styles moved to public/css/style.css -->
<!-- views/farmer/assign_delivery.php: original <style> block consolidated into public/css/style.css -->

<?php
// Couleurs d'avatar pour les livreurs
$avatarColors = ['#2D7A4F','#1A3C2B','#D4A853','#5B8A6E','#8B6914','#3D6B52'];
?>

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
    <div class="errors-box">
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
            <span style="width:6px;height:6px;border-radius:50%;background:var(--vert-action);display:inline-block;"></span>
            <?= htmlspecialchars(formatStatusLabel($order['status'])) ?>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date de commande</span>
          <span class="detail-value" style="font-family:'DM Mono',monospace;font-size:.8rem;">
            <?= date('d/m/Y', strtotime($order['created_at'])) ?><br>
            <span style="color:var(--gris-doux);font-weight:400;"><?= date('H:i', strtotime($order['created_at'])) ?></span>
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
        <form action="index.php?action=farmer/assign-delivery&order_id=<?= $order['id'] ?>" method="post">
          <div class="delivery-list" style="margin-bottom:1.25rem;">
            <?php if (empty($deliveries)): ?>
              <div class="empty-box">
                <div class="ico">🔍</div>
                <p style="margin:0;font-weight:600;">Aucun livreur disponible</p>
                <p style="margin:.25rem 0 0;font-size:.8rem;opacity:.8;">Réessayez plus tard ou contactez votre équipe.</p>
              </div>
            <?php else: ?>
              <?php foreach ($deliveries as $i => $delivery):
                $initials = mb_strtoupper(mb_substr($delivery['name'], 0, 1));
                $words = explode(' ', trim($delivery['name']));
                if (count($words) >= 2) {
                  $initials = mb_strtoupper(mb_substr($words[0],0,1) . mb_substr($words[1],0,1));
                }
                $color = $avatarColors[$i % count($avatarColors)];
              ?>
                <label style="display:block;">
                  <input
                    type="radio"
                    name="delivery_id"
                    value="<?= $delivery['id'] ?>"
                    class="delivery-option"
                    required
                  >
                  <div class="delivery-card">
                    <div class="avatar" style="background:<?= $color ?>;"><?= $initials ?></div>
                    <div class="delivery-info">
                      <div class="name"><?= htmlspecialchars($delivery['name']) ?></div>
                      <?php if (!empty($delivery['phone'])): ?>
                        <div class="phone"><?= htmlspecialchars($delivery['phone']) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </label>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div style="display:flex;flex-direction:column;gap:.6rem;">
            <?php if (!empty($deliveries)): ?>
              <button type="submit" class="btn-submit">
                <span>🚀</span> Confirmer l'assignation
              </button>
            <?php endif; ?>
            <a href="index.php?action=farmer/orders" class="btn-cancel">
              <span>✕</span> Annuler
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
        <span>La commande est assignée au livreur choisi</span>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <span>Le statut passe automatiquement à <strong>En cours</strong></span>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <span>Le livreur reçoit une notification et peut accepter ou refuser</span>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <span>Une fois acceptée, il accède aux coordonnées du client</span>
      </div>
    </div>
  </div>

</div><!-- /page-wrap -->

<?php require __DIR__ . '/../partials/footer.php'; ?>