<?php
session_start();

// Rediriger si non connecté
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php?modal=login');
  exit;
}

$user_nom  = $_SESSION['user_nom']  ?? 'Utilisateur';
$user_role = $_SESSION['user_role'] ?? 'client';
$user_id   = $_SESSION['user_id'];

$tab = $_GET['tab'] ?? 'reservations';

// Connexion DB
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

// ── Mes réservations ──────────────────────────────────────
$reservations = [];
if ($tab === 'reservations') {
  $stmt = $pdo->prepare("
    SELECT r.*, l.titre, l.adresse, l.prix_nuit,
           v.nom AS ville_nom
    FROM reservations r
    JOIN logements l ON r.logement_id = l.id
    JOIN villes v    ON l.ville_id    = v.id
    WHERE r.client_id = ?
    ORDER BY r.created_at DESC
  ");
  $stmt->execute([$user_id]);
  $reservations = $stmt->fetchAll();
}

// ── Mise à jour profil ────────────────────────────────────
$success_msg = '';
$error_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profil') {
  $nom = trim(strip_tags($_POST['nom'] ?? ''));
  $tel = trim(strip_tags($_POST['telephone'] ?? ''));
  $mdp = $_POST['nouveau_mdp'] ?? '';

  if (!$nom) {
    $error_msg = 'Le nom est obligatoire.';
  } else {
    if ($mdp) {
      if (strlen($mdp) < 6) {
        $error_msg = 'Le mot de passe doit faire au moins 6 caractères.';
      } else {
        $hash = password_hash($mdp, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET nom=?, telephone=?, mot_de_passe=? WHERE id=?")
            ->execute([$nom, $tel, $hash, $user_id]);
      }
    } else {
      $pdo->prepare("UPDATE users SET nom=?, telephone=? WHERE id=?")
          ->execute([$nom, $tel, $user_id]);
    }
    if (!$error_msg) {
      $_SESSION['user_nom'] = $nom;
      $user_nom = $nom;
      $success_msg = 'Profil mis à jour avec succès !';
    }
  }
}

// ── Données utilisateur ───────────────────────────────────
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$userData = $userStmt->fetch();

// ── Couleurs statuts ──────────────────────────────────────
function statutStyle(string $s): array {
  return match($s) {
    'confirmee'  => ['#e8f5e9', '#2e7d32', 'fa-circle-check',  'Confirmée'],
    'annulee'    => ['#ffebee', '#c62828', 'fa-circle-xmark',  'Annulée'],
    'terminee'   => ['#e3f2fd', '#1565c0', 'fa-flag-checkered','Terminée'],
    default      => ['#fff3e0', '#e65100', 'fa-clock',         'En attente'],
  };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Espace – Sen Location</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    i { margin-right: 5px; }

    /* ── Layout ── */
    .profil-wrap {
      max-width: 1100px;
      margin: 0 auto;
      padding: 100px 48px 60px;
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 36px;
      align-items: start;
    }

    /* ── Sidebar ── */
    .profil-sidebar {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-card);
      overflow: hidden;
      position: sticky;
      top: 90px;
    }
    .sidebar-header {
      background: var(--brown-dark);
      padding: 28px 20px;
      text-align: center;
    }
    .sidebar-avatar {
      width: 64px; height: 64px;
      background: rgba(255,255,255,.15);
      border: 2px solid rgba(255,255,255,.3);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 12px;
      font-size: 1.6rem; color: #fff;
    }
    .sidebar-avatar i { margin: 0; }
    .sidebar-nom {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; color: #fff; font-weight: 600;
    }
    .sidebar-email { font-size: .78rem; color: rgba(255,255,255,.6); margin-top: 4px; }
    .sidebar-role {
      display: inline-block;
      margin-top: 10px;
      background: rgba(255,255,255,.15);
      border-radius: 50px;
      padding: 3px 12px;
      font-size: .72rem;
      color: var(--beige-deep);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .sidebar-nav { padding: 12px 0; }
    .sidebar-nav a {
      display: flex; align-items: center; gap: 10px;
      padding: 12px 20px;
      color: var(--text-mid);
      text-decoration: none;
      font-size: .88rem;
      transition: all .2s;
      border-left: 3px solid transparent;
    }
    .sidebar-nav a:hover { background: var(--beige-light); color: var(--brown-dark); }
    .sidebar-nav a.active {
      background: var(--beige-mid);
      color: var(--brown-dark);
      border-left-color: var(--brown-mid);
      font-weight: 600;
    }
    .sidebar-nav a i { color: var(--brown-light); margin: 0; width: 18px; text-align: center; }
    .sidebar-nav a.active i { color: var(--brown-mid); }
    .sidebar-nav .sep { border-top: 1px solid var(--beige-mid); margin: 8px 0; }
    .sidebar-nav .logout { color: #c62828 !important; }
    .sidebar-nav .logout i { color: #c62828 !important; }

    /* ── Contenu principal ── */
    .profil-main { min-width: 0; }
    .profil-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.9rem; font-weight: 600;
      color: var(--brown-dark);
      margin-bottom: 24px;
    }

    /* ── Alertes ── */
    .msg-success {
      background: #e8f5e9; color: #2e7d32;
      border: 1px solid #a5d6a7;
      border-radius: 9px; padding: 12px 16px;
      font-size: .88rem; margin-bottom: 20px;
    }
    .msg-error {
      background: #ffebee; color: #c62828;
      border: 1px solid #ef9a9a;
      border-radius: 9px; padding: 12px 16px;
      font-size: .88rem; margin-bottom: 20px;
    }

    /* ── Carte réservation ── */
    .resa-card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-soft);
      border: 1.5px solid var(--beige-mid);
      display: grid;
      grid-template-columns: 110px 1fr auto;
      gap: 18px;
      align-items: center;
      padding: 18px;
      margin-bottom: 16px;
      transition: box-shadow .2s;
    }
    .resa-card:hover { box-shadow: var(--shadow-card); }
    .resa-img {
      width: 110px; height: 80px;
      object-fit: cover;
      border-radius: 9px;
      background: var(--beige-mid);
    }
    .resa-img-placeholder {
      width: 110px; height: 80px;
      background: linear-gradient(135deg, var(--beige-deep), var(--brown-light));
      border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.8rem; color: #fff;
    }
    .resa-titre {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.05rem; font-weight: 600;
      color: var(--brown-dark); margin-bottom: 4px;
    }
    .resa-dates {
      font-size: .82rem; color: var(--text-soft); margin-bottom: 6px;
    }
    .resa-statut-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 50px;
      font-size: .72rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .4px;
    }
    .resa-statut-badge i { margin: 0; }
    .resa-prix-total {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.3rem; font-weight: 600;
      color: var(--brown-dark); text-align: right;
      white-space: nowrap;
    }
    .resa-nuits {
      font-size: .78rem; color: var(--text-soft);
      text-align: right; margin-top: 4px;
    }
    .resa-moyen {
      font-size: .76rem; color: var(--text-soft);
      text-align: right; margin-top: 4px;
    }

    /* Vide */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-soft);
      border: 1.5px solid var(--beige-mid);
    }
    .empty-state i {
      font-size: 3rem;
      color: var(--beige-deep);
      display: block;
      margin: 0 0 16px 0;
    }
    .empty-state p { color: var(--text-soft); margin-bottom: 20px; }

    /* ── Formulaire profil ── */
    .profil-form-card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-card);
      border: 1.5px solid var(--beige-mid);
      padding: 32px;
    }
    .form-2col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    /* ── Responsive ── */
    @media (max-width: 860px) {
      .profil-wrap { grid-template-columns: 1fr; padding: 80px 20px 40px; }
      .profil-sidebar { position: static; }
      .resa-card { grid-template-columns: 80px 1fr; }
      .resa-img, .resa-img-placeholder { width: 80px; height: 65px; }
      .resa-prix-total { display: none; }
    }
    @media (max-width: 500px) {
      .form-2col { grid-template-columns: 1fr; }
      .resa-card { grid-template-columns: 1fr; }
      .resa-img, .resa-img-placeholder { width: 100%; height: 140px; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a href="index.php" class="nav-logo"><span>Sen</span> Location</a>
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
        <?php if (in_array($user_role, ['admin','proprietaire'])): ?>
          <a href="admin/"><i class="fa-solid fa-gear"></i> Administration</a>
        <?php endif; ?>
        <a href="#" class="sep" onclick="logout(); return false;">
          <i class="fa-solid fa-right-from-bracket"></i> Se déconnecter
        </a>
      </div>
    </div>
  </div>
</nav>


<div class="profil-wrap">

  <!-- ── SIDEBAR ── -->
  <aside class="profil-sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar"><i class="fa-solid fa-user"></i></div>
      <div class="sidebar-nom"><?= htmlspecialchars($user_nom) ?></div>
      <div class="sidebar-email"><?= htmlspecialchars($userData['email'] ?? '') ?></div>
      <div class="sidebar-role"><?= htmlspecialchars($user_role) ?></div>
    </div>
    <nav class="sidebar-nav">
      <a href="profil.php?tab=reservations"
         class="<?= $tab === 'reservations' ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-check"></i> Mes réservations
      </a>
      <a href="profil.php?tab=profil"
         class="<?= $tab === 'profil' ? 'active' : '' ?>">
        <i class="fa-solid fa-user-pen"></i> Mon profil
      </a>
      <?php if (in_array($user_role, ['admin','proprietaire'])): ?>
      <div class="sep"></div>
      <a href="admin/">
        <i class="fa-solid fa-gauge"></i> Administration
      </a>
      <?php endif; ?>
      <div class="sep"></div>
      <a href="index.php">
        <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
      </a>
      <a href="#" class="logout" onclick="logout(); return false;">
        <i class="fa-solid fa-right-from-bracket"></i> Se déconnecter
      </a>
    </nav>
  </aside>


  <!-- ── CONTENU PRINCIPAL ── -->
  <div class="profil-main">

    <?php if ($success_msg): ?>
      <div class="msg-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
      <div class="msg-error"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="msg-success">
        <i class="fa-solid fa-circle-check"></i>
        Réservation confirmée ! Vous serez contacté pour les détails du paiement.
      </div>
    <?php endif; ?>


    <!-- ══ ONGLET : Réservations ══ -->
    <?php if ($tab === 'reservations'): ?>

      <h2 class="profil-title">
        <i class="fa-solid fa-calendar-check"></i> Mes réservations
      </h2>

      <?php if (empty($reservations)): ?>
        <div class="empty-state">
          <i class="fa-solid fa-calendar-xmark"></i>
          <p>Vous n'avez pas encore de réservation.</p>
          <a href="index.php#logements" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Parcourir les logements
          </a>
        </div>

      <?php else: ?>
        <?php foreach ($reservations as $r):
          [$bg, $color, $icon, $label] = statutStyle($r['statut']);
        ?>
        <div class="resa-card">

          <!-- Image -->
          <div class="resa-img-placeholder">
            <i class="fa-solid fa-house" style="margin:0"></i>
          </div>

          <!-- Infos -->
          <div>
            <div class="resa-titre"><?= htmlspecialchars($r['titre']) ?></div>
            <div class="resa-dates">
              <i class="fa-solid fa-calendar" style="color:var(--brown-light)"></i>
              <?= date('d/m/Y', strtotime($r['date_arrivee'])) ?>
              <i class="fa-solid fa-arrow-right" style="margin:0 4px;font-size:.75rem"></i>
              <?= date('d/m/Y', strtotime($r['date_depart'])) ?>
              · <?= $r['nb_nuits'] ?> nuit<?= $r['nb_nuits'] > 1 ? 's' : '' ?>
            </div>
            <span class="resa-statut-badge"
                  style="background:<?= $bg ?>;color:<?= $color ?>">
              <i class="fa-solid <?= $icon ?>"></i> <?= $label ?>
            </span>
          </div>

          <!-- Prix -->
          <div>
            <div class="resa-prix-total">
              <?= number_format($r['prix_total'], 0, ',', ' ') ?> FCFA
            </div>
            <div class="resa-nuits">
              <?= $r['nb_nuits'] ?> nuit<?= $r['nb_nuits'] > 1 ? 's' : '' ?>
            </div>
            <div class="resa-moyen">
              <i class="fa-solid fa-credit-card"></i>
              <?= htmlspecialchars($r['moyen_paiement']) ?>
            </div>
            <?php if ($r['statut'] === 'en_attente'): ?>
              <button class="btn btn-danger"
                      style="font-size:.78rem;padding:6px 12px;margin-top:8px"
                      onclick="annuler(<?= $r['id'] ?>)">
                <i class="fa-solid fa-xmark"></i> Annuler
              </button>
            <?php endif; ?>
          </div>

        </div>
        <?php endforeach; ?>
      <?php endif; ?>


    <!-- ══ ONGLET : Profil ══ -->
    <?php elseif ($tab === 'profil'): ?>

      <h2 class="profil-title">
        <i class="fa-solid fa-user-pen"></i> Mon profil
      </h2>

      <div class="profil-form-card">
        <form method="POST" action="profil.php?tab=profil">
          <input type="hidden" name="action" value="update_profil">

          <div class="form-2col">
            <div class="form-group">
              <label><i class="fa-solid fa-user"></i> Prénom et Nom *</label>
              <input type="text" name="nom"
                     value="<?= htmlspecialchars($userData['nom'] ?? '') ?>"
                     required>
            </div>
            <div class="form-group">
              <label><i class="fa-solid fa-envelope"></i> E-mail</label>
              <input type="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                     disabled style="opacity:.6;cursor:not-allowed">
            </div>
            <div class="form-group">
              <label><i class="fa-solid fa-phone"></i> Téléphone</label>
              <input type="tel" name="telephone"
                     value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>"
                     placeholder="77 000 00 00">
            </div>
          </div>

          <div style="border-top:1.5px solid var(--beige-mid);margin:24px 0 20px;padding-top:20px">
            <p style="font-size:.84rem;color:var(--text-soft);margin-bottom:16px">
              <i class="fa-solid fa-lock"></i>
              Changer le mot de passe
              <small>(laisser vide pour ne pas changer)</small>
            </p>
            <div class="form-2col">
              <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" placeholder="min. 6 caractères">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-floppy-disk"></i> Enregistrer
          </button>
        </form>
      </div>

    <?php endif; ?>

  </div><!-- /profil-main -->
</div><!-- /profil-wrap -->


<script src="assets/js/main.js"></script>
<script>
  // Navbar scroll
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

  // Annuler une réservation
  async function annuler(id) {
    if (!confirm('Annuler cette réservation ?')) return;

    const fd = new FormData();
    fd.append('action', 'annuler');
    fd.append('id', id);

    try {
      const res  = await fetch('api/reservation.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Erreur lors de l\'annulation.');
      }
    } catch {
      alert('Erreur réseau. Réessayez.');
    }
  }
</script>

</body>
</html>
