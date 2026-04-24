<?php
// ============================================================
//  Sen Location — admin/index.php
//  Tableau de bord webmaster
// ============================================================
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?modal=login');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$pdo       = getDB();
$user_nom  = $_SESSION['user_nom']  ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? '';

if (!in_array($user_role, ['admin', 'proprietaire'])) {
    header('Location: ../index.php');
    exit;
}

$stats = [
  'users'        => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
  'logements'    => $pdo->query("SELECT COUNT(*) FROM logements")->fetchColumn(),
  'reservations' => $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
  'en_attente'   => $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'")->fetchColumn(),
  'revenus'      => $pdo->query("SELECT COALESCE(SUM(prix_total),0) FROM reservations WHERE statut IN ('confirmee','en_attente')")->fetchColumn(),
];

$derniers_users = $pdo->query("
    SELECT id, nom, email, telephone, role, created_at
    FROM users ORDER BY created_at DESC LIMIT 8
")->fetchAll();

$derniers_resas = $pdo->query("
    SELECT r.*, l.titre AS logement_titre,
           u.nom AS client_nom, u.telephone AS client_tel
    FROM reservations r
    JOIN logements l ON r.logement_id = l.id
    JOIN users u     ON r.client_id   = u.id
    ORDER BY r.created_at DESC LIMIT 6
")->fetchAll();

function statutBadge(string $s): string {
    return match($s) {
        'confirmee' => '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Confirmée</span>',
        'annulee'   => '<span class="badge badge-red"><i class="fa-solid fa-circle-xmark"></i> Annulée</span>',
        'terminee'  => '<span class="badge badge-blue"><i class="fa-solid fa-flag-checkered"></i> Terminée</span>',
        default     => '<span class="badge badge-orange"><i class="fa-solid fa-clock"></i> En attente</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – Sen Location</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    html, body {
      margin: 0; padding: 0;
      overflow-x: hidden;
      background: #f0ebe3;
      min-height: 100vh;
    }

    :root {
      --sw: 240px;       /* sidebar width ouverte  */
      --sc: 64px;        /* sidebar width fermée   */
      --dur: .28s cubic-bezier(.4,0,.2,1);
    }

    /* ══ Layout ══ */
    .admin-layout {
      display: flex;
      min-height: 100vh;
    }

    /* ══ SIDEBAR ══ */
    .admin-sidebar {
      width: var(--sw);
      background: var(--brown-dark);
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 200;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: width var(--dur);
      flex-shrink: 0;
    }
    .admin-sidebar.closed { width: var(--sc); }

    /* Bouton toggle */
    .sb-toggle {
      position: absolute;
      top: 16px; right: 10px;
      width: 26px; height: 26px;
      background: rgba(255,255,255,.12);
      border: none; border-radius: 7px;
      color: rgba(255,255,255,.75);
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: .75rem;
      transition: background .2s, transform var(--dur);
      z-index: 5;
    }
    .sb-toggle:hover { background: rgba(255,255,255,.22); color: #fff; }
    .admin-sidebar.closed .sb-toggle { transform: rotate(180deg); right: 19px; }

    /* Logo */
    .sb-logo {
      padding: 16px 14px 16px 16px;
      border-bottom: 1px solid rgba(255,255,255,.08);
      display: flex; align-items: center; gap: 10px;
      white-space: nowrap; overflow: hidden; flex-shrink: 0;
    }
    .sb-logo-icon { font-size: 1.25rem; flex-shrink: 0; }
    .sb-logo-texts {
      overflow: hidden;
      opacity: 1; transition: opacity var(--dur);
    }
    .admin-sidebar.closed .sb-logo-texts { opacity: 0; pointer-events: none; }
    .sb-logo-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.25rem; color: var(--beige-deep); font-weight: 600;
    }
    .sb-logo-sub {
      font-size: .62rem; color: rgba(255,255,255,.38);
      text-transform: uppercase; letter-spacing: 1.4px;
    }

    /* User */
    .sb-user {
      padding: 13px 14px;
      border-bottom: 1px solid rgba(255,255,255,.08);
      display: flex; align-items: center; gap: 10px;
      white-space: nowrap; overflow: hidden; flex-shrink: 0;
    }
    .sb-avatar {
      width: 33px; height: 33px; border-radius: 50%;
      background: rgba(166,124,82,.35);
      display: flex; align-items: center; justify-content: center;
      color: var(--beige-deep); font-size: .82rem; flex-shrink: 0;
    }
    .sb-avatar i { margin: 0; }
    .sb-user-info { overflow: hidden; opacity: 1; transition: opacity var(--dur); }
    .admin-sidebar.closed .sb-user-info { opacity: 0; pointer-events: none; }
    .sb-user-nom { font-size: .82rem; color: #fff; font-weight: 500; overflow: hidden; text-overflow: ellipsis; }
    .sb-user-role { font-size: .66rem; color: rgba(255,255,255,.4); }

    /* Nav */
    .sb-nav { flex: 1; padding: 8px 0; overflow-y: auto; overflow-x: hidden; }

    .sb-nav .sep { border-top: 1px solid rgba(255,255,255,.06); margin: 6px 0; }

    .sb-nav .grp-label {
      padding: 7px 16px 4px;
      font-size: .62rem; color: rgba(255,255,255,.28);
      text-transform: uppercase; letter-spacing: 1.4px;
      white-space: nowrap; overflow: hidden;
      opacity: 1; transition: opacity var(--dur);
    }
    .admin-sidebar.closed .sb-nav .grp-label { opacity: 0; }

    .sb-nav a {
      display: flex; align-items: center; gap: 12px;
      padding: 10px 16px;
      color: rgba(255,255,255,.58);
      text-decoration: none; font-size: .84rem;
      border-left: 3px solid transparent;
      white-space: nowrap; overflow: hidden;
      transition: background .18s, color .18s;
      position: relative;
    }
    .sb-nav a:hover { background: rgba(255,255,255,.07); color: #fff; }
    .sb-nav a.active {
      background: rgba(255,255,255,.11);
      color: var(--beige-deep);
      border-left-color: var(--brown-light);
    }
    .sb-nav a .ico { width: 20px; text-align: center; flex-shrink: 0; }
    .sb-nav a .ico i { margin: 0; }
    .sb-nav a .lbl { overflow: hidden; opacity: 1; transition: opacity var(--dur); }
    .admin-sidebar.closed .sb-nav a .lbl { opacity: 0; pointer-events: none; }

    /* Badge nombre */
    .nb {
      margin-left: auto; flex-shrink: 0;
      background: #e53935; color: #fff;
      font-size: .60rem; font-weight: 700;
      padding: 2px 6px; border-radius: 50px;
      opacity: 1; transition: opacity var(--dur);
    }
    .admin-sidebar.closed .nb { opacity: 0; }

    /* Tooltip quand fermé */
    .admin-sidebar.closed .sb-nav a::after {
      content: attr(data-tip);
      position: absolute;
      left: calc(var(--sc) + 6px);
      top: 50%; transform: translateY(-50%);
      background: #2c1f14;
      color: #fff; font-size: .76rem;
      padding: 4px 10px; border-radius: 6px;
      white-space: nowrap;
      opacity: 0; pointer-events: none;
      transition: opacity .15s;
      box-shadow: 0 3px 10px rgba(0,0,0,.3);
    }
    .admin-sidebar.closed .sb-nav a:hover::after { opacity: 1; }

    /* Lien déconnexion rouge */
    .sb-nav a.danger { color: #ef9a9a !important; }

    /* ══ MAIN ══ */
    .admin-main {
      flex: 1;
      margin-left: var(--sw);
      padding: 28px 28px 48px;
      min-width: 0;
      overflow-x: hidden;
      transition: margin-left var(--dur);
    }
    .admin-main.closed { margin-left: var(--sc); }

    /* Header */
    .adm-header {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
      margin-bottom: 24px;
    }
    .adm-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.85rem; font-weight: 600;
      color: var(--brown-dark); margin: 0;
    }
    .adm-date { font-size: .78rem; color: var(--text-soft); margin-top: 3px; }

    /* Stats */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 14px; margin-bottom: 24px;
    }
    .sc {
      background: #fff; border-radius: var(--radius);
      padding: 17px 15px;
      box-shadow: var(--shadow-soft);
      border: 1.5px solid var(--beige-mid);
      position: relative; overflow: hidden; min-width: 0;
    }
    .sc::before {
      content: ''; position: absolute;
      top: 0; left: 0; right: 0; height: 3px;
    }
    .sc.blue::before   { background: #1565c0; }
    .sc.green::before  { background: #2e7d32; }
    .sc.orange::before { background: #e65100; }
    .sc.brown::before  { background: var(--brown-mid); }
    .sc-icon {
      width: 37px; height: 37px; border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      font-size: .9rem; margin-bottom: 11px;
    }
    .sc.blue   .sc-icon { background: #e3f2fd; color: #1565c0; }
    .sc.green  .sc-icon { background: #e8f5e9; color: #2e7d32; }
    .sc.orange .sc-icon { background: #fff3e0; color: #e65100; }
    .sc.brown  .sc-icon { background: var(--beige-mid); color: var(--brown-mid); }
    .sc-icon i { margin: 0; }
    .sc-num {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.65rem; font-weight: 600;
      color: var(--brown-dark); line-height: 1;
      margin-bottom: 4px; word-break: break-all;
    }
    .sc-lbl { font-size: .70rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: .8px; }

    /* Badges */
    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 9px; border-radius: 50px;
      font-size: .69rem; font-weight: 600; white-space: nowrap;
    }
    .badge i { margin: 0; }
    .badge-green  { background:#e8f5e9; color:#2e7d32; }
    .badge-red    { background:#ffebee; color:#c62828; }
    .badge-blue   { background:#e3f2fd; color:#1565c0; }
    .badge-orange { background:#fff3e0; color:#e65100; }

    .role-badge {
      display: inline-block; padding: 2px 9px; border-radius: 50px;
      font-size: .65rem; font-weight: 600;
      text-transform: uppercase; white-space: nowrap;
    }
    .role-admin        { background:#f3e5f5; color:#6a1b9a; }
    .role-proprietaire { background:#e3f2fd; color:#1565c0; }
    .role-client       { background:var(--beige-mid); color:var(--brown-mid); }

    /* Section */
    .sec-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.22rem; font-weight: 600;
      color: var(--brown-dark);
      margin-bottom: 12px;
      display: flex; align-items: center; justify-content: space-between; gap: 8px;
    }
    .sec-title a {
      font-family: 'DM Sans', sans-serif;
      font-size: .76rem; color: var(--brown-mid);
      text-decoration: none; font-weight: 500; flex-shrink: 0;
    }
    .sec-title a:hover { text-decoration: underline; }

    /* Tables */
    .t-card {
      background: #fff; border-radius: var(--radius);
      box-shadow: var(--shadow-soft);
      border: 1.5px solid var(--beige-mid);
      overflow: hidden; margin-bottom: 24px;
    }
    .t-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; min-width: 440px; }
    thead th {
      background: var(--beige-light);
      padding: 10px 13px; text-align: left;
      font-size: .68rem; font-weight: 600;
      color: var(--text-mid);
      text-transform: uppercase; letter-spacing: .8px; white-space: nowrap;
    }
    tbody td {
      padding: 11px 13px;
      border-bottom: 1px solid var(--beige-light);
      font-size: .82rem; color: var(--text-dark);
      vertical-align: middle;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #fdf9f5; }
    .trunc { max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .av-mini {
      width: 27px; height: 27px; border-radius: 50%;
      background: var(--beige-mid);
      display: flex; align-items: center; justify-content: center;
      color: var(--brown-mid); font-size: .70rem; flex-shrink: 0;
    }
    .av-mini i { margin: 0; }

    /* 2 cols */
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    /* ══ Responsive ══ */
    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(3,1fr); } }
    @media (max-width: 960px)  { .stats-grid { grid-template-columns: repeat(2,1fr); } .two-col { grid-template-columns: 1fr; } }
    @media (max-width: 768px)  {
      .admin-sidebar { width: var(--sc) !important; }
      .admin-main { margin-left: var(--sc) !important; padding: 14px 14px 40px; }
    }
    @media (max-width: 520px)  { .stats-grid { grid-template-columns: 1fr 1fr; } }
  </style>
</head>
<body>
<div class="admin-layout">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside class="admin-sidebar" id="sidebar">

    <button class="sb-toggle" id="sbToggle" title="Réduire / Agrandir">
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    <div class="sb-logo">
      
      <div class="sb-logo-texts">
        <div class="sb-logo-name">Sen Location</div>
        <div class="sb-logo-sub">Administration</div>
      </div>
    </div>

    <div class="sb-user">
      <div class="sb-avatar"><i class="fa-solid fa-user"></i></div>
      <div class="sb-user-info">
        <div class="sb-user-nom"><?= htmlspecialchars($user_nom) ?></div>
        <div class="sb-user-role"><?= htmlspecialchars($user_role) ?></div>
      </div>
    </div>

    <nav class="sb-nav">
      <div class="grp-label">Menu principal</div>

      <a href="index.php" class="active" data-tip="Tableau de bord">
        <span class="ico"><i class="fa-solid fa-gauge"></i></span>
        <span class="lbl">Tableau de bord</span>
      </a>

      <a href="users.php" data-tip="Utilisateurs">
        <span class="ico"><i class="fa-solid fa-users"></i></span>
        <span class="lbl">Utilisateurs</span>
        <?php if ($stats['users'] > 0): ?>
          <span class="nb"><?= $stats['users'] ?></span>
        <?php endif; ?>
      </a>

      <a href="reservations.php" data-tip="Réservations">
        <span class="ico"><i class="fa-solid fa-calendar-check"></i></span>
        <span class="lbl">Réservations</span>
        <?php if ($stats['en_attente'] > 0): ?>
          <span class="nb"><?= $stats['en_attente'] ?></span>
        <?php endif; ?>
      </a>

      <!-- <a href="logements.php" data-tip="Logements">
        <span class="ico"><i class="fa-solid fa-building"></i></span>
        <span class="lbl">Logements</span>
      </a> -->

      <div class="sep"></div>
      <div class="grp-label">Compte</div>

      <a href="../profil.php" data-tip="Mon profil">
        <span class="ico"><i class="fa-solid fa-user-pen"></i></span>
        <span class="lbl">Mon profil</span>
      </a>

      <a href="../index.php" data-tip="Voir le site">
        <span class="ico"><i class="fa-solid fa-arrow-left"></i></span>
        <span class="lbl">Voir le site</span>
      </a>

      <a href="#" class="danger" data-tip="Déconnexion" onclick="logoutAdmin(); return false;">
        <span class="ico"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span class="lbl">Déconnexion</span>
      </a>
    </nav>
  </aside>

  <!-- ══════════ MAIN ══════════ -->
  <main class="admin-main" id="adminMain">

    <div class="adm-header">
      <div>
        <h1 class="adm-title"><i class="fa-solid fa-gauge"></i> Tableau de bord</h1>
        <div class="adm-date">
          <i class="fa-solid fa-calendar"></i> <?= date('l d F Y', time()) ?>
        </div>
      </div>
      <a href="../index.php" class="btn btn-outline" style="font-size:.80rem;white-space:nowrap">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> Voir le site
      </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="sc blue">
        <div class="sc-icon"><i class="fa-solid fa-users"></i></div>
        <div class="sc-num"><?= $stats['users'] ?></div>
        <div class="sc-lbl">Utilisateurs</div>
      </div>
      <div class="sc brown">
        <div class="sc-icon"><i class="fa-solid fa-building"></i></div>
        <div class="sc-num"><?= $stats['logements'] ?></div>
        <div class="sc-lbl">Logements</div>
      </div>
      <div class="sc green">
        <div class="sc-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="sc-num"><?= $stats['reservations'] ?></div>
        <div class="sc-lbl">Réservations</div>
      </div>
      <div class="sc orange">
        <div class="sc-icon"><i class="fa-solid fa-clock"></i></div>
        <div class="sc-num"><?= $stats['en_attente'] ?></div>
        <div class="sc-lbl">En attente</div>
      </div>
      <div class="sc green">
        <div class="sc-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
        <div class="sc-num" style="font-size:1.1rem"><?= number_format($stats['revenus'], 0, ',', ' ') ?></div>
        <div class="sc-lbl">FCFA générés</div>
      </div>
    </div>

    <!-- 2 colonnes -->
    <div class="two-col">

      <div>
        <div class="sec-title">
          <span><i class="fa-solid fa-users"></i> Derniers inscrits</span>
          <a href="users.php">Voir tout →</a>
        </div>
        <div class="t-card">
          <div class="t-wrap">
            <table>
              <thead>
                <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Date</th></tr>
              </thead>
              <tbody>
                <?php foreach ($derniers_users as $u): ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:7px;min-width:0">
                      <div class="av-mini"><i class="fa-solid fa-user"></i></div>
                      <span style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($u['nom']) ?></span>
                    </div>
                  </td>
                  <td class="trunc" style="color:var(--text-soft);font-size:.77rem"><?= htmlspecialchars($u['email']) ?></td>
                  <td><span class="role-badge role-<?= $u['role'] ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                  <td style="color:var(--text-soft);font-size:.74rem;white-space:nowrap"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($derniers_users)): ?>
                  <tr><td colspan="4" style="text-align:center;color:var(--text-soft);padding:28px">Aucun utilisateur</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div>
        <div class="sec-title">
          <span><i class="fa-solid fa-calendar-check"></i> Dernières réservations</span>
          <a href="reservations.php">Voir tout →</a>
        </div>
        <div class="t-card">
          <div class="t-wrap">
            <table>
              <thead>
                <tr><th>Client</th><th>Logement</th><th>Dates</th><th>Montant</th><th>Statut</th></tr>
              </thead>
              <tbody>
                <?php foreach ($derniers_resas as $r): ?>
                <tr>
                  <td style="white-space:nowrap">
                    <div style="font-weight:500;font-size:.82rem"><?= htmlspecialchars($r['client_nom']) ?></div>
                    <?php if ($r['client_tel']): ?>
                      <div style="font-size:.70rem;color:var(--text-soft)">
                        <i class="fa-solid fa-phone" style="margin-right:2px"></i><?= htmlspecialchars($r['client_tel']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="trunc" style="font-size:.79rem"><?= htmlspecialchars($r['logement_titre']) ?></td>
                  <td style="font-size:.72rem;color:var(--text-soft);white-space:nowrap">
                    <?= date('d/m/y', strtotime($r['date_arrivee'])) ?><br>→ <?= date('d/m/y', strtotime($r['date_depart'])) ?>
                  </td>
                  <td style="font-weight:600;white-space:nowrap;font-size:.79rem"><?= number_format($r['prix_total'], 0, ',', ' ') ?> F</td>
                  <td><?= statutBadge($r['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($derniers_resas)): ?>
                  <tr><td colspan="5" style="text-align:center;color:var(--text-soft);padding:28px">Aucune réservation</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="../assets/js/main.js"></script>
<script>
  const sidebar   = document.getElementById('sidebar');
  const main      = document.getElementById('adminMain');
  const toggleBtn = document.getElementById('sbToggle');
  const KEY       = 'senloc_sb';

  // Restaurer état sauvegardé
  if (localStorage.getItem(KEY) === '1') {
    sidebar.classList.add('closed');
    main.classList.add('closed');
  }

  toggleBtn.addEventListener('click', () => {
    const isClosed = sidebar.classList.toggle('closed');
    main.classList.toggle('closed', isClosed);
    localStorage.setItem(KEY, isClosed ? '1' : '0');
  });

  async function logoutAdmin() {
    const fd = new FormData();
    fd.append('action', 'logout');
    await fetch('../api/auth.php', { method: 'POST', body: fd });
    window.location.href = '../index.php';
  }
</script>
</body>
</html>