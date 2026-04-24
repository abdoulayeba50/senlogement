

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php?modal=login'); exit; }
require_once __DIR__ . '/../includes/db.php';
$pdo = getDB();
$user_nom  = $_SESSION['user_nom']  ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['admin','proprietaire'])) { header('Location: ../index.php'); exit; }

// ── Changer rôle ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'changer_role') {
    $uid  = (int)$_POST['user_id'];
    $role = $_POST['role'] ?? 'client';
    if (in_array($role, ['client','proprietaire','admin']) && $uid !== (int)$_SESSION['user_id']) {
        $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $uid]);
    }
    header('Location: users.php?msg=role_ok');
    exit;
}

// ── Supprimer utilisateur ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $uid = (int)$_POST['user_id'];
    if ($uid !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    }
    header('Location: users.php?msg=supp_ok');
    exit;
}

// ── Liste users ───────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$sql    = "SELECT u.*, COUNT(r.id) AS nb_reservations
           FROM users u
           LEFT JOIN reservations r ON r.client_id = u.id";
if ($search) {
    $sql .= " WHERE u.nom LIKE ? OR u.email LIKE ? OR u.telephone LIKE ?";
}
$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $q = "%{$search}%";
    $stmt->execute([$q, $q, $q]);
} else {
    $stmt->execute();
}
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Utilisateurs – Admin Sen Location</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    i { margin-right: 5px; }
    body { background: #f0ebe3; }
    .admin-layout { display: flex; min-height: 100vh; }
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
    .admin-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
    .admin-page-title { font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:600; color:var(--brown-dark); }

    /* Search bar */
    .search-form { display:flex; gap:10px; align-items:center; }
    .search-form input { padding:10px 14px; border:1.5px solid var(--beige-deep); border-radius:9px; font-family:'DM Sans',sans-serif; font-size:.88rem; outline:none; width:260px; }
    .search-form input:focus { border-color:var(--brown-light); }

    /* Table */
    .table-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow-soft); border:1.5px solid var(--beige-mid); overflow:hidden; }
    .table-wrap { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; }
    thead th { background:var(--beige-light); padding:12px 16px; text-align:left; font-size:.74rem; font-weight:600; color:var(--text-mid); text-transform:uppercase; letter-spacing:.8px; white-space:nowrap; }
    tbody td { padding:13px 16px; border-bottom:1px solid var(--beige-light); font-size:.86rem; color:var(--text-dark); vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fdf9f5; }
    .role-badge { display:inline-block; padding:2px 10px; border-radius:50px; font-size:.70rem; font-weight:600; text-transform:uppercase; }
    .role-admin        { background:#f3e5f5; color:#6a1b9a; }
    .role-proprietaire { background:#e3f2fd; color:#1565c0; }
    .role-client       { background:var(--beige-mid); color:var(--brown-mid); }

    .msg-ok { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; border-radius:9px; padding:10px 16px; font-size:.88rem; margin-bottom:18px; }

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
      <a href="users.php" class="active"><i class="fa-solid fa-users"></i> Utilisateurs</a>
      <a href="reservations.php"><i class="fa-solid fa-calendar-check"></i> Réservations</a>
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
        <i class="fa-solid fa-users"></i> Utilisateurs (<?= count($users) ?>)
      </h1>
      <form method="GET" class="search-form">
        <input type="text" name="q" placeholder="Rechercher nom, email…"
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary btn-sm"
                style="padding:10px 18px">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <?php if ($search): ?>
          <a href="users.php" class="btn btn-outline btn-sm" style="padding:10px 14px">
            <i class="fa-solid fa-xmark"></i>
          </a>
        <?php endif; ?>
      </form>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="msg-ok">
        <i class="fa-solid fa-circle-check"></i>
        <?= $_GET['msg'] === 'role_ok' ? 'Rôle mis à jour.' : 'Utilisateur supprimé.' ?>
      </div>
    <?php endif; ?>

    <div class="table-card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Rôle</th>
              <th>Réservations</th>
              <th>Inscrit le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td style="color:var(--text-soft)"><?= $u['id'] ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:9px">
                  <div style="width:34px;height:34px;background:var(--beige-mid);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--brown-mid);flex-shrink:0">
                    <i class="fa-solid fa-user" style="margin:0;font-size:.85rem"></i>
                  </div>
                  <span style="font-weight:500"><?= htmlspecialchars($u['nom']) ?></span>
                  <?php if ($u['id'] == $_SESSION['user_id']): ?>
                    <span style="background:var(--beige-mid);color:var(--brown-mid);font-size:.66rem;padding:2px 7px;border-radius:50px">Moi</span>
                  <?php endif; ?>
                </div>
              </td>
              <td style="color:var(--text-soft);font-size:.83rem"><?= htmlspecialchars($u['email']) ?></td>
              <td style="font-size:.83rem">
                <?php if ($u['telephone']): ?>
                  <a href="tel:<?= htmlspecialchars($u['telephone']) ?>"
                     style="color:var(--brown-mid);text-decoration:none">
                    <i class="fa-solid fa-phone"></i><?= htmlspecialchars($u['telephone']) ?>
                  </a>
                <?php else: ?>
                  <span style="color:var(--text-soft)">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action"  value="changer_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="role" onchange="this.form.submit()"
                            style="padding:4px 8px;border:1.5px solid var(--beige-deep);border-radius:7px;font-family:'DM Sans',sans-serif;font-size:.80rem;cursor:pointer;background:#fff">
                      <option value="client"       <?= $u['role']==='client'       ?'selected':'' ?>>Client</option>
                      <option value="proprietaire" <?= $u['role']==='proprietaire' ?'selected':'' ?>>Propriétaire</option>
                      <option value="admin"        <?= $u['role']==='admin'        ?'selected':'' ?>>Admin</option>
                    </select>
                  </form>
                <?php else: ?>
                  <span class="role-badge role-<?= $u['role'] ?>"><?= $u['role'] ?></span>
                <?php endif; ?>
              </td>
              <td style="text-align:center">
                <span style="background:var(--beige-mid);color:var(--brown-mid);padding:3px 10px;border-radius:50px;font-size:.80rem;font-weight:600">
                  <?= $u['nb_reservations'] ?>
                </span>
              </td>
              <td style="color:var(--text-soft);font-size:.80rem;white-space:nowrap">
                <?= date('d/m/Y', strtotime($u['created_at'])) ?>
              </td>
              <td>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                  <form method="POST" style="display:inline"
                        onsubmit="return confirm('Supprimer cet utilisateur ?')">
                    <input type="hidden" name="action"  value="supprimer">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-danger"
                            style="font-size:.76rem;padding:5px 11px">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="8" style="text-align:center;color:var(--text-soft);padding:40px">
                  <i class="fa-solid fa-users-slash" style="font-size:2rem;display:block;margin-bottom:10px"></i>
                  Aucun utilisateur trouvé.
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
