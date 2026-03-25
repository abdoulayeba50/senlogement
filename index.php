<?php
session_start();
$user_nom  = $_SESSION['user_nom']  ?? null;
$user_role = $_SESSION['user_role'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sen Location – Trouvez votre logement idéal</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
.pay-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  padding: 6px 10px;
  border-radius: 6px;
  margin-right: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.pay-logo {
  width: 40px;
  height: auto;
  object-fit: contain;
}
.pay-badge:hover {
  transform: scale(1.05);
  transition: 0.2s;
  cursor: pointer;
}
    /* ── Icônes ── */
    i { margin-right: 5px; }
    .card-wishlist i,
    .card-rating i  { margin: 0; }
    .feature i { color: var(--brown-mid); margin-right: 4px; }

    /* ── HERO ── */
    .hero {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .hero-bg {
      position: absolute;
      inset: 0;
      background: url('images/im1.png') center center / cover no-repeat;
      filter: blur(3px) brightness(0.58);
      transform: scale(1.05);
      transition: transform 10s ease;
    }
    .hero-bg.loaded { transform: scale(1); }
    .hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        160deg,
        rgba(74,46,26,.40) 0%,
        rgba(20,10,3,.55)  60%,
        rgba(74,46,26,.25) 100%
      );
    }
    .hero-content {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 140px 24px 80px;
      width: 100%;
      max-width: 960px;
    }
    .hero-badge {
      display: inline-block;
      background: rgba(255,255,255,.15);
      border: 1px solid rgba(255,255,255,.30);
      color: rgba(255,255,255,.92);
      font-size: .78rem;
      font-weight: 500;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 7px 20px;
      border-radius: 50px;
      margin-bottom: 28px;
      backdrop-filter: blur(8px);
      animation: fadeUp .7s ease both;
    }
    .hero-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2.4rem, 6vw, 4.4rem);
      font-weight: 600;
      color: #fff;
      line-height: 1.10;
      margin-bottom: 14px;
      animation: fadeUp .7s .12s ease both;
    }
    .hero-title em { font-style: italic; color: var(--beige-deep); }
    .hero-sub {
      color: rgba(255,255,255,.78);
      font-size: 1.05rem;
      font-weight: 300;
      max-width: 480px;
      line-height: 1.65;
      margin-bottom: 38px;
      animation: fadeUp .7s .22s ease both;
    }

    /* ── Carte de connexion flottante ── */
    .login-card {
      width: 100%;
      max-width: 420px;
      background: rgba(255,255,255,.13);
      border: 1px solid rgba(255,255,255,.25);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-radius: 20px;
      padding: 36px 32px 28px;
      animation: fadeUp .7s .32s ease both;
      box-shadow: 0 24px 60px rgba(0,0,0,.25);
    }
    .login-card h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem;
      color: #fff;
      margin-bottom: 22px;
      text-align: center;
    }
    .login-card .form-group input {
      background: rgba(255,255,255,.18);
      border: 1px solid rgba(255,255,255,.28);
      color: #fff;
      border-radius: 10px;
    }
    .login-card .form-group input::placeholder { color: rgba(255,255,255,.55); }
    .login-card .form-group input:focus {
      border-color: rgba(255,255,255,.60);
      background: rgba(255,255,255,.24);
    }
    .btn-hero {
      width: 100%;
      padding: 13px;
      background: var(--brown-mid);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: .96rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 6px;
      transition: background .25s, transform .15s;
    }
    .btn-hero:hover { background: var(--brown-dark); transform: translateY(-1px); }
    .hero-form-footer {
      text-align: center;
      margin-top: 14px;
      color: rgba(255,255,255,.68);
      font-size: .87rem;
    }
    .hero-form-footer a { color: var(--beige-deep); text-decoration: underline; cursor: pointer; }

    /* ── Bienvenue si connecté ── */
    .hero-welcome { animation: fadeUp .7s .32s ease both; }
    .hero-welcome h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem; color: #fff; margin-bottom: 12px;
    }
    .hero-welcome p { color: rgba(255,255,255,.75); margin-bottom: 24px; }

    /* ── Stats bar ── */
    .stats-bar {
      background: var(--brown-dark);
      padding: 22px 48px;
      display: flex;
      justify-content: center;
      gap: 60px;
      flex-wrap: wrap;
    }
    .stat-item { text-align: center; }
    .stat-item .number {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem; font-weight: 600; color: var(--beige-deep);
    }
    .stat-item .label {
      font-size: .78rem; color: rgba(255,255,255,.55);
      text-transform: uppercase; letter-spacing: 1.2px; margin-top: 2px;
    }

    /* ── Section logements ── */
    .section-logements {
      padding: 70px 48px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .section-header { margin-bottom: 36px; }
    .section-label {
      font-size: .74rem; font-weight: 600;
      letter-spacing: 3px; text-transform: uppercase;
      color: var(--brown-light); margin-bottom: 8px;
    }
    .section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(1.7rem, 3vw, 2.6rem);
      color: var(--brown-dark); margin-bottom: 6px;
    }
    .section-sub {
      color: var(--text-soft); font-size: .93rem;
      max-width: 480px; line-height: 1.6; font-weight: 300;
    }

    /* ── Search bar ── */
    .search-bar {
      display: flex;
      background: #fff;
      border-radius: 12px;
      box-shadow: var(--shadow-card);
      overflow: hidden;
      border: 1.5px solid var(--beige-mid);
      margin-bottom: 28px;
    }
    .search-bar input {
      flex: 1; padding: 13px 16px;
      border: none; outline: none;
      font-family: 'DM Sans', sans-serif;
      font-size: .90rem; color: var(--text-dark);
    }
    .search-bar select {
      padding: 13px 14px;
      border: none; border-left: 1.5px solid var(--beige-mid);
      background: transparent;
      font-family: 'DM Sans', sans-serif;
      font-size: .88rem; color: var(--text-mid);
      outline: none; cursor: pointer;
    }
    .search-bar button {
      padding: 12px 26px;
      background: var(--brown-mid); color: #fff;
      border: none; font-family: 'DM Sans', sans-serif;
      font-size: .90rem; font-weight: 600;
      cursor: pointer; transition: background .2s;
      white-space: nowrap;
    }
    .search-bar button:hover { background: var(--brown-dark); }

    /* ── Filtres ── */
    .filter-row { display: flex; gap: 9px; flex-wrap: wrap; margin-bottom: 32px; }
    .filter-btn {
      padding: 7px 18px;
      border: 1.5px solid var(--beige-deep);
      background: transparent; border-radius: 50px;
      color: var(--text-mid); font-family: 'DM Sans', sans-serif;
      font-size: .84rem; cursor: pointer;
      transition: all .2s; font-weight: 500;
    }
    .filter-btn:hover, .filter-btn.active {
      background: var(--brown-mid); border-color: var(--brown-mid); color: #fff;
    }

    /* ── Grille logements ── */
    .listings-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 26px;
    }

    /* ── Carte logement ── */
    .property-card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-card);
      overflow: hidden;
      cursor: pointer;
      transition: transform .28s, box-shadow .28s;
    }
    .property-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 50px rgba(74,46,26,.18);
    }
    .card-img {
      position: relative; height: 210px;
      overflow: hidden; background: var(--beige-mid);
    }
    .card-img img {
      width: 100%; height: 100%;
      object-fit: cover; transition: transform .5s;
    }
    .property-card:hover .card-img img { transform: scale(1.06); }
    .card-img-badge { position: absolute; top: 12px; left: 12px; }
    .card-wishlist {
      position: absolute; top: 10px; right: 12px;
      background: rgba(255,255,255,.88); border: none;
      width: 34px; height: 34px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; font-size: 1rem;
      transition: background .2s, color .2s;
    }
    .card-wishlist:hover { background: #fff; color: #e53935; }
    .card-wishlist.liked { color: #e53935; background: #fff; }
    .card-date-badge {
      position: absolute; bottom: 10px; left: 12px;
      background: rgba(0,0,0,.55); color: rgba(255,255,255,.9);
      font-size: .70rem; padding: 3px 9px;
      border-radius: 6px; backdrop-filter: blur(6px);
    }
    .card-body { padding: 16px 18px 20px; }
    .card-meta {
      display: flex; justify-content: space-between;
      align-items: flex-start; margin-bottom: 6px;
    }
    .card-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.12rem; font-weight: 600;
      color: var(--brown-dark); line-height: 1.2;
    }
    .card-rating {
      display: flex; align-items: center; gap: 3px;
      font-size: .82rem; color: var(--text-mid);
      white-space: nowrap;
    }
    .card-rating i { color: #f59e0b; }
    .card-location { font-size: .82rem; color: var(--text-soft); margin-bottom: 12px; }
    .card-location i { color: var(--brown-light); margin-right: 4px; }
    .card-features { display: flex; gap: 14px; margin-bottom: 14px; flex-wrap: wrap; }
    .feature { font-size: .80rem; color: var(--text-mid); display: flex; align-items: center; }
    .card-footer {
      display: flex; justify-content: space-between;
      align-items: center; border-top: 1px solid var(--beige-mid); padding-top: 12px;
    }
    .price {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; font-weight: 600; color: var(--brown-dark);
    }
    .price span { font-size: .75rem; color: var(--text-soft); font-family: 'DM Sans', sans-serif; font-weight: 400; }

    /* ── Section avantages ── */
    .features-wrap { background: var(--brown-dark); padding: 70px 48px; }
    .features-inner {
      max-width: 1100px; margin: 0 auto;
      display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 32px;
    }
    .feature-card {
      text-align: center; padding: 34px 22px;
      border: 1px solid rgba(255,255,255,.10);
      border-radius: var(--radius);
      background: rgba(255,255,255,.04);
      transition: background .25s, border-color .25s;
    }
    .feature-card:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.20); }
    .feature-icon {
      width: 60px; height: 60px;
      background: rgba(166,124,82,.20);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 18px;
      font-size: 1.6rem; color: var(--beige-deep);
      border: 1px solid rgba(166,124,82,.30);
    }
    .feature-icon i { margin: 0; }
    .feature-card h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; color: var(--beige-light); margin-bottom: 9px;
    }
    .feature-card p { font-size: .85rem; color: rgba(255,255,255,.55); line-height: 1.65; font-weight: 300; }
    .payment-badges { display: flex; justify-content: center; gap: 9px; margin-top: 12px; }
    .pay-badge {
      background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.18);
      border-radius: 7px; padding: 4px 11px;
      font-size: .76rem; color: rgba(255,255,255,.80); font-weight: 500;
    }

    /* ── Footer ── */
    footer { background: var(--brown-dark); border-top: 1px solid rgba(255,255,255,.08); padding: 28px 48px; }
    .footer-inner {
      max-width: 1200px; margin: 0 auto;
      display: flex; align-items: center;
      justify-content: space-between; flex-wrap: wrap; gap: 14px;
    }
    .footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: var(--beige-deep); }
    .footer-links { display: flex; gap: 26px; }
    .footer-links a { color: rgba(255,255,255,.55); text-decoration: none; font-size: .83rem; transition: color .2s; }
    .footer-links a:hover { color: var(--beige-deep); }
    .footer-copy { width: 100%; text-align: center; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.06); margin-top: 8px; color: rgba(255,255,255,.30); font-size: .76rem; }

    /* ── Modal ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0; z-index: 200;
      background: rgba(30,15,5,.65); backdrop-filter: blur(8px);
      align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--beige-light); border-radius: 18px;
      padding: 38px 34px 30px; width: 100%; max-width: 420px;
      position: relative; animation: popIn .28s ease both;
      max-height: 90vh; overflow-y: auto;
    }
    .modal-close {
      position: absolute; top: 14px; right: 16px;
      background: none; border: none; font-size: 1.2rem;
      cursor: pointer; color: var(--text-soft); transition: color .2s;
    }
    .modal-close:hover { color: var(--brown-dark); }
    .tab-switch { display: flex; border-bottom: 2px solid var(--beige-mid); margin-bottom: 24px; }
    .tab-btn {
      flex: 1; padding: 9px; background: none; border: none;
      font-family: 'DM Sans', sans-serif; font-size: .90rem;
      font-weight: 500; cursor: pointer; color: var(--text-soft);
      border-bottom: 2px solid transparent; margin-bottom: -2px; transition: color .2s;
    }
    .tab-btn.active { color: var(--brown-mid); border-bottom-color: var(--brown-mid); }

    /* ── Animations ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes popIn {
      from { opacity: 0; transform: scale(.94) translateY(10px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .fade-up { animation: fadeUp .55s ease both; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .navbar { padding: 14px 20px; }
      .nav-links { display: none; }
      .section-logements { padding: 50px 20px; }
      .features-inner { grid-template-columns: 1fr; }
      .stats-bar { gap: 30px; padding: 20px; }
      footer { padding: 24px 20px; }
    }
    @media (max-width: 600px) {
      .search-bar { flex-direction: column; }
      .search-bar select { border-left: none; border-top: 1.5px solid var(--beige-mid); }
      .search-bar button { width: 100%; }
      .listings-grid { grid-template-columns: 1fr; }
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
  <a href="index.php" class="nav-logo"> <span>Sen </span> Location</a>
  <div class="nav-links">
    <a href="index.php"><i class="fa-solid fa-house"></i> Accueil</a>
    <a href="#logements"><i class="fa-solid fa-building"></i> Logements</a>
    <a href="#contact"><i class="fa-solid fa-envelope"></i> Contact</a>
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

<!-- HERO -->
<section class="hero">
  <div class="hero-bg" id="heroBg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-badge">
      <i class="fa-solid fa-earth-africa"></i> Logements au Sénégal
    </div>
    <h1 class="hero-title">
      Trouvez votre<br><em>logement idéal</em><br>en quelques clics
    </h1>
    <p class="hero-sub">
      De Dakar à Saly, découvrez des appartements, villas et studios
      disponibles à la location. Réservez en toute simplicité.
    </p>
    <?php if ($user_nom): ?>
      <div class="hero-welcome">
        <h3>Bon retour, <?= htmlspecialchars($user_nom) ?> 👋</h3>
        <p>Trouvez votre prochain logement ci-dessous.</p>
        <a href="profil.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.5)">
          <i class="fa-solid fa-arrow-right"></i> Mon espace
        </a>
      </div>
    <?php else: ?>
      <div class="login-card">
        <h3><i class="fa-solid fa-right-to-bracket" style="font-size:1rem;margin-right:8px"></i>Connexion rapide</h3>
        <form id="heroLoginForm">
          <div class="form-group">
            <input type="email" name="email" placeholder="E-mail" required>
          </div>
          <div class="form-group">
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
          </div>
          <button type="submit" class="btn-hero">
            <i class="fa-solid fa-right-to-bracket"></i> Se Connecter
          </button>
        </form>
        <p class="hero-form-footer">
          Pas encore de compte ? <a onclick="openModal('register')">Créer un compte</a>
        </p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- STATS -->
<div class="stats-bar">
  <div class="stat-item">
    <div class="number">20</div>
    <div class="label">Logements</div>
  </div>
  <div class="stat-item">
    <div class="number">6</div>
    <div class="label">Villes</div>
  </div>
  <div class="stat-item">
    <div class="number">100</div>
    <div class="label">Réservations</div>
  </div>
  <div class="stat-item">
    <div class="number">4 <i class="fa-solid fa-star" style="font-size:1rem;color:var(--beige-deep);margin:0"></i></div>
    <div class="label">Note moyenne</div>
  </div>
</div>

<!-- LOGEMENTS -->
<div class="section-logements" id="logements">
  <div class="section-header">
    <p class="section-label"><i class="fa-solid fa-building"></i> Nos logements</p>
    <h2 class="section-title">Découvrez nos logements<br>au Sénégal</h2>
    <p class="section-sub">Sélectionnez votre destination et réservez en quelques minutes.</p>
  </div>

  <!-- Recherche -->
  <div class="search-bar">
    <input type="text" placeholder="   Rechercher une ville, un quartier…">
    <select>
      <option value="">Toutes les villes</option>
      <option>Dakar</option><option>Saly</option><option>Thiès</option>
      <option>Saint-Louis</option><option>Ziguinchor</option><option>Mbour</option>
    </select>
    <button><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
  </div>

  <!-- Filtres -->
  <div class="filter-row">
    <button class="filter-btn active" data-filter=""><i class="fa-solid fa-border-all"></i> Tous</button>
    <button class="filter-btn" data-filter="disponible"><i class="fa-solid fa-circle-check"></i> Disponible</button>
    <button class="filter-btn" data-filter="appartement"><i class="fa-solid fa-building"></i> Appartement</button>
    <button class="filter-btn" data-filter="villa"><i class="fa-solid fa-house-chimney"></i> Villa</button>
    <button class="filter-btn" data-filter="studio"><i class="fa-solid fa-couch"></i> Studio</button>
    <button class="filter-btn" data-filter="maison"><i class="fa-solid fa-house"></i> Maison</button>
  </div>

  <!-- Grille -->
  <div class="listings-grid">

    <!-- Carte 1 : Appartement -->
    <div class="property-card" data-type="appartement" data-statut="disponible"
         onclick="window.location='logement.php?id=1'">
      <div class="card-img">
        <img src="images/Ap.jpg" alt="Appartement Dakar">
        <div class="card-img-badge">
          <span class="badge badge-free"><i class="fa-solid fa-circle-check"></i> Disponible</span>
        </div>
        <button class="card-wishlist" onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="card-title">Appartement Moderne — Dakar</div>
          <div class="card-rating"><i class="fa-solid fa-star"></i> 4.9 <span style="color:#ccc">(32)</span></div>
        </div>
        <div class="card-location"><i class="fa-solid fa-location-dot"></i> Almadies, Dakar</div>
        <div class="card-features">
          <div class="feature"><i class="fa-solid fa-bed"></i> 2 ch.</div>
          <div class="feature"><i class="fa-solid fa-bath"></i> 1 sdb</div>
          <div class="feature"><i class="fa-solid fa-wifi"></i> WiFi</div>
          <div class="feature"><i class="fa-solid fa-snowflake"></i> Clim</div>
        </div>
        <div class="card-footer">
          <div class="price">45 000 <span>FCFA / nuit</span></div>
          <button class="btn btn-primary" style="font-size:.82rem;padding:8px 16px">Voir Détails</button>
        </div>
      </div>
    </div>

    <!-- Carte 2 : Villa -->
    <div class="property-card" data-type="villa" data-statut="occupe"
         onclick="window.location='logement.php?id=2'">
      <div class="card-img">
        <img src="images/villa.jpg" alt="Villa Saly">
        <div class="card-img-badge">
          <span class="badge badge-busy"><i class="fa-solid fa-clock"></i> Sous-location</span>
        </div>
        <button class="card-wishlist" onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>
        <div class="card-date-badge"><i class="fa-solid fa-calendar"></i> Dispo le 12 Mai</div>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="card-title">Villa de Charme — Saly</div>
          <div class="card-rating"><i class="fa-solid fa-star"></i> 4.7 <span style="color:#ccc">(18)</span></div>
        </div>
        <div class="card-location"><i class="fa-solid fa-location-dot"></i> Saly Portudal</div>
        <div class="card-features">
          <div class="feature"><i class="fa-solid fa-bed"></i> 4 ch.</div>
          <div class="feature"><i class="fa-solid fa-person-swimming"></i> Piscine</div>
          <div class="feature"><i class="fa-solid fa-square-parking"></i> Parking</div>
          <div class="feature"><i class="fa-solid fa-wifi"></i> WiFi</div>
        </div>
        <div class="card-footer">
          <div class="price">120 000 <span>FCFA / nuit</span></div>
          <button class="btn btn-primary" style="font-size:.82rem;padding:8px 16px">Voir Détails</button>
        </div>
      </div>
    </div>

    <!-- Carte 3 : Studio -->
    <div class="property-card" data-type="studio" data-statut="disponible"
         onclick="window.location='logement.php?id=3'">
      <div class="card-img">
        <img src="images/studio.webp" alt="Studio Thiès">
        <div class="card-img-badge">
          <span class="badge badge-free"><i class="fa-solid fa-circle-check"></i> Disponible</span>
        </div>
        <button class="card-wishlist" onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="card-title">Studio Cosy — Thiès</div>
          <div class="card-rating"><i class="fa-solid fa-star"></i> 4.5 <span style="color:#ccc">(9)</span></div>
        </div>
        <div class="card-location"><i class="fa-solid fa-location-dot"></i> Centre-ville, Thiès</div>
        <div class="card-features">
          <div class="feature"><i class="fa-solid fa-bed"></i> 1 ch.</div>
          <div class="feature"><i class="fa-solid fa-utensils"></i> Cuisine</div>
          <div class="feature"><i class="fa-solid fa-wifi"></i> WiFi</div>
          <div class="feature"><i class="fa-solid fa-tv"></i> Projecteur</div>
        </div>
        <div class="card-footer">
          <div class="price">25 000 <span>FCFA / nuit</span></div>
          <button class="btn btn-primary" style="font-size:.82rem;padding:8px 16px">Voir Détails</button>
        </div>
      </div>
    </div>

    <!-- Carte 4 : Penthouse -->
    <div class="property-card" data-type="appartement" data-statut="disponible"
         onclick="window.location='logement.php?id=4'">
      <div class="card-img">
        <img src="images/penthouse.webp" alt="Penthouse Dakar">
        <div class="card-img-badge">
          <span class="badge badge-free"><i class="fa-solid fa-circle-check"></i> Disponible</span>
        </div>
        <button class="card-wishlist" onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="card-title">Penthouse Vue Océan — Dakar</div>
          <div class="card-rating"><i class="fa-solid fa-star"></i> 5.0 <span style="color:#ccc">(47)</span></div>
        </div>
        <div class="card-location"><i class="fa-solid fa-location-dot"></i> Plateau, Dakar</div>
        <div class="card-features">
          <div class="feature"><i class="fa-solid fa-bed"></i> 3 ch.</div>
          <div class="feature"><i class="fa-solid fa-sun"></i> Terrasse</div>
          <div class="feature"><i class="fa-solid fa-snowflake"></i> Clim</div>
          <div class="feature"><i class="fa-solid fa-square-parking"></i> Parking</div>
        </div>
        <div class="card-footer">
          <div class="price">95 000 <span>FCFA / nuit</span></div>
          <button class="btn btn-primary" style="font-size:.82rem;padding:8px 16px">Voir Détails</button>
        </div>
      </div>
    </div>

    <!-- Carte 5 : Maison Ziguinchor -->
    <div class="property-card" data-type="maison" data-statut="sous_location"
         onclick="window.location='logement.php?id=5'">
      <div class="card-img">
        <img src="images/maison.jpg" alt="Maison Ziguinchor">
        <div class="card-img-badge">
          <span class="badge badge-sub"><i class="fa-solid fa-rotate"></i> Sous-location</span>
        </div>
        <button class="card-wishlist" onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>
        <div class="card-date-badge"><i class="fa-solid fa-calendar"></i> Dispo le 3 Juin</div>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="card-title">Maison Tropicale — Ziguinchor</div>
          <div class="card-rating"><i class="fa-solid fa-star"></i> 4.6 <span style="color:#ccc">(14)</span></div>
        </div>
        <div class="card-location"><i class="fa-solid fa-location-dot"></i> Ziguinchor, Casamance</div>
        <div class="card-features">
          <div class="feature"><i class="fa-solid fa-bed"></i> 3 ch.</div>
          <div class="feature"><i class="fa-solid fa-leaf"></i> Jardin</div>
          <div class="feature"><i class="fa-solid fa-person-swimming"></i> Piscine</div>
          <div class="feature"><i class="fa-solid fa-square-parking"></i> Parking</div>
        </div>
        <div class="card-footer">
          <div class="price">55 000 <span>FCFA / nuit</span></div>
          <button class="btn btn-primary" style="font-size:.82rem;padding:8px 16px">Voir Détails</button>
        </div>
      </div>
    </div>

    <!-- Carte 6 : Saint-Louis -->
    <div class="property-card" data-type="maison" data-statut="disponible"
         onclick="window.location='logement.php?id=6'">
      <div class="card-img">
        <img src="images/saintlouis.jpg" alt="Saint-Louis">
        <div class="card-img-badge">
          <span class="badge badge-free"><i class="fa-solid fa-circle-check"></i> Disponible</span>
        </div>
        <button class="card-wishlist" onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="card-title">Maison Coloniale — Saint-Louis</div>
          <div class="card-rating"><i class="fa-solid fa-star"></i> 4.8 <span style="color:#ccc">(27)</span></div>
        </div>
        <div class="card-location"><i class="fa-solid fa-location-dot"></i>Saint-Louis</div>
        <div class="card-features">
          <div class="feature"><i class="fa-solid fa-bed"></i> 2 ch.</div>
          <div class="feature"><i class="fa-solid fa-landmark"></i> Patrimoine</div>
          <div class="feature"><i class="fa-solid fa-wifi"></i> WiFi</div>
          <div class="feature"><i class="fa-solid fa-seedling"></i> Jardin</div>
        </div>
        <div class="card-footer">
          <div class="price">38 000 <span>FCFA / nuit</span></div>
          <button class="btn btn-primary" style="font-size:.82rem;padding:8px 16px">Voir Détails</button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- AVANTAGES -->
<div class="features-wrap">
  <div class="features-inner">
    <div class="feature-card">
      <div class="feature-icon"><i class="fa-solid fa-calendar-check"></i></div>
      <h4>Réservation Simple</h4>
      <p>Sélectionnez vos dates, choisissez le nombre de nuits et confirmez en quelques secondes.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
      <h4>Paiement Sécurisé</h4>
      <p>Payez directement avec les solutions locales que vous connaissez.</p>
      <!-- <div class="payment-badges">
        <div class="pay-badge">📲 Wave</div>
        <div class="pay-badge">🟠 Orange Money</div>
      </div> -->

      <div class="payment-badges">
        <div class="pay-badge">
          <img src="images/wave.png" alt="Wave" class="pay-logo">
        </div>
        <div class="pay-badge">
          <img src="images/orange.png" alt="Orange Money" class="pay-logo">
        </div>
      </div>
      
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fa-solid fa-list-check"></i></div>
      <h4>Conseils de Séjour</h4>
      <p>Chaque logement affiche ses règles et consignes avant votre réservation. Zéro mauvaise surprise.</p>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer id="contact">
  <div class="footer-inner">
    <div class="footer-logo">Sen Location  <img src="images/senegal.jpg" alt="Sénégal" class="nav-flag"></div>
    <div class="footer-links">
      <a href="#"><i class="fa-solid fa-circle-question"></i> FAQ</a>
      <a href="#"><i class="fa-solid fa-headset"></i> Assistance</a>
      <a href="#"><i class="fa-solid fa-file-contract"></i> Conditions</a>
      <a href="#"><i class="fa-solid fa-plus"></i> Ajouter un logement</a>
    </div>
    <div class="footer-copy">© 2024 Sen Location · Tous droits réservés</div>
  </div>
</footer>

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

    <!-- Connexion -->
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
      <p style="text-align:center;margin-top:12px;font-size:.86rem;color:var(--text-soft)">
        Mot de passe oublié ? <a href="#" style="color:var(--brown-mid)">Réinitialiser</a>
      </p>
    </div>

    <!-- Inscription -->
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
          <label><i class="fa-solid fa-lock"></i> Mot de passe * <small style="font-weight:300">(min. 6 car.)</small></label>
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

<!-- SCRIPTS -->
<script src="assets/js/main.js"></script>
<script>
  // Hero background avec animation
  window.addEventListener('load', () => {
    document.getElementById('heroBg').classList.add('loaded');
  });

  // Formulaire hero
  document.getElementById('heroLoginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    handleLogin(this);
  });

  // Toggle favori (cœur)
  function toggleWish(btn) {
    const icon = btn.querySelector('i');
    const liked = icon.classList.contains('fa-solid');
    icon.classList.toggle('fa-regular', liked);
    icon.classList.toggle('fa-solid', !liked);
    btn.classList.toggle('liked', !liked);
  }
</script>

</body>
</html>
