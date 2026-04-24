<?php
session_start();
$user_nom  = $_SESSION['user_nom']  ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$user_id   = $_SESSION['user_id']   ?? null;

// Données statiques des logements (en attendant la DB)
$logements = [
  1 => [
    'id'          => 1,
    'titre'       => 'Appartement Moderne — Dakar',
    'adresse'     => 'Almadies, Dakar',
    'type'        => 'Appartement',
    'statut'      => 'disponible',
    'prix_nuit'   => 45000,
    'nb_chambres' => 2,
    'nb_bains'    => 1,
    'capacite'    => 4,
    'note'        => 4.9,
    'nb_avis'     => 32,
    'description' => 'Bel appartement moderne et lumineux situé aux Almadies, l\'un des quartiers les plus prisés de Dakar. Vue partielle sur l\'océan Atlantique. Idéal pour un séjour en famille ou entre amis.',
    'consignes'   => "Non-fumeur à l'intérieur\nAnimaux non autorisés\nCheck-in après 14h00\nCheck-out avant 11h00\nPas de fête ni de soirée bruyante",
    'wifi'        => true,
    'clim'        => true,
    'piscine'     => false,
    'parking'     => true,
    'images'      => ['images/Ap.jpg'],
    'proprio'     => 'Abdoulaye Ba',
    'proprio_tel' => '+221 77 889 02 34',
  ],
  2 => [
    'id'          => 2,
    'titre'       => 'Villa de Charme — Saly',
    'adresse'     => 'Saly Portudal',
    'type'        => 'Villa',
    'statut'      => 'occupe',
    'date_dispo'  => '12 Mai 2025',
    'prix_nuit'   => 120000,
    'nb_chambres' => 4,
    'nb_bains'    => 3,
    'capacite'    => 8,
    'note'        => 4.7,
    'nb_avis'     => 18,
    'description' => 'Superbe villa avec piscine privée à Saly Portudal. 4 chambres spacieuses, jacuzzi, grande terrasse et jardin tropical. Parfaite pour les familles ou les groupes d\'amis.',
    'consignes'   => "Respect du voisinage après 22h00\nPiscine ouverte de 8h à 20h\nPas de fête sans accord préalable\nMénage inclus tous les 2 jours",
    'wifi'        => true,
    'clim'        => true,
    'piscine'     => true,
    'parking'     => true,
    'images'      => ['images/villa.jpg'],
    'proprio'     => 'Abdoulaye Ba',
    'proprio_tel' => '+221 77 889 02 34',
  ],
  3 => [
    'id'          => 3,
    'titre'       => 'Studio Cosy — Thiès',
    'adresse'     => 'Centre-ville, Thiès',
    'type'        => 'Studio',
    'statut'      => 'disponible',
    'prix_nuit'   => 25000,
    'nb_chambres' => 1,
    'nb_bains'    => 1,
    'capacite'    => 2,
    'note'        => 4.5,
    'nb_avis'     => 9,
    'description' => 'Studio moderne et tout équipé en plein centre-ville de Thiès. Proche des commerces, transports et restaurants. Idéal pour un séjour solo ou en couple.',
    'consignes'   => "Ménage à la charge du locataire\nPas de bruit après 22h\nNon-fumeur",
    'wifi'        => true,
    'clim'        => false,
    'piscine'     => false,
    'parking'     => false,
    'images'      => ['images/studio.webp'],
   'proprio'     => 'Abdoulaye Ba',
   'proprio_tel' => '+221 77 889 02 34',
  ],
  4 => [
    'id'          => 4,
    'titre'       => 'Penthouse Vue Océan — Dakar',
    'adresse'     => 'Plateau, Dakar',
    'type'        => 'Appartement',
    'statut'      => 'disponible',
    'prix_nuit'   => 95000,
    'nb_chambres' => 3,
    'nb_bains'    => 2,
    'capacite'    => 6,
    'note'        => 5.0,
    'nb_avis'     => 47,
    'description' => 'Penthouse d\'exception au Plateau avec vue panoramique sur l\'océan Atlantique. Grande terrasse, décoration haut de gamme, cuisine équipée. L\'adresse ultime à Dakar.',
    'consignes'   => "Non-fumeur\nCheck-in après 15h00\nCheck-out avant 10h00\nDépôt de garantie requis",
    'wifi'        => true,
    'clim'        => true,
    'piscine'     => false,
    'parking'     => true,
    'images'      => ['images/penthouse.webp'],
    'proprio'     => 'Abdoulaye Ba',
    'proprio_tel' => '+221 77 889 02 34',
  ],
  5 => [
    'id'          => 5,
    'titre'       => 'Maison Tropicale — Ziguinchor',
    'adresse'     => 'Ziguinchor, Casamance',
    'type'        => 'Maison',
    'statut'      => 'sous_location',
    'date_dispo'  => '3 Juin 2026',
    'prix_nuit'   => 55000,
    'nb_chambres' => 3,
    'nb_bains'    => 2,
    'capacite'    => 6,
    'note'        => 4.6,
    'nb_avis'     => 14,
    'description' => 'Belle maison tropicale entourée de verdure en Casamance. Jardin, piscine, grand salon. Idéale pour découvrir le sud du Sénégal dans un cadre authentique et reposant.',
    'consignes'   => "Respect de l'environnement\nAnimaux acceptés sous conditions\nPas de musique forte après 21h",
    'wifi'        => true,
    'clim'        => false,
    'piscine'     => true,
    'parking'     => true,
    'images'      => ['images/maison.jpg'],
    'proprio'     => 'Abdoulaye Ba',
    'proprio_tel' => '+221 77 889 02 34',
  ],
  6 => [
    'id'          => 6,
    'titre'       => 'Maison Coloniale — Saint-Louis',
    'adresse'     => 'Île de Saint-Louis',
    'type'        => 'Maison',
    'statut'      => 'disponible',
    'prix_nuit'   => 38000,
    'nb_chambres' => 2,
    'nb_bains'    => 1,
    'capacite'    => 4,
    'note'        => 4.8,
    'nb_avis'     => 27,
    'description' => 'Magnifique maison coloniale sur l\'île de Saint-Louis, classée au patrimoine mondial de l\'UNESCO. Architecture typique, jardin fleuri, ambiance unique entre fleuve et océan.',
    'consignes'   => "Respect du patrimoine\nNon-fumeur\nCheck-in après 13h00",
    'wifi'        => true,
    'clim'        => false,
    'piscine'     => false,
    'parking'     => false,
    'images'      => ['images/saintlouis.jpg'],
   'proprio'     => 'Abdoulaye Ba',
   'proprio_tel' => '+221 77 889 02 34',
  ],
];

// Récupérer l'ID depuis l'URL
$id  = (int)($_GET['id'] ?? 0);
$log = $logements[$id] ?? null;

// Si logement introuvable → retour accueil
if (!$log) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($log['titre']) ?> – Sen Location</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    i { margin-right: 5px; }

    /* ── Page layout ── */
    .page-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 100px 48px 60px;
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 48px;
      align-items: start;
    }

    /* ── Image principale ── */
    .detail-img-wrap {
      border-radius: var(--radius);
      overflow: hidden;
      height: 420px;
      background: var(--beige-mid);
      margin-bottom: 32px;
      position: relative;
    }
    .detail-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
    }
    .detail-img-badge {
      position: absolute;
      top: 16px; left: 16px;
    }

    /* ── Infos logement ── */
    .detail-type {
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--brown-light);
      margin-bottom: 8px;
    }
    .detail-titre {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(1.8rem, 3vw, 2.4rem);
      font-weight: 600;
      color: var(--brown-dark);
      margin-bottom: 10px;
      line-height: 1.15;
    }
    .detail-meta {
      display: flex;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
      font-size: .88rem;
      color: var(--text-mid);
    }
    .detail-meta .rating i { color: #f59e0b; margin: 0; }
    .detail-meta .location i { color: var(--brown-light); }

    /* ── Équipements ── */
    .equip-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin: 24px 0;
    }
    .equip-item {
      background: var(--beige-light);
      border: 1.5px solid var(--beige-mid);
      border-radius: 10px;
      padding: 12px 10px;
      text-align: center;
      font-size: .82rem;
      color: var(--text-mid);
      font-weight: 500;
    }
    .equip-item i {
      display: block;
      font-size: 1.3rem;
      color: var(--brown-mid);
      margin: 0 0 6px 0;
    }

    /* ── Description ── */
    .detail-section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--brown-dark);
      margin: 28px 0 12px;
      padding-bottom: 8px;
      border-bottom: 1.5px solid var(--beige-mid);
    }
    .detail-desc {
      color: var(--text-mid);
      font-size: .92rem;
      line-height: 1.80;
      font-weight: 300;
    }

    /* ── Consignes ── */
    .consignes-box {
      background: var(--beige-light);
      border-left: 4px solid var(--brown-light);
      border-radius: 0 10px 10px 0;
      padding: 16px 20px;
      font-size: .88rem;
      color: var(--text-mid);
      line-height: 1.8;
    }
    .consigne-ligne {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      margin-bottom: 6px;
    }
    .consigne-ligne i {
      color: var(--brown-mid);
      margin: 2px 0 0 0;
      flex-shrink: 0;
    }

    /* ── Contact proprio ── */
    .proprio-box {
      display: flex;
      align-items: center;
      gap: 16px;
      background: var(--beige-mid);
      border-radius: 12px;
      padding: 16px 20px;
      margin-top: 28px;
    }
    .proprio-avatar {
      width: 50px; height: 50px;
      background: var(--brown-mid);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 1.3rem; flex-shrink: 0;
    }
    .proprio-avatar i { margin: 0; }
    .proprio-nom { font-weight: 600; color: var(--brown-dark); font-size: .94rem; }
    .proprio-label { font-size: .78rem; color: var(--text-soft); margin-top: 2px; }

    /* ── Sidebar réservation ── */
    .resa-card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-card);
      padding: 28px 24px;
      position: sticky;
      top: 90px;
      border: 1.5px solid var(--beige-mid);
    }
    .resa-prix {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      font-weight: 600;
      color: var(--brown-dark);
      margin-bottom: 6px;
    }
    .resa-prix span {
      font-size: .85rem;
      font-family: 'DM Sans', sans-serif;
      color: var(--text-soft);
      font-weight: 400;
    }
    .resa-rating {
      font-size: .84rem;
      color: var(--text-soft);
      margin-bottom: 22px;
      padding-bottom: 20px;
      border-bottom: 1.5px solid var(--beige-mid);
    }
    .resa-rating i { color: #f59e0b; margin: 0; }

    /* Dates */
    .dates-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 14px;
    }

    /* Résumé prix */
    .resa-summary {
      background: var(--beige-light);
      border-radius: 10px;
      padding: 14px 16px;
      margin: 16px 0;
      display: none;
    }
    .resa-summary.visible { display: block; }
    .resa-row {
      display: flex;
      justify-content: space-between;
      font-size: .86rem;
      color: var(--text-mid);
      padding: 4px 0;
    }
    .resa-row.total {
      font-weight: 600;
      color: var(--brown-dark);
      border-top: 1px solid var(--beige-deep);
      margin-top: 8px;
      padding-top: 10px;
      font-size: .94rem;
    }

    /* Paiement */
    .pay-methods {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 8px;
      margin-bottom: 16px;
    }
    .pay-method {
      border: 2px solid var(--beige-deep);
      border-radius: 10px;
      padding: 10px 6px;
      text-align: center;
      cursor: pointer;
      transition: all .2s;
      background: #fff;
      font-size: .76rem;
      color: var(--text-mid);
      font-weight: 500;
    }
    .pay-method i { display: block; font-size: 1.3rem; margin: 0 0 5px 0; color: var(--brown-light); }
    .pay-method:hover { border-color: var(--brown-light); background: var(--beige-light); }
    .pay-method.selected { border-color: var(--brown-mid); background: var(--beige-mid); color: var(--brown-dark); }
    .pay-method.selected i { color: var(--brown-mid); }

    /* Statut indisponible */
    .indispo-box {
      background: #fff3e0;
      border: 1.5px solid #ffcc80;
      border-radius: 10px;
      padding: 16px;
      text-align: center;
      color: #e65100;
      font-size: .90rem;
    }
    .indispo-box i { font-size: 1.5rem; display: block; margin: 0 0 8px 0; }
    .indispo-box strong { display: block; margin-bottom: 4px; }

    /* Message dispo */
    #dispo-msg {
      font-size: .84rem;
      margin-bottom: 10px;
      min-height: 20px;
      text-align: center;
    }
    .dispo-ok  { color: #2e7d32; }
    .dispo-non { color: #c62828; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .page-wrap {
        grid-template-columns: 1fr;
        padding: 80px 20px 40px;
        gap: 32px;
      }
      .resa-card { position: static; }
      .equip-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 500px) {
      .pay-methods { grid-template-columns: 1fr 1fr; }
      .dates-grid  { grid-template-columns: 1fr; }
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
  <a href="index.php" class="nav-logo"><span>Sen</span> Location  <img src="images/senegal.jpg" alt="Sénégal" class="nav-flag"></a>
  <div class="nav-links">
    <a href="index.php"><i class="fa-solid fa-house"></i> Accueil</a>
    <a href="index.php#logements"><i class="fa-solid fa-building"></i> Logements</a>
    <a href="index.php#contact"><i class="fa-solid fa-envelope"></i> Contact</a>
  </div>
  <div class="nav-actions">
    <?php if ($user_nom): ?>
      <div class="nav-dropdown">
        <button class="btn btn-outline">
          <i class="fa-solid fa-user"></i>
          <?= htmlspecialchars($user_nom) ?>
          <i class="fa-solid fa-chevron-down" style="margin-left:6px;font-size:.75rem;margin-right:0"></i>
        </button>
        <div class="nav-dropdown-menu">
          <a href="profil.php"><i class="fa-solid fa-user"></i> Mon profil</a>
          <a href="profil.php?tab=reservations"><i class="fa-solid fa-calendar-check"></i> Mes réservations</a>
          <?php if (in_array($user_role, ['admin','proprietaire'])): ?>
            <a href="admin/"><i class="fa-solid fa-gear"></i> Administration</a>
          <?php endif; ?>
          <a href="#" class="sep" onclick="logout(); return false;">
            <i class="fa-solid fa-right-from-bracket"></i> Se déconnecter
          </a>
        </div>
      </div>
    <?php else: ?>
      <button class="btn btn-outline" onclick="openModal('login')">
        <i class="fa-solid fa-right-to-bracket"></i> Se Connecter
      </button>
      <button class="btn btn-primary" onclick="openModal('register')">
        <i class="fa-solid fa-user-plus"></i> S'inscrire
      </button>
    <?php endif; ?>
  </div>
</nav>


<!-- CONTENU PRINCIPAL -->
<div class="page-wrap">

  <!-- ── COLONNE GAUCHE : Infos ── -->
  <div>

    <!-- Image principale -->
    <div class="detail-img-wrap">
      <img src="<?= htmlspecialchars($log['images'][0]) ?>"
           alt="<?= htmlspecialchars($log['titre']) ?>">
      <div class="detail-img-badge">
        <?php
          $badges = [
            'disponible'    => ['badge-free', 'fa-circle-check',  'Disponible'],
            'occupe'        => ['badge-busy', 'fa-clock',         'Occupé'],
            'sous_location' => ['badge-sub',  'fa-rotate',        'Sous-location'],
          ];
          $b = $badges[$log['statut']] ?? ['badge-free','fa-circle-check','Disponible'];
        ?>
        <span class="badge <?= $b[0] ?>">
          <i class="fa-solid <?= $b[1] ?>"></i> <?= $b[2] ?>
        </span>
      </div>
    </div>

    <!-- Type + Titre -->
    <div class="detail-type">
      <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($log['type']) ?>
    </div>
    <h1 class="detail-titre"><?= htmlspecialchars($log['titre']) ?></h1>

    <!-- Meta -->
    <div class="detail-meta">
      <span class="rating">
        <i class="fa-solid fa-star"></i>
        <strong><?= $log['note'] ?></strong>
        <span style="color:var(--text-soft)">(<?= $log['nb_avis'] ?> avis)</span>
      </span>
      <span class="location">
        <i class="fa-solid fa-location-dot"></i>
        <?= htmlspecialchars($log['adresse']) ?>
      </span>
      <span>
        <i class="fa-solid fa-users"></i>
        <?= $log['capacite'] ?> personnes max
      </span>
    </div>

    <!-- Équipements -->
    <div class="equip-grid">
      <div class="equip-item">
        <i class="fa-solid fa-bed"></i>
        <?= $log['nb_chambres'] ?> chambre<?= $log['nb_chambres'] > 1 ? 's' : '' ?>
      </div>
      <div class="equip-item">
        <i class="fa-solid fa-bath"></i>
        <?= $log['nb_bains'] ?> salle<?= $log['nb_bains'] > 1 ? 's' : '' ?> de bain
      </div>
      <div class="equip-item" style="<?= !$log['wifi'] ? 'opacity:.4' : '' ?>">
        <i class="fa-solid fa-wifi"></i>
        WiFi<?= !$log['wifi'] ? ' ✗' : '' ?>
      </div>
      <div class="equip-item" style="<?= !$log['clim'] ? 'opacity:.4' : '' ?>">
        <i class="fa-solid fa-snowflake"></i>
        Clim<?= !$log['clim'] ? ' ✗' : '' ?>
      </div>
      <div class="equip-item" style="<?= !$log['piscine'] ? 'opacity:.4' : '' ?>">
        <i class="fa-solid fa-person-swimming"></i>
        Piscine<?= !$log['piscine'] ? ' ✗' : '' ?>
      </div>
      <div class="equip-item" style="<?= !$log['parking'] ? 'opacity:.4' : '' ?>">
        <i class="fa-solid fa-square-parking"></i>
        Parking<?= !$log['parking'] ? ' ✗' : '' ?>
      </div>
    </div>

    <!-- Description -->
    <div class="detail-section-title">
      <i class="fa-solid fa-align-left"></i> Description
    </div>
    <p class="detail-desc"><?= nl2br(htmlspecialchars($log['description'])) ?></p>

    <!-- Consignes -->
    <div class="detail-section-title">
      <i class="fa-solid fa-list-check"></i> Règles & Consignes
    </div>
    <div class="consignes-box">
      <?php foreach (explode("\n", $log['consignes']) as $ligne): ?>
        <?php if (trim($ligne)): ?>
        <div class="consigne-ligne">
          <i class="fa-solid fa-circle-check"></i>
          <span><?= htmlspecialchars(trim($ligne)) ?></span>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Propriétaire -->
    <div class="proprio-box">
      <div class="proprio-avatar">
        <i class="fa-solid fa-user"></i>
      </div>
      <div style="flex:1">
        <div class="proprio-nom"><?= htmlspecialchars($log['proprio']) ?></div>
        <div class="proprio-label">Propriétaire</div>
      </div>
      <a href="tel:<?= htmlspecialchars($log['proprio_tel']) ?>"
         class="btn btn-outline" style="white-space:nowrap">
        <i class="fa-solid fa-phone"></i> Contacter
      </a>
    </div>

  </div><!-- /colonne gauche -->


  <!-- ── COLONNE DROITE : Réservation ── -->
  <div>
    <div class="resa-card">

      <!-- Prix -->
      <div class="resa-prix">
        <?= number_format($log['prix_nuit'], 0, ',', ' ') ?>
        <span>FCFA / nuit</span>
      </div>
      <div class="resa-rating">
        <i class="fa-solid fa-star"></i>
        <strong><?= $log['note'] ?></strong> ·
        <?= $log['nb_avis'] ?> avis
      </div>

      <?php if ($log['statut'] === 'disponible'): ?>

        <?php if ($user_id): ?>
        <!-- Formulaire réservation -->
        <form id="formResa">
          <input type="hidden" name="logement_id" value="<?= $log['id'] ?>">
          <input type="hidden" name="prix_nuit"   value="<?= $log['prix_nuit'] ?>">

          <!-- Dates -->
          <div class="dates-grid">
            <div class="form-group" style="margin:0">
              <label style="font-size:.78rem;font-weight:600;color:var(--text-mid);margin-bottom:5px;display:block">
                <i class="fa-solid fa-plane-arrival"></i> Arrivée
              </label>
              <input type="date" name="date_arrivee" id="date_arrivee" required
                     min="<?= date('Y-m-d') ?>"
                     style="padding:10px 12px;border:1.5px solid var(--beige-deep);border-radius:9px;font-family:inherit;font-size:.88rem;width:100%;outline:none">
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:.78rem;font-weight:600;color:var(--text-mid);margin-bottom:5px;display:block">
                <i class="fa-solid fa-plane-departure"></i> Départ
              </label>
              <input type="date" name="date_depart" id="date_depart" required
                     min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                     style="padding:10px 12px;border:1.5px solid var(--beige-deep);border-radius:9px;font-family:inherit;font-size:.88rem;width:100%;outline:none">
            </div>
          </div>

          <!-- Message disponibilité -->
          <div id="dispo-msg"></div>

          <!-- Résumé prix -->
          <div class="resa-summary" id="resa-summary">
            <div class="resa-row">
              <span id="resa-label-nuits">— nuits</span>
              <span id="resa-sous-total">—</span>
            </div>
            <div class="resa-row total">
              <span><i class="fa-solid fa-receipt"></i> Total</span>
              <span id="resa-total">—</span>
            </div>
          </div>

          <!-- Moyen de paiement -->
          <div class="form-group">
            <label style="font-size:.80rem;font-weight:600;color:var(--text-mid);margin-bottom:10px;display:block">
              <i class="fa-solid fa-credit-card"></i> Paiement
            </label>
            <div class="pay-methods">
              <div class="pay-method selected" data-val="wave" onclick="selectPay(this)">
                <i class="fa-solid fa-mobile-screen"></i> Wave
              </div>
              <div class="pay-method" data-val="orange_money" onclick="selectPay(this)">
                <i class="fa-solid fa-circle"></i> Orange Money
              </div>
              <div class="pay-method" data-val="especes" onclick="selectPay(this)">
                <i class="fa-solid fa-money-bill"></i> Espèces
              </div>
            </div>
            <input type="hidden" name="moyen_paiement" id="moyen_paiement" value="wave">
          </div>

          <!-- Notes -->
          <div class="form-group">
            <label style="font-size:.80rem;font-weight:600;color:var(--text-mid);margin-bottom:5px;display:block">
              <i class="fa-solid fa-comment"></i> Notes (optionnel)
            </label>
            <textarea name="notes" rows="2"
                      placeholder="Heure d'arrivée, demandes spéciales…"
                      style="width:100%;padding:10px 12px;border:1.5px solid var(--beige-deep);border-radius:9px;font-family:inherit;font-size:.87rem;resize:none;outline:none"></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-reserver">
            <i class="fa-solid fa-calendar-check"></i> Réserver maintenant
          </button>
        </form>

        <?php else: ?>
        <!-- Non connecté -->
        <div style="text-align:center;padding:10px 0">
          <p style="color:var(--text-soft);font-size:.90rem;margin-bottom:16px">
            <i class="fa-solid fa-lock"></i>
            Connectez-vous pour réserver ce logement.
          </p>
          <button class="btn btn-primary btn-block" onclick="openModal('login')">
            <i class="fa-solid fa-right-to-bracket"></i> Se connecter
          </button>
          <button class="btn btn-outline btn-block" onclick="openModal('register')"
                  style="margin-top:10px">
            <i class="fa-solid fa-user-plus"></i> Créer un compte
          </button>
        </div>
        <?php endif; ?>

      <?php else: ?>
      <!-- Logement indisponible -->
      <div class="indispo-box">
        <i class="fa-solid fa-clock"></i>
        <strong>Actuellement indisponible</strong>
        <?php if (!empty($log['date_dispo'])): ?>
          <span>Disponible à partir du <strong><?= htmlspecialchars($log['date_dispo']) ?></strong></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </div><!-- /colonne droite -->

</div><!-- /page-wrap -->


<!-- MODAL AUTH -->
<div class="modal-overlay" id="authModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="tab-switch">
      <button class="tab-btn active" id="tabLogin" onclick="switchTab('login')">
        <i class="fa-solid fa-right-to-bracket"></i> Se Connecter
      </button>
      <button class="tab-btn" id="tabRegister" onclick="switchTab('register')">
        <i class="fa-solid fa-user-plus"></i> S'inscrire
      </button>
    </div>
    <div id="formLogin">
      <form id="loginForm">
        <div class="form-group">
          <label><i class="fa-solid fa-envelope"></i> E-mail *</label>
          <input type="email" name="email" placeholder="votre@email.sn" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Mot de passe *</label>
          <input type="password" name="mot_de_passe" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fa-solid fa-right-to-bracket"></i> Se Connecter
        </button>
      </form>
    </div>
    <div id="formRegister" style="display:none">
      <form id="registerForm">
        <div class="form-group">
          <label><i class="fa-solid fa-user"></i> Prénom et Nom *</label>
          <input type="text" name="nom" placeholder="ex : Moussa Diallo" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-envelope"></i> E-mail *</label>
          <input type="email" name="email" placeholder="votre@email.sn" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-phone"></i> Téléphone</label>
          <input type="tel" name="telephone" placeholder="77 000 00 00">
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Mot de passe *</label>
          <input type="password" name="mot_de_passe" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Confirmer *</label>
          <input type="password" name="confirmer_mdp" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fa-solid fa-user-plus"></i> Créer mon compte
        </button>
      </form>
    </div>
  </div>
</div>


<script src="assets/js/main.js"></script>
<script>
  // ── Navbar scroll ──
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

  // ── Sélection moyen de paiement ──
  function selectPay(el) {
    document.querySelectorAll('.pay-method').forEach(m => m.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('moyen_paiement').value = el.dataset.val;
  }

  // ── Calcul automatique des nuits et du prix ──
  const prixNuit   = <?= $log['prix_nuit'] ?>;
  const arriveeIn  = document.getElementById('date_arrivee');
  const departIn   = document.getElementById('date_depart');
  const summary    = document.getElementById('resa-summary');
  const dispoMsg   = document.getElementById('dispo-msg');

  function calculer() {
    const a = arriveeIn?.value;
    const d = departIn?.value;
    if (!a || !d) return;

    const d1    = new Date(a);
    const d2    = new Date(d);
    const nuits = Math.round((d2 - d1) / 86400000);

    if (nuits <= 0) {
      dispoMsg.innerHTML = '<span class="dispo-non"><i class="fa-solid fa-xmark"></i> La date de départ doit être après l\'arrivée.</span>';
      summary.classList.remove('visible');
      return;
    }

    const total = nuits * prixNuit;
    document.getElementById('resa-label-nuits').textContent =
      nuits + ' nuit' + (nuits > 1 ? 's' : '') + ' × ' + prixNuit.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('resa-sous-total').textContent = total.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('resa-total').textContent      = total.toLocaleString('fr-FR') + ' FCFA';
    summary.classList.add('visible');

    dispoMsg.innerHTML = '<span class="dispo-ok"><i class="fa-solid fa-circle-check"></i> Disponible pour ces dates !</span>';
  }

  arriveeIn?.addEventListener('change', () => {
    // Empêcher départ avant arrivée
    if (arriveeIn.value) {
      const minDepart = new Date(arriveeIn.value);
      minDepart.setDate(minDepart.getDate() + 1);
      departIn.min = minDepart.toISOString().split('T')[0];
    }
    calculer();
  });
  departIn?.addEventListener('change', calculer);

  // ── Soumission réservation ──
  document.getElementById('formResa')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const a = arriveeIn.value;
    const d = departIn.value;

    if (!a || !d) {
      dispoMsg.innerHTML = '<span class="dispo-non"><i class="fa-solid fa-xmark"></i> Sélectionnez vos dates.</span>';
      return;
    }

    const btn = document.getElementById('btn-reserver');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner"></span> Réservation en cours…';

    const fd = new FormData(this);
    fd.append('action', 'creer');

    try {
      const res  = await fetch('api/reservation.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        dispoMsg.innerHTML = '<span class="dispo-ok"><i class="fa-solid fa-circle-check"></i> ' + data.message + '</span>';
        btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Réservé !';
        btn.style.background = '#2e7d32';
        setTimeout(() => { window.location.href = 'profil.php?tab=reservations'; }, 1500);
      } else {
        dispoMsg.innerHTML = '<span class="dispo-non"><i class="fa-solid fa-xmark"></i> ' + (data.message || 'Erreur.') + '</span>';
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Réserver maintenant';
      }
    } catch (err) {
      dispoMsg.innerHTML = '<span class="dispo-non"><i class="fa-solid fa-xmark"></i> Erreur réseau. Réessayez.</span>';
      btn.disabled  = false;
      btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Réserver maintenant';
    }
  });
</script>

</body>
</html>
