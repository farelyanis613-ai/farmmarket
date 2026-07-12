<?php
    $photoFolder = __DIR__ . '/../../public/images/livreurt';
    $photoFiles = [];
    if (is_dir($photoFolder)) {
        foreach (scandir($photoFolder) as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $photoFiles[] = 'livreurt/' . $file;
            }
        }
    }
    $currentPhotoUrl = htmlspecialchars($_POST['photo_url'] ?? '');
<?php require __DIR__ . '/../partials/header.php'; ?>


<div class="page-wrap page-add-delivery">
    <span class="arrow">←</span> Retour aux livreurs
  </a>

  <div class="cmd-header">
    <div class="cmd-icon">🛵</div>
    <div>
      <h1>Ajouter un livreur</h1>
      <p>Créez un compte livreur pour l'assignation des commandes et le suivi des livraisons.</p>
    </div>
  </div>

  <?php if (!empty($_SESSION['success'])) : ?>
    <div class="success-box">
      <?= htmlspecialchars($_SESSION['success']) ?>
      <?php unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)) : ?>
    <div class="errors-box" role="alert">
      <ul>
        <?php foreach ($errors as $error) : ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="index.php?action=farmer/deliveries/add" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="card">
      <div class="card-head">
        <div class="ico">👤</div>
        <h2>Informations du livreur</h2>
      </div>
      <div class="card-body">
        <div class="field-row">
          <div class="field">
            <label for="name">Nom du livreur</label>
            <input id="name" name="name" type="text" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="phone">Téléphone</label>
            <input id="phone" name="phone" type="tel" placeholder="+229 01 XX XX XX XX" pattern="^\+229(?:\s?\d){10}$" title="Format attendu : +229 01 XX XX XX XX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="address">Adresse</label>
            <input id="address" name="address" type="text" placeholder="Quartier, ville" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
          </div>
        </div>

        <div class="field">
          <label for="photo_url">Photo du livreur</label>
          <div class="photo-picker-row">
            <input id="photo_url" name="photo_url" type="url" placeholder="https://example.com/photo.jpg" value="<?= $currentPhotoUrl ?>">
            <button type="button" id="select-photo-btn" class="btn-secondary">Choisir une photo</button>
          </div>
          <p class="hint">Choisissez une image depuis <code>public/images/livreurt</code> ou collez un lien direct.</p>
          <div id="photo-preview" class="photo-preview" style="<?= $currentPhotoUrl ? '' : 'display:none;' ?>">
            <img id="photo-preview-img" src="<?= $currentPhotoUrl ?>" alt="Aperçu">
            <span>Aperçu de la photo sélectionnée</span>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <div class="ico">🔒</div>
        <h2>Accès au compte</h2>
      </div>
      <div class="card-body">
        <p class="hint" style="margin-top:-4px;margin-bottom:14px;">
          Laissez ces champs vides pour utiliser le mot de passe par défaut
          (<code>delivery123</code>) — le livreur pourra le changer à sa première connexion.
        </p>
        <div class="field-row">
          <div class="field">
            <label for="password">Mot de passe (optionnel)</label>
            <input id="password" name="password" type="password" autocomplete="new-password">
          </div>
          <div class="field">
            <label for="confirm">Confirmez le mot de passe</label>
            <input id="confirm" name="confirm" type="password" autocomplete="new-password">
          </div>
        </div>
      </div>
    </div>

    <div class="actions-row">
      <div class="actions-left">
        <a href="index.php?action=farmer/dashboard" class="btn-cancel">Tableau de bord</a>
        <a href="index.php?action=farmer/deliveries" class="btn-cancel">Voir les livreurs</a>
      </div>
      <button type="submit" class="btn-submit">Créer le livreur</button>
    </div>
  </form>
</div>

<div id="photo-picker-modal" class="photo-modal">
  <div class="photo-modal-box">
    <div class="photo-modal-head">
      <div>
        <h2>Choisir une photo de livreur</h2>
        <p>Sélectionnez une image depuis le dossier <code>public/images/livreurt</code>.</p>
      </div>
      <button type="button" id="close-photo-picker" class="photo-modal-close">✕</button>
    </div>
    <?php if (empty($photoFiles)): ?>
      <div class="photo-empty">Aucune image trouvée dans le dossier <code>public/images/livreurt</code>.</div>
    <?php else: ?>
      <div class="photo-grid">
        <?php foreach ($photoFiles as $photo): ?>
          <button type="button" class="photo-option" data-photo="<?= htmlspecialchars($photo) ?>">
            <img src="public/images/<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars(basename($photo)) ?>">
            <div class="name"><?= htmlspecialchars(basename($photo)) ?></div>
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var photoInput = document.getElementById('photo_url');
    var openBtn = document.getElementById('select-photo-btn');
    var modal = document.getElementById('photo-picker-modal');
    var closeBtn = document.getElementById('close-photo-picker');
    var preview = document.getElementById('photo-preview');
    var previewImg = document.getElementById('photo-preview-img');

    function showPreview(url) {
      if (!preview || !previewImg) return;
      if (url) {
        previewImg.src = url;
        preview.style.display = 'flex';
      } else {
        preview.style.display = 'none';
      }
    }

    if (openBtn && modal) {
      openBtn.addEventListener('click', function () {
        modal.classList.add('open');
      });
    }
    if (closeBtn && modal) {
      closeBtn.addEventListener('click', function () {
        modal.classList.remove('open');
      });
    }
    if (modal) {
      modal.addEventListener('click', function (event) {
        if (event.target === modal) {
          modal.classList.remove('open');
        }
      });
    }
    if (photoInput) {
      photoInput.addEventListener('input', function () {
        showPreview(this.value.trim());
      });
    }

    document.querySelectorAll('.photo-option').forEach(function (button) {
      button.addEventListener('click', function () {
        var photo = this.dataset.photo;
        var url = window.location.origin + window.location.pathname.replace(/index\.php$/, '') + 'public/images/' + photo;
        if (photoInput) {
          photoInput.value = url;
        }
        showPreview(url);
        modal.classList.remove('open');
      });
    });
  });
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>