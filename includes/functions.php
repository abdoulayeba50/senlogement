<?php
// ============================================================
//  Sen Location — includes/functions.php
//  Fonctions utilitaires partagées dans tout le projet
// ============================================================

require_once __DIR__ . '/db.php';

// ── Session ───────────────────────────────────────────────

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT id, nom, email, telephone, role
         FROM users WHERE id = ?"
    );
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// Redirige vers login si non connecté
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /senlocation/index.php?modal=login');
        exit;
    }
}

// Redirige si pas admin
function requireAdmin(): void {
    requireLogin();
    $user = getCurrentUser();
    if ($user['role'] !== 'admin') {
        header('Location: /senlocation/index.php');
        exit;
    }
}

// ── Sécurité ──────────────────────────────────────────────

// Échappe le HTML pour affichage sécurisé
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Nettoie une chaîne (retire balises et espaces)
function sanitize(string $str): string {
    return trim(strip_tags($str));
}

// ── Logements ─────────────────────────────────────────────

function getLogements(array $filtres = []): array {
    $pdo = getDB();

    $sql = "SELECT l.*,
                   v.nom AS ville_nom,
                   (SELECT chemin FROM logement_images
                    WHERE logement_id = l.id AND principale = 1
                    LIMIT 1) AS image_principale
            FROM logements l
            JOIN villes v ON l.ville_id = v.id
            WHERE 1=1";

    $params = [];

    if (!empty($filtres['ville_id'])) {
        $sql .= " AND l.ville_id = ?";
        $params[] = $filtres['ville_id'];
    }
    if (!empty($filtres['type'])) {
        $sql .= " AND l.type = ?";
        $params[] = $filtres['type'];
    }
    if (!empty($filtres['statut'])) {
        $sql .= " AND l.statut = ?";
        $params[] = $filtres['statut'];
    }
    if (!empty($filtres['search'])) {
        $sql .= " AND (l.titre LIKE ? OR l.adresse LIKE ? OR v.nom LIKE ?)";
        $q = '%' . $filtres['search'] . '%';
        $params = array_merge($params, [$q, $q, $q]);
    }

    $sql .= " ORDER BY l.note_moyenne DESC, l.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getLogementById(int $id): ?array {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT l.*, v.nom AS ville_nom,
               u.nom AS proprietaire_nom,
               u.telephone AS proprio_tel
        FROM logements l
        JOIN villes v ON l.ville_id = v.id
        JOIN users u ON l.proprietaire_id = u.id
        WHERE l.id = ?
    ");
    $stmt->execute([$id]);
    $logement = $stmt->fetch();
    if (!$logement) return null;

    // Images du logement
    $imgStmt = $pdo->prepare(
        "SELECT * FROM logement_images
         WHERE logement_id = ?
         ORDER BY principale DESC"
    );
    $imgStmt->execute([$id]);
    $logement['images'] = $imgStmt->fetchAll();

    // Avis du logement
    $avisStmt = $pdo->prepare("
        SELECT a.*, u.nom AS client_nom
        FROM avis a
        JOIN users u ON a.client_id = u.id
        WHERE a.logement_id = ?
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $avisStmt->execute([$id]);
    $logement['avis'] = $avisStmt->fetchAll();

    return $logement;
}

// ── Disponibilité ─────────────────────────────────────────

function isDisponible(int $logementId, string $arrivee, string $depart): bool {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations
        WHERE logement_id = ?
          AND statut NOT IN ('annulee')
          AND date_arrivee < ?
          AND date_depart  > ?
    ");
    $stmt->execute([$logementId, $depart, $arrivee]);
    return (int) $stmt->fetchColumn() === 0;
}

// ── Formatage ─────────────────────────────────────────────

function formatPrix(float $prix): string {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}

function badgeStatut(string $statut, ?string $dateDispo = null): string {
    $map = [
        'disponible'    => ['Disponible',   'badge-free'],
        'occupe'        => ['Occupé',        'badge-busy'],
        'sous_location' => ['Sous-location', 'badge-sub'],
        'maintenance'   => ['Maintenance',   'badge-maint'],
    ];

    $info  = $map[$statut] ?? ['Inconnu', ''];
    $label = $info[0];
    $cls   = $info[1];

    if ($statut === 'occupe' && $dateDispo) {
        $label .= ' · Dispo ' . date('d M', strtotime($dateDispo));
    }

    return "<span class=\"badge {$cls}\">{$label}</span>";
}
