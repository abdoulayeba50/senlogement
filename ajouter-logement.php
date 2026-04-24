<?php
session_start();

// Doit être connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?modal=login');
    exit;
}

$user_nom  = $_SESSION['user_nom']  ?? '';
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'client';

require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

// Récupérer les villes
$villes = $pdo->query("SELECT * FROM villes ORDER BY nom")->fetchAll();

$success = '';
$errors  = [];

// ── Traitement formulaire ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs
    $titre       = trim($_POST['titre']       ?? '');
    $ville_id    = (int)($_POST['ville_id']   ?? 0);
    $adresse     = trim($_POST['adresse']     ?? '');
    $type        = $_POST['type']             ?? 'appartement';
    $description = trim($_POST['description'] ?? '');
    $prix_nuit   = (float)($_POST['prix_nuit'] ?? 0);
    $nb_chambres = (int)($_POST['nb_chambres'] ?? 1);
    $nb_bains    = (int)($_POST['nb_bains']    ?? 1);
    $capacite    = (int)($_POST['capacite']    ?? 2);
    $wifi        = isset($_POST['wifi'])        ? 1 : 0;
    $clim        = isset($_POST['clim'])        ? 1 : 0;
    $piscine     = isset($_POST['piscine'])     ? 1 : 0;
    $parking     = isset($_POST['parking'])     ? 1 : 0;
    $consignes   = trim($_POST['consignes']    ?? '');
    $proprio_tel = trim($_POST['proprio_tel']  ?? '');

    // Validations
    if (!$titre)       $errors[] = 'Le titre est obligatoire.';
    if (!$ville_id)    $errors[] = 'Choisissez une ville.';
    if (!$type)        $errors[] = 'Choisissez un type de logement.';
    if (!$description) $errors[] = 'La description est obligatoire.';
    if ($prix_nuit <= 0) $errors[] = 'Le prix par nuit est obligatoire.';
    if (!$proprio_tel) $errors[] = 'Le numéro du bailleur est obligatoire.';

    // Vérification image
    $image_ok = false;
    if (!empty($_FILES['image']['name'])) {
        $ext_ok = ['jpg','jpeg','png','webp','gif'];
        $ext    = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ext_ok)) {
            $errors[] = 'Format image invalide (jpg, png, webp acceptés).';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'L\'image ne doit pas dépasser 5 Mo.';
        } else {
            $image_ok = true;
        }
    } else {
        $errors[] = 'Ajoutez au moins une photo du logement.';
    }

    // Si pas d'erreurs → insérer
    if (empty($errors)) {

        // Insérer le logement
        $stmt = $pdo->prepare("
            INSERT INTO logements
              (proprietaire_id, ville_id, titre, description, adresse, type,
               prix_nuit, nb_chambres, nb_salles_bain, capacite,
               wifi, climatisation, piscine, parking, consignes, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'disponible')
        ");
        $stmt->execute([
            $user_id, $ville_id, $titre, $description, $adresse, $type,
            $prix_nuit, $nb_chambres, $nb_bains, $capacite,
            $wifi, $clim, $piscine, $parking, $consignes
        ]);
        $logement_id = $pdo->lastInsertId();

        // Sauvegarder l'image
        if ($image_ok) {
            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = 'log_' . $logement_id . '_' . time() . '.' . $ext;
            $dest     = __DIR__ . '/images/' . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $pdo->prepare("
                    INSERT INTO logement_images (logement_id, chemin, principale)
                    VALUES (?, ?, 1)
                ")->execute([$logement_id, 'images/' . $filename]);
            }
        }

        // Mettre à jour le rôle en propriétaire si c'est un client
        if ($user_role === 'client') {
            $pdo->prepare("UPDATE users SET role = 'proprietaire' WHERE id = ?")
                ->execute([$user_id]);
            $_SESSION['user_role'] = 'proprietaire';
        }

        $success = 'Votre logement a été ajouté avec succès ! Il est maintenant visible sur le site.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter un logement – Sen Location</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    i { margin-right: 5px; }

    .page-wrap {
      max-width: 860px;
      margin: 0 auto;
      padding: 100px 24px 60px;
    }

    /* ── En-tête ── */
    .page-header {
      text-align: center;
      margin-bottom: 44px;
    }
    .page-header .label {
      font-size: .74rem; font-weight: 600;
      letter-spacing: 3px; text-transform: uppercase;
      color: var(--brown-light); margin-bottom: 10px;
    }
    .page-header h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2rem, 4vw, 2.8rem);
      font-weight: 600; color: var(--brown-dark);
      margin-bottom: 10px;
    }
    .page-header p {
      color: var(--text-soft); font-size: .94rem;
      max-width: 500px; margin: 0 auto; line-height: 1.65;
    }

    /* ── Carte formulaire ── */
    .form-card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-card);
      border: 1.5px solid var(--beige-mid);
      overflow: hidden;
    }

    /* Sections du formulaire */
    .form-section {
      padding: 28px 32px;
      border-bottom: 1.5px solid var(--beige-light);
    }
    .form-section:last-child { border-bottom: none; }
    .form-section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; font-weight: 600;
      color: var(--brown-dark);
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 1.5px solid var(--beige-mid);
      display: flex; align-items: center; gap: 8px;
    }
    .form-section-title i { color: var(--brown-light); margin: 0; }

    /* Grilles */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .col-full { grid-column: 1 / -1; }

    /* ── Upload photo ── */
    .upload-zone {
      border: 2px dashed var(--beige-deep);
      border-radius: 12px;
      padding: 40px 20px;
      text-align: center;
      cursor: pointer;
      transition: all .25s;
      background: var(--beige-light);
      position: relative;
    }
    .upload-zone:hover {
      border-color: var(--brown-light);
      background: var(--beige-mid);
    }
    .upload-zone.has-file {
      border-color: var(--brown-mid);
      background: #f0f9f0;
    }
    .upload-zone input[type=file] {
      position: absolute; inset: 0;
      opacity: 0; cursor: pointer;
      width: 100%; height: 100%;
    }
    .upload-icon {
      font-size: 2.5rem;
      color: var(--brown-light);
      margin-bottom: 12px;
      display: block;
    }
    .upload-icon i { margin: 0; }
    .upload-text { color: var(--text-mid); font-size: .92rem; margin-bottom: 6px; }
    .upload-text strong { color: var(--brown-mid); }
    .upload-hint { color: var(--text-soft); font-size: .78rem; }
    .upload-preview {
      display: none;
      margin-top: 16px;
    }
    .upload-preview img {
      width: 100%; max-height: 220px;
      object-fit: cover; border-radius: 9px;
    }

    /* ── Équipements checkboxes ── */
    .equip-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
    }
    .equip-check {
      display: flex; flex-direction: column;
      align-items: center; gap: 8px;
      padding: 16px 10px;
      border: 2px solid var(--beige-deep);
      border-radius: 10px;
      cursor: pointer;
      transition: all .2s;
      text-align: center;
      font-size: .82rem; color: var(--text-mid);
      font-weight: 500;
      position: relative;
    }
    .equip-check input[type=checkbox] {
      position: absolute; opacity: 0;
    }
    .equip-check i {
      font-size: 1.4rem;
      color: var(--text-soft);
      margin: 0;
      display: block;
      transition: color .2s;
    }
    .equip-check:hover {
      border-color: var(--brown-light);
      background: var(--beige-light);
    }
    .equip-check.checked {
      border-color: var(--brown-mid);
      background: var(--beige-mid);
      color: var(--brown-dark);
    }
    .equip-check.checked i { color: var(--brown-mid); }

    /* ── Alertes ── */
    .alert-success {
      background: #e8f5e9; color: #2e7d32;
      border: 1px solid #a5d6a7;
      border-radius: 10px; padding: 16px 20px;
      font-size: .92rem; margin-bottom: 24px;
      display: flex; align-items: flex-start; gap: 10px;
    }
    .alert-success i { font-size: 1.2rem; margin: 0; flex-shrink: 0; }
    .alert-error {
      background: #ffebee; color: #c62828;
      border: 1px solid #ef9a9a;
      border-radius: 10px; padding: 16px 20px;
      font-size: .88rem; margin-bottom: 24px;
    }
    .alert-error ul { margin: 8px 0 0 18px; }
    .alert-error li { margin-bottom: 4px; }

    /* ── Footer form ── */
    .form-footer {
      padding: 24px 32px;
      background: var(--beige-light);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .form-footer p {
      font-size: .83rem;
      color: var(--text-soft);
      max-width: 400px;
      line-height: 1.55;
    }

    /* ── Responsive ── */
    @media (max-width: 700px) {
      .grid-2, .grid-3 { grid-template-columns: 1fr; }
      .equip-grid { grid-template-columns: repeat(2, 1fr); }
      .form-section { padding: 20px; }
      .form-footer { flex-direction: column; align-items: stretch; }
    }
     .nav-logo {
      display: flex;
      align-items: center;
      font-weight: bold;
      font-size: 1.2rem;
      text-decoration: none;
    }

    .nav-flag {
      width: 20px;
      
      border-radius: 2px;
      object-fit: cover;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a href="index.php" class="nav-logo"><span>Sen</span> Location </a>
  <div class="nav-links">
    <a href="index.php"><i class="fa-solid fa-house"></i> Accueil</a>
    <a href="index.php#logements"><i class="fa-solid fa-building"></i> Logements</a>
  </div>
  <div class="nav-actions">
    <div class="nav-dropdown">
      <button class="btn btn-outline">
        <i class="fa-solid fa-user"></i>
        <?= htmlspecialchars($user_nom) ?>
        <i class="fa-solid fa-chevron-down" style="margin-left:6px;font-size:.75rem;margin-right:0"></i>
      </button>
      <div class="nav-dropdown-menu">
        <a href="profil.php"><i class="fa-solid fa-user"></i> Mon profil</a>
        <a href="profil.php?tab=reservations"><i class="fa-solid fa-calendar-check"></i> Mes réservations</a>
        <a href="ajouter-logement.php"><i class="fa-solid fa-plus"></i> Ajouter un logement</a>
        <a href="#" class="sep" onclick="logout(); return false;">
          <i class="fa-solid fa-right-from-bracket"></i> Se déconnecter
        </a>
      </div>
    </div>
  </div>
</nav>


<div class="page-wrap">

  <!-- En-tête -->
  <div class="page-header">
    <p class="label"><i class="fa-solid fa-building"></i> Propriétaires</p>
    <h1>Ajouter votre logement</h1>
    <p>Remplissez le formulaire ci-dessous. Votre logement sera visible immédiatement sur Sen Location.</p>
  </div>

  <!-- Messages -->
  <?php if ($success): ?>
    <div class="alert-success">
      <i class="fa-solid fa-circle-check"></i>
      <div>
        <strong><?= htmlspecialchars($success) ?></strong><br>
        <a href="index.php#logements" style="color:#2e7d32;font-size:.87rem">
          <i class="fa-solid fa-arrow-right"></i> Voir les logements
        </a>
        &nbsp;·&nbsp;
        <a href="ajouter-logement.php" style="color:#2e7d32;font-size:.87rem">
          <i class="fa-solid fa-plus"></i> Ajouter un autre
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert-error">
      <strong><i class="fa-solid fa-circle-xmark"></i> Corrigez les erreurs suivantes :</strong>
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Formulaire -->
  <form method="POST" enctype="multipart/form-data" id="formAjout">
    <div class="form-card">

      <!-- ── 1. Photo ── -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fa-solid fa-camera"></i> Photo du logement *
        </div>
        <div class="upload-zone" id="uploadZone">
          <input type="file" name="image" id="imageInput"
                 accept="image/jpeg,image/png,image/webp,image/gif">
          <span class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
          <div class="upload-text">
            <strong>Cliquez pour choisir une photo</strong> ou glissez-la ici
          </div>
          <div class="upload-hint">JPG, PNG, WEBP · Max 5 Mo</div>
          <div class="upload-preview" id="uploadPreview">
            <img id="previewImg" src="" alt="Aperçu">
          </div>
        </div>
      </div>

      <!-- ── 2. Informations générales ── -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fa-solid fa-circle-info"></i> Informations générales
        </div>
        <div class="grid-2">
          <div class="form-group col-full">
            <label><i class="fa-solid fa-heading"></i> Titre du logement *</label>
            <input type="text" name="titre"
                   placeholder="ex : Appartement moderne aux Almadies"
                   value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                   required>
          </div>
          <div class="form-group">
            <label><i class="fa-solid fa-map-pin"></i> Ville *</label>
            <select name="ville_id" required>
              <option value="">-- Choisir une ville --</option>
              <?php foreach ($villes as $v): ?>
                <option value="<?= $v['id'] ?>"
                  <?= (($_POST['ville_id'] ?? '') == $v['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($v['nom']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label><i class="fa-solid fa-tag"></i> Type de logement *</label>
            <select name="type" required>
              <option value="appartement" <?= (($_POST['type'] ?? '') === 'appartement') ? 'selected' : '' ?>>Appartement</option>
              <option value="villa"       <?= (($_POST['type'] ?? '') === 'villa')       ? 'selected' : '' ?>>Villa</option>
              <option value="studio"      <?= (($_POST['type'] ?? '') === 'studio')      ? 'selected' : '' ?>>Studio</option>
              <option value="maison"      <?= (($_POST['type'] ?? '') === 'maison')      ? 'selected' : '' ?>>Maison</option>
            </select>
          </div>
          <div class="form-group col-full">
            <label><i class="fa-solid fa-location-dot"></i> Adresse précise</label>
            <input type="text" name="adresse"
                   placeholder="ex : Rue 10, Almadies, Dakar"
                   value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
          </div>
          <div class="form-group col-full">
            <label><i class="fa-solid fa-align-left"></i> Description *</label>
            <textarea name="description" rows="4"
                      placeholder="Décrivez votre logement : vue, ambiance, proximité des commerces, transports…"
                      required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- ── 3. Capacité & Prix ── -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fa-solid fa-sliders"></i> Capacité & Prix
        </div>
        <div class="grid-3">
          <div class="form-group">
            <label><i class="fa-solid fa-bed"></i> Chambres *</label>
            <input type="number" name="nb_chambres" min="1" max="20"
                   value="<?= htmlspecialchars($_POST['nb_chambres'] ?? '1') ?>" required>
          </div>
          <div class="form-group">
            <label><i class="fa-solid fa-bath"></i> Salles de bain *</label>
            <input type="number" name="nb_bains" min="1" max="10"
                   value="<?= htmlspecialchars($_POST['nb_bains'] ?? '1') ?>" required>
          </div>
          <div class="form-group">
            <label><i class="fa-solid fa-users"></i> Capacité max *</label>
            <input type="number" name="capacite" min="1" max="50"
                   value="<?= htmlspecialchars($_POST['capacite'] ?? '2') ?>" required>
          </div>
          <div class="form-group col-full">
            <label><i class="fa-solid fa-money-bill-wave"></i> Prix par nuit (FCFA) *</label>
            <input type="number" name="prix_nuit" min="1000" step="500"
                   placeholder="ex : 45000"
                   value="<?= htmlspecialchars($_POST['prix_nuit'] ?? '') ?>"
                   required>
          </div>
        </div>
      </div>

      <!-- ── 4. Équipements ── -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fa-solid fa-star"></i> Équipements disponibles
        </div>
        <div class="equip-grid">
          <label class="equip-check <?= isset($_POST['wifi'])    ? 'checked' : '' ?>">
            <input type="checkbox" name="wifi"    <?= isset($_POST['wifi'])    ? 'checked' : '' ?>>
            <i class="fa-solid fa-wifi"></i>
            WiFi
          </label>
          <label class="equip-check <?= isset($_POST['clim'])    ? 'checked' : '' ?>">
            <input type="checkbox" name="clim"    <?= isset($_POST['clim'])    ? 'checked' : '' ?>>
            <i class="fa-solid fa-snowflake"></i>
            Climatisation
          </label>
          <label class="equip-check <?= isset($_POST['piscine']) ? 'checked' : '' ?>">
            <input type="checkbox" name="piscine" <?= isset($_POST['piscine']) ? 'checked' : '' ?>>
            <i class="fa-solid fa-person-swimming"></i>
            Piscine
          </label>
          <label class="equip-check <?= isset($_POST['parking']) ? 'checked' : '' ?>">
            <input type="checkbox" name="parking" <?= isset($_POST['parking']) ? 'checked' : '' ?>>
            <i class="fa-solid fa-square-parking"></i>
            Parking
          </label>
        </div>
      </div>

      <!-- ── 5. Règles & Contact ── -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fa-solid fa-list-check"></i> Règles & Contact bailleur
        </div>
        <div class="grid-2">
          <div class="form-group col-full">
            <label><i class="fa-solid fa-clipboard-list"></i> Règles & Consignes</label>
            <textarea name="consignes" rows="4"
                      placeholder="Non-fumeur, animaux non autorisés, check-in après 14h…"><?= htmlspecialchars($_POST['consignes'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label><i class="fa-solid fa-phone"></i> Numéro du bailleur *</label>
            <input type="tel" name="proprio_tel"
                   placeholder="77 000 00 00"
                   value="<?= htmlspecialchars($_POST['proprio_tel'] ?? '') ?>"
                   required>
          </div>
          <div class="form-group">
            <label><i class="fa-solid fa-user"></i> Nom du bailleur</label>
            <input type="text" name="proprio_nom"
                   placeholder="Prénom Nom"
                   value="<?= htmlspecialchars($_POST['proprio_nom'] ?? $user_nom) ?>">
          </div>
        </div>
      </div>

      <!-- Footer formulaire -->
      <div class="form-footer">
        <p>
          <i class="fa-solid fa-shield-halved" style="color:var(--brown-light)"></i>
          Votre logement sera visible immédiatement après soumission.
          L'équipe Sen Location se réserve le droit de le retirer si les informations sont incorrectes.
        </p>
        <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
          <i class="fa-solid fa-paper-plane"></i> Publier le logement
        </button>
      </div>

    </div>
  </form>
</div>


<!-- FOOTER -->
<footer id="contact" style="margin-top:60px">
  <div class="footer-inner">
   <div class="footer-logo">Sen Location  <img src="images/senegal.jpg" alt="Sénégal" class="nav-flag"></div>
    <div class="footer-links">
      <a href="#"><i class="fa-solid fa-circle-question"></i> FAQ</a>
      <a href="#"><i class="fa-solid fa-headset"></i> Assistance</a>
      <a href="ajouter-logement.php"><i class="fa-solid fa-plus"></i> Ajouter un logement</a>
    </div>
    <div class="footer-copy">© 2024 Sen Location · Tous droits réservés</div>
  </div>
</footer>


<script src="assets/js/main.js"></script>
<script>
  // Navbar scroll
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

  // ── Aperçu image ──
  const input   = document.getElementById('imageInput');
  const zone    = document.getElementById('uploadZone');
  const preview = document.getElementById('uploadPreview');
  const img     = document.getElementById('previewImg');

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      preview.style.display = 'block';
      zone.classList.add('has-file');
      zone.querySelector('.upload-icon').style.display = 'none';
      zone.querySelector('.upload-text').innerHTML =
        '<strong style="color:var(--brown-mid)"><i class="fa-solid fa-circle-check"></i> ' +
        file.name + '</strong>';
      zone.querySelector('.upload-hint').textContent =
        (file.size / 1024 / 1024).toFixed(2) + ' Mo';
    };
    reader.readAsDataURL(file);
  });

  // ── Toggle équipements ──
  document.querySelectorAll('.equip-check').forEach(label => {
    label.addEventListener('click', () => {
      const cb = label.querySelector('input[type=checkbox]');
      // Le click natif va cocher/décocher — on sync juste la classe
      setTimeout(() => {
        label.classList.toggle('checked', cb.checked);
      }, 0);
    });
  });

  // ── Loading au submit ──
  document.getElementById('formAjout').addEventListener('submit', () => {
    const btn = document.getElementById('btnSubmit');
    btn.innerHTML = '<span class="spinner"></span> Publication en cours…';
    btn.disabled  = true;
  });
</script>

</body>
</html>
