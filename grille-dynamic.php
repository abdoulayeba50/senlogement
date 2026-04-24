  <!-- Grille dynamique depuis la DB -->
  <div class="listings-grid">
    <?php
    // Connexion DB
    require_once __DIR__ . '/includes/db.php';
    $pdo = getDB();

    // Filtres depuis l'URL
    $filtre_type   = $_GET['type']   ?? '';
    $filtre_ville  = $_GET['ville']  ?? '';
    $filtre_search = $_GET['q']      ?? '';

    // Requête
    $sql = "SELECT l.*, v.nom AS ville_nom,
                   (SELECT chemin FROM logement_images
                    WHERE logement_id = l.id AND principale = 1
                    LIMIT 1) AS image_principale
            FROM logements l
            JOIN villes v ON l.ville_id = v.id
            WHERE 1=1";
    $params = [];

    if ($filtre_type) {
        $sql .= " AND l.type = ?";
        $params[] = $filtre_type;
    }
    if ($filtre_ville) {
        $sql .= " AND v.nom LIKE ?";
        $params[] = '%' . $filtre_ville . '%';
    }
    if ($filtre_search) {
        $sql .= " AND (l.titre LIKE ? OR l.adresse LIKE ? OR v.nom LIKE ?)";
        $q = '%' . $filtre_search . '%';
        $params = array_merge($params, [$q, $q, $q]);
    }

    $sql .= " ORDER BY l.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logements = $stmt->fetchAll();

    if (empty($logements)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-soft)">
        <i class="fa-solid fa-building" style="font-size:3rem;display:block;margin-bottom:14px;color:var(--beige-deep)"></i>
        <p>Aucun logement trouvé.</p>
        <a href="index.php" class="btn btn-outline" style="margin-top:16px">
          <i class="fa-solid fa-xmark"></i> Effacer les filtres
        </a>
      </div>

    <?php else: foreach ($logements as $log):

      // Badge statut
      $badges = [
        'disponible'    => ['badge-free', 'fa-circle-check', 'Disponible'],
        'occupe'        => ['badge-busy', 'fa-clock',        'Occupé'],
        'sous_location' => ['badge-sub',  'fa-rotate',       'Sous-location'],
      ];
      $b = $badges[$log['statut']] ?? ['badge-free','fa-circle-check','Disponible'];

    ?>
    <div class="property-card"
         data-type="<?= htmlspecialchars($log['type']) ?>"
         data-statut="<?= htmlspecialchars($log['statut']) ?>"
         onclick="window.location='logement.php?id=<?= $log['id'] ?>'">

      <div class="card-img">
        <?php if ($log['image_principale']): ?>
          <img src="<?= htmlspecialchars($log['image_principale']) ?>"
               alt="<?= htmlspecialchars($log['titre']) ?>">
        <?php else: ?>
          <!-- Image par défaut si pas de photo -->
          <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--beige-deep),var(--brown-light));display:flex;align-items:center;justify-content:center;font-size:3rem">
            🏠
          </div>
        <?php endif; ?>

        <div class="card-img-badge">
          <span class="badge <?= $b[0] ?>">
            <i class="fa-solid <?= $b[1] ?>"></i> <?= $b[2] ?>
          </span>
        </div>

        <button class="card-wishlist"
                onclick="event.stopPropagation(); toggleWish(this)">
          <i class="fa-regular fa-heart"></i>
        </button>

        <?php if ($log['statut'] !== 'disponible' && !empty($log['date_dispo'])): ?>
          <div class="card-date-badge">
            <i class="fa-solid fa-calendar"></i>
            Dispo le <?= date('d M', strtotime($log['date_dispo'])) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card-body">
        <div class="card-meta">
          <div class="card-title"><?= htmlspecialchars($log['titre']) ?></div>
          <div class="card-rating">
            <i class="fa-solid fa-star"></i>
            <?= number_format($log['note_moyenne'], 1) ?>
          </div>
        </div>

        <div class="card-location">
          <i class="fa-solid fa-location-dot"></i>
          <?= htmlspecialchars($log['adresse'] ?: $log['ville_nom']) ?>
        </div>

        <div class="card-features">
          <div class="feature">
            <i class="fa-solid fa-bed"></i> <?= $log['nb_chambres'] ?> ch.
          </div>
          <div class="feature">
            <i class="fa-solid fa-bath"></i> <?= $log['nb_salles_bain'] ?> sdb
          </div>
          <?php if ($log['wifi']): ?>
            <div class="feature"><i class="fa-solid fa-wifi"></i> WiFi</div>
          <?php endif; ?>
          <?php if ($log['climatisation']): ?>
            <div class="feature"><i class="fa-solid fa-snowflake"></i> Clim</div>
          <?php endif; ?>
          <?php if ($log['piscine']): ?>
            <div class="feature"><i class="fa-solid fa-person-swimming"></i> Piscine</div>
          <?php endif; ?>
        </div>

        <div class="card-footer">
          <div class="price">
            <?= number_format($log['prix_nuit'], 0, ',', ' ') ?>
            <span>FCFA / nuit</span>
          </div>
          <button class="btn btn-primary"
                  style="font-size:.82rem;padding:8px 16px">
            Voir Détails
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>

  </div><!-- /listings-grid -->
