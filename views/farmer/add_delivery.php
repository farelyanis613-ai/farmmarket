<?php require __DIR__ . '/../partials/header.php'; ?>
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

/* Header page */
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
  margin: 0 0 4px;
}
.cmd-header p {
  font-size: 13px;
  color: #5f5e5a;
  margin: 0;
}

/* Bannieres succes / erreurs */
.success-box {
  background: #eaf3de;
  border: 0.5px solid #c0dd97;
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 1.25rem;
  color: #3b6d11;
  font-weight: 600;
  font-size: 13px;
}
.errors-box {
  background: #fcebeb;
  border: 0.5px solid #f09595;
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 1.25rem;
  color: #791f1f;
  font-size: 13px;
}
.errors-box ul { margin: 0; padding-left: 18px; }
.errors-box li { margin: 4px 0; }

/* Carte formulaire */
.card {
  background: #ffffff;
  border: 0.5px solid #d3d1c7;
  border-radius: 14px;
  overflow: hidden;
  margin-bottom: 14px;
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

.field { margin-bottom: 1.1rem; }
.field:last-child { margin-bottom: 0; }
.field label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #444441;
  margin-bottom: 6px;
}
.field .hint {
  font-size: 11px;
  color: #888780;
  margin-top: 5px;
}
.field input[type="text"],
.field input[type="email"],
.field input[type="tel"],
.field input[type="url"],
.field input[type="password"] {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 0.5px solid #d3d1c7;
  background: #f9f8f5;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  color: #2c2c2a;
  transition: border-color .15s, background .15s;
}
.field input:focus {
  outline: none;
  border-color: #185fa5;
  background: #ffffff;
}
.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
@media (max-width: 560px) {
  .field-row { grid-template-columns: 1fr; }
}

/* Selecteur photo */
.photo-picker-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
.photo-picker-row input { flex: 1; }
.btn-secondary {
  flex-shrink: 0;
  padding: 10px 16px;
  border-radius: 10px;
  border: 0.5px solid #d3d1c7;
  background: #f1efe8;
  color: #2c2c2a;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background .15s;
  font-family: 'Inter', sans-serif;
}
.btn-secondary:hover { background: #e8e6df; }
.photo-preview {
  margin-top: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.photo-preview img {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 0.5px solid #d3d1c7;
}
.photo-preview span {
  font-size: 12px;
  color: #5f5e5a;
}

/* Actions */
.actions-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: space-between;
  align-items: center;
}
.actions-left { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-cancel {
  padding: 11px 20px;
  border-radius: 8px;
  border: 0.5px solid #b4b2a9;
  background: transparent;
  color: #5f5e5a;
  font-size: 13px;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: background .15s;
  font-family: 'Inter', sans-serif;
}
.btn-cancel:hover { background: #f1efe8; }
.btn-submit {
  padding: 11px 24px;
  border-radius: 8px;
  border: none;
  background: #185fa5;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity .15s;
  font-family: 'Inter', sans-serif;
}
.btn-submit:hover { opacity: .9; }

/* Modale selection photo */
.photo-modal {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: none;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,.4);
  padding: 1rem;
}
.photo-modal.open { display: flex; }
.photo-modal-box {
  width: 100%;
  max-width: 640px;
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 20px 45px rgba(0,0,0,.2);
}
.photo-modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 14px;
}
.photo-modal-head h2 { font-size: 15px; font-weight: 600; margin: 0 0 4px; color: #2c2c2a; }
.photo-modal-head p { font-size: 12px; color: #888780; margin: 0; }
.photo-modal-close {
  border: none;
  background: #f1efe8;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  color: #5f5e5a;
}
.photo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 10px;
  max-height: 340px;
  overflow-y: auto;
}
.photo-option {
  border: 0.5px solid #d3d1c7;
  border-radius: 10px;
  background: #f9f8f5;
  overflow: hidden;
  cursor: pointer;
  transition: border-color .15s;
}
.photo-option:hover { border-color: #185fa5; }
.photo-option img { width: 100%; height: 90px; object-fit: cover; display: block; }
.photo-option .name {
  font-size: 11px;
  color: #5f5e5a;
  text-align: center;
  padding: 6px 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.photo-empty {
  font-size: 12px;
  color: #5f5e5a;
  background: #f1efe8;
  border-radius: 10px;
  padding: 14px;
}
</style>

<div class="page-wrap">

  <a href="index.php?action=farmer/deliveries" class="breadcrumb">
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