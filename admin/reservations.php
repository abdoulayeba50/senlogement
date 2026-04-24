<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php?modal=login'); exit; }
require_once __DIR__ . '/../includes/db.php';
$pdo = getDB();
$user_nom  = $_SESSION['user_nom']  ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['admin','proprietaire'])) { header('Location: ../index.php'); exit; }

// ── Confirmer paiement ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirmer') {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE reservations SET statut='confirmee', statut_paiement='paye' WHERE id=?")
        ->execute([$id]);
    header('Location: reservations.php?msg=confirme');
    exit;
}

// ── Annuler réservation ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'annuler') {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE reservations SET statut='annulee' WHERE id=?")->execute([$id]);

    // Récupérer logement_id pour le remettre dispo
    $r = $pdo->prepare("SELECT logement_id FROM reservations WHERE id=?");
    $r->execute([$id]);
    $resa = $r->fetch();
    if ($resa) {
        $actStmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE logement_id=? AND statut NOT IN ('annulee') AND id!=?");
        $actStmt->execute([$resa['logement_id'], $id]);
        if ((int)$actStmt->fetchColumn() === 0) {
            $pdo->prepare("UPDATE logements SET statut='disponible', date_dispo=NULL WHERE id=?")
                ->execute([$resa['logement_id']]);
        }
    }
    header('Location: reservations.php?msg=annule');
    exit;
}

// ── Marquer terminée ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'terminer') {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE reservations SET statut='terminee' WHERE id=?")->execute([$id]);
    header('Location: reservations.php?msg=termine');
    exit;
}

// ── Filtre statut ─────────────────────────────────────────
$filtre  = $_GET['statut'] ?? '';
$search  = trim($_GET['q'] ?? '');

$sql = "SELECT r.*, l.titre AS logement_titre, l.adresse,
               u.nom AS client_nom, u.email AS client_email,
               u.telephone AS client_tel
        FROM reservations r
        JOIN logements l ON r.logement_id = l.id
        JOIN users u     ON r.client_id   = u.id
        WHERE 1=1";

$params = [];
if ($filtre) { $sql .= " AND r.statut = ?"; $params[] = $filtre; }
if ($search) {
    $sql .= " AND (u.nom LIKE ? OR u.email LIKE ? OR l.titre LIKE ?)";
    $q = "%{$search}%";
    $params = array_merge($params, [$q, $q, $q]);
}
$sql .= " ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

// Comptes par statut
$counts = [];
foreach (['en_attente','confirmee','annulee','terminee'] as $s) {
    $c = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE statut = ?");
    $c->execute([$s]);
    $counts[$s] = $c->fetchColumn();
}

function statutBadge(string $s): string {
    return match($s) {
        'confirmee' => '<span style="background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:600"><i class="fa-solid fa-circle-check"></i> Confirmée</span>',
        'annulee'   => '<span style="background:#ffebee;color:#c62828;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:600"><i class="fa-solid fa-circle-xmark"></i> Annulée</span>',
        'terminee'  => '<span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:600"><i class="fa-solid fa-flag-checkered"></i> Terminée</span>',
        default     => '<span style="background:#fff3e0;color:#e65100;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:600"><i class="fa-solid fa-clock"></i> En attente</span>',
    };
}

$webmaster = '778890234';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réservations – Admin Sen Location</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    i { margin-right: 5px; }
    body { background: #f0ebe3; }
    .admin-layout { display:flex; min-height:100vh; }
    .admin-sidebar { width:250px; background:var(--brown-dark); position:fixed; top:0; left:0; bottom:0; overflow-y:auto; z-index:100; }
    .sidebar-logo { padding:24px 20px; border-bottom:1px solid rgba(255,255,255,.08); }
    .sidebar-logo .logo-text { font-family:'Cormorant Garamond',serif; font-size:1.5rem; color:var(--beige-deep); font-weight:600; }
    .sidebar-logo .logo-sub { font-size:.72rem; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1.5px; margin-top:3px; }
    .sidebar-user { padding:18px 20px; border-bottom:1px solid rgba(255,255,255,.08); display:flex; align-items:center; gap:12px; }
    .sidebar-user-avatar { width:38px; height:38px; background:rgba(166,124,82,.3); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--beige-deep); font-size:1rem; flex-shrink:0; }
    .sidebar-user-avatar i { margin:0; }
    .sidebar-user-nom { font-size:.88rem; color:#fff; font-weight:500; }
    .sidebar-user-role { font-size:.72rem; color:rgba(255,255,255,.45); margin-top:2px; }
    .sidebar-nav { padding:12px 0; }
    .sidebar-nav a { display:flex; align-items:center; gap:10px; padding:11px 20px; color:rgba(255,255,255,.60); text-decoration:none; font-size:.87rem; transition:all .2s; border-left:3px solid transparent; }
    .sidebar-nav a:hover { background:rgba(255,255,255,.06); color:#fff; }
    .sidebar-nav a.active { background:rgba(255,255,255,.10); color:var(--beige-deep); border-left-color:var(--brown-light); }
    .sidebar-nav a i { width:18px; text-align:center; margin:0; flex-shrink:0; }
    .sidebar-nav .nav-sep { border-top:1px solid rgba(255,255,255,.06); margin:8px 0; }
    .sidebar-nav .nav-label { padding:8px 20px; font-size:.68rem; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:1.5px; }
    .admin-main { margin-left:250px; flex:1; padding:32px; }
    .admin-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .admin-page-title { font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:600; color:var(--brown-dark); }

    /* Filtres tab */
    .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
    .filter-tab {
      padding:7px 16px; border-radius:50px;
      font-size:.83rem; font-weight:500;
      text-decoration:none; color:var(--text-mid);
      border:1.5px solid var(--beige-deep);
      transition:all .2s;
    }
    .filter-tab:hover, .filter-tab.active {
      background:var(--brown-mid); border-color:var(--brown-mid); color:#fff;
    }
    .filter-tab .count {
      display:inline-block;
      background:rgba(0,0,0,.12);
      border-radius:50px;
      padding:1px 7px;
      font-size:.70rem;
      margin-left:4px;
    }
    .filter-tab.active .count { background:rgba(255,255,255,.25); }

    /* Search */
    .search-form { display:flex; gap:10px; align-items:center; margin-bottom:20px; }
    .search-form input { padding:10px 14px; border:1.5px solid var(--beige-deep); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:.88rem; outline:none; width:280px; }
    .search-form input:focus { border-color:var(--brown-light); }

    /* Table */
    .table-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow-soft); border:1.5px solid var(--beige-mid); overflow:hidden; }
    .table-wrap { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; }
    thead th { background:var(--beige-light); padding:12px 16px; text-align:left; font-size:.74rem; font-weight:600; color:var(--text-mid); text-transform:uppercase; letter-spacing:.8px; white-space:nowrap; }
    tbody td { padding:12px 16px; border-bottom:1px solid var(--beige-light); font-size:.85rem; color:var(--text-dark); vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fdf9f5; }

    /* Alert */
    .msg-ok { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; border-radius:9px; padding:10px 16px; font-size:.88rem; margin-bottom:18px; }

    /* Paiement instructions */
    .pmt-box {
      background:var(--beige-light);
      border:1.5px solid var(--beige-deep);
      border-radius:9px;
      padding:14px 16px;
      font-size:.84rem;
      color:var(--text-mid);
      margin-top:8px;
    }
    .pmt-box strong { color:var(--brown-dark); }
    .pmt-numero {
      display:inline-flex; align-items:center; gap:6px;
      background:var(--brown-mid); color:#fff;
      padding:4px 12px; border-radius:50px;
      font-size:.84rem; font-weight:600; margin-top:8px;
    }
    .pmt-numero i { margin:0; }

    @media(max-width:900px){
      .admin-sidebar { display:none; }
      .admin-main { margin-left:0; padding:20px; }
    }
  </style>
</head>
<body>
<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar">
    <div class="sidebar-logo">
      <div class="logo-text">Sen Location 🇸🇳</div>
      <div class="logo-sub">Administration</div>
    </div>
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><i class="fa-solid fa-user"></i></div>
      <div>
        <div class="sidebar-user-nom"><?= htmlspecialchars($user_nom) ?></div>
        <div class="sidebar-user-role"><?= htmlspecialchars($user_role) ?></div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-label">Menu principal</div>
      <a href="index.php"><i class="fa-solid fa-gauge"></i> Tableau de bord</a>
      <a href="users.php"><i class="fa-solid fa-users"></i> Utilisateurs</a>
      <a href="reservations.php" class="active"><i class="fa-solid fa-calendar-check"></i> Réservations</a>
      <a href="logements.php"><i class="fa-solid fa-building"></i> Logements</a>
      <div class="nav-sep"></div>
      <a href="../profil.php"><i class="fa-solid fa-user-pen"></i> Mon profil</a>
      <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Voir le site</a>
      <a href="#" onclick="logoutAdmin();return false;" style="color:#ef9a9a !important">
        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
      </a>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">
    <div class="admin-header">
      <h1 class="admin-page-title">
        <i class="fa-solid fa-calendar-check"></i>
        Réservations (<?= count($reservations) ?>)
      </h1>
      <!-- Numéro webmaster -->
      <div style="background:#fff;border-radius:10px;padding:10px 18px;border:1.5px solid var(--beige-mid);display:flex;align-items:center;gap:10px;font-size:.86rem">
        <i class="fa-solid fa-phone" style="color:var(--brown-mid)"></i>
        <span style="color:var(--text-soft)">Paiements vers :</span>
        <strong style="color:var(--brown-dark)">+221 <?= $webmaster ?></strong>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="msg-ok">
        <i class="fa-solid fa-circle-check"></i>
        <?= match($_GET['msg']) {
          'confirme' => 'Réservation confirmée et paiement validé !',
          'annule'   => 'Réservation annulée.',
          'termine'  => 'Réservation marquée comme terminée.',
          default    => 'Action effectuée.'
        } ?>
      </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="filter-tabs">
      <a href="reservations.php" class="filter-tab <?= !$filtre?'active':'' ?>">
        <i class="fa-solid fa-border-all"></i> Toutes
        <span class="count"><?= array_sum($counts) ?></span>
      </a>
      <a href="?statut=en_attente" class="filter-tab <?= $filtre==='en_attente'?'active':'' ?>">
        <i class="fa-solid fa-clock"></i> En attente
        <span class="count"><?= $counts['en_attente'] ?></span>
      </a>
      <a href="?statut=confirmee" class="filter-tab <?= $filtre==='confirmee'?'active':'' ?>">
        <i class="fa-solid fa-circle-check"></i> Confirmées
        <span class="count"><?= $counts['confirmee'] ?></span>
      </a>
      <a href="?statut=terminee" class="filter-tab <?= $filtre==='terminee'?'active':'' ?>">
        <i class="fa-solid fa-flag-checkered"></i> Terminées
        <span class="count"><?= $counts['terminee'] ?></span>
      </a>
      <a href="?statut=annulee" class="filter-tab <?= $filtre==='annulee'?'active':'' ?>">
        <i class="fa-solid fa-circle-xmark"></i> Annulées
        <span class="count"><?= $counts['annulee'] ?></span>
      </a>
    </div>

    <!-- Recherche -->
    <form method="GET" class="search-form">
      <?php if ($filtre): ?>
        <input type="hidden" name="statut" value="<?= htmlspecialchars($filtre) ?>">
      <?php endif; ?>
      <input type="text" name="q"
             placeholder="Rechercher client, logement…"
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:.88rem">
        <i class="fa-solid fa-magnifying-glass"></i> Rechercher
      </button>
      <?php if ($search): ?>
        <a href="reservations.php<?= $filtre?"?statut={$filtre}":'' ?>"
           class="btn btn-outline" style="padding:10px 14px;font-size:.88rem">
          <i class="fa-solid fa-xmark"></i>
        </a>
      <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="table-card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Client</th>
              <th>Logement</th>
              <th>Dates</th>
              <th>Nuits</th>
              <th>Montant</th>
              <th>Paiement</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr>
              <td style="color:var(--text-soft);font-size:.80rem">#<?= $r['id'] ?></td>
              <td>
                <div style="font-weight:500"><?= htmlspecialchars($r['client_nom']) ?></div>
                <?php if ($r['client_tel']): ?>
                  <a href="tel:<?= htmlspecialchars($r['client_tel']) ?>"
                     style="font-size:.76rem;color:var(--brown-mid);text-decoration:none">
                    <i class="fa-solid fa-phone"></i><?= htmlspecialchars($r['client_tel']) ?>
                  </a>
                <?php endif; ?>
                <div style="font-size:.74rem;color:var(--text-soft)"><?= htmlspecialchars($r['client_email']) ?></div>
              </td>
              <td style="max-width:150px">
                <div style="font-weight:500;font-size:.84rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <?= htmlspecialchars($r['logement_titre']) ?>
                </div>
                <div style="font-size:.76rem;color:var(--text-soft)"><?= htmlspecialchars($r['adresse']) ?></div>
              </td>
              <td style="font-size:.80rem;white-space:nowrap">
                <div><i class="fa-solid fa-plane-arrival" style="color:var(--brown-light)"></i><?= date('d/m/Y', strtotime($r['date_arrivee'])) ?></div>
                <div><i class="fa-solid fa-plane-departure" style="color:var(--brown-light)"></i><?= date('d/m/Y', strtotime($r['date_depart'])) ?></div>
              </td>
              <td style="text-align:center;font-weight:600"><?= $r['nb_nuits'] ?>j</td>
              <td style="font-weight:600;white-space:nowrap;color:var(--brown-dark)">
                <?= number_format($r['prix_total'], 0, ',', ' ') ?> F
              </td>
              <td>
                <div style="font-size:.80rem;color:var(--text-mid)">
                  <?php
                    $icons = ['wave'=>'fa-mobile-screen','orange_money'=>'fa-circle','especes'=>'fa-money-bill'];
                    $icon  = $icons[$r['moyen_paiement']] ?? 'fa-credit-card';
                  ?>
                  <i class="fa-solid <?= $icon ?>"></i>
                  <?= htmlspecialchars($r['moyen_paiement']) ?>
                </div>
                <!-- Instructions paiement -->
                <?php if ($r['statut'] === 'en_attente' && $r['moyen_paiement'] !== 'especes'): ?>
                <div class="pmt-box">
                  Envoyer <strong><?= number_format($r['prix_total'],0,',',' ') ?> FCFA</strong>
                  via <?= $r['moyen_paiement']==='wave'?'Wave':'Orange Money' ?><br>
                  Réf : <strong>RESA-<?= $r['id'] ?></strong>
                  <div class="pmt-numero">
                    <i class="fa-solid fa-phone"></i> +221 <?= $webmaster ?>
                  </div>
                </div>
                <?php endif; ?>
              </td>
              <td><?= statutBadge($r['statut']) ?></td>
              <td>
                <div style="display:flex;flex-direction:column;gap:5px">
                  <?php if ($r['statut'] === 'en_attente'): ?>
                    <form method="POST">
                      <input type="hidden" name="action" value="confirmer">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <button type="submit" class="btn btn-success"
                              style="font-size:.75rem;padding:5px 10px;width:100%">
                        <i class="fa-solid fa-circle-check"></i> Confirmer
                      </button>
                    </form>
                    <form method="POST"
                          onsubmit="return confirm('Annuler cette réservation ?')">
                      <input type="hidden" name="action" value="annuler">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <button type="submit" class="btn btn-danger"
                              style="font-size:.75rem;padding:5px 10px;width:100%">
                        <i class="fa-solid fa-xmark"></i> Annuler
                      </button>
                    </form>
                  <?php elseif ($r['statut'] === 'confirmee'): ?>
                    <form method="POST">
                      <input type="hidden" name="action" value="terminer">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <button type="submit" class="btn btn-outline"
                              style="font-size:.75rem;padding:5px 10px;width:100%">
                        <i class="fa-solid fa-flag-checkered"></i> Terminer
                      </button>
                    </form>
                  <?php else: ?>
                    <span style="color:var(--text-soft);font-size:.76rem">—</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($reservations)): ?>
              <tr>
                <td colspan="9" style="text-align:center;color:var(--text-soft);padding:50px">
                  <i class="fa-solid fa-calendar-xmark" style="font-size:2rem;display:block;margin-bottom:10px"></i>
                  Aucune réservation trouvée.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script src="../assets/js/main.js"></script>
<script>
  async function logoutAdmin() {
    const fd = new FormData();
    fd.append('action', 'logout');
    await fetch('../api/auth.php', { method: 'POST', body: fd });
    window.location.href = '../index.php';
  }
</script>
</body>
</html>
