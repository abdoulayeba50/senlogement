<?php
// ============================================================
//  Sen Location — api/reservation.php
//  Gère : création, annulation des réservations
// ============================================================

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ── CRÉER UNE RÉSERVATION ─────────────────────────────
    case 'creer':

        // Vérifier connexion
        if (!isLoggedIn()) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous devez être connecté pour réserver.'
            ]);
            exit;
        }

        $user    = getCurrentUser();
        $logId   = (int)($_POST['logement_id']   ?? 0);
        $arrivee = $_POST['date_arrivee']         ?? '';
        $depart  = $_POST['date_depart']          ?? '';
        $moyen   = $_POST['moyen_paiement']        ?? 'wave';
        $notes   = trim(strip_tags($_POST['notes'] ?? ''));

        // Validations de base
        if (!$logId || !$arrivee || !$depart) {
            echo json_encode([
                'success' => false,
                'message' => 'Données incomplètes. Sélectionnez vos dates.'
            ]);
            exit;
        }

        $d1 = new DateTime($arrivee);
        $d2 = new DateTime($depart);

        if ($d2 <= $d1) {
            echo json_encode([
                'success' => false,
                'message' => "La date de départ doit être après la date d'arrivée."
            ]);
            exit;
        }

        $nbNuits = (int)$d1->diff($d2)->days;

        $pdo = getDB();

        // Vérifier que le logement existe
        $logStmt = $pdo->prepare("SELECT * FROM logements WHERE id = ?");
        $logStmt->execute([$logId]);
        $log = $logStmt->fetch();

        if (!$log) {
            echo json_encode([
                'success' => false,
                'message' => 'Logement introuvable.'
            ]);
            exit;
        }

        // ✅ CORRECTION : on ne bloque plus sur le champ statut (souvent désynchronisé).
        // On vérifie directement s'il existe un chevauchement de dates dans les réservations actives.
        $chkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE logement_id = ?
              AND statut NOT IN ('annulee')
              AND date_arrivee < ?
              AND date_depart  > ?
        ");
        $chkStmt->execute([$logId, $depart, $arrivee]);

        if ((int)$chkStmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ce logement est déjà réservé pour ces dates.'
            ]);
            exit;
        }

        // Calcul du prix total
        $prixTotal = $nbNuits * $log['prix_nuit'];

        // Insérer la réservation
        $insStmt = $pdo->prepare("
            INSERT INTO reservations
              (logement_id, client_id, date_arrivee, date_depart,
               nb_nuits, prix_total, moyen_paiement, notes_client)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insStmt->execute([
            $logId,
            $user['id'],
            $arrivee,
            $depart,
            $nbNuits,
            $prixTotal,
            $moyen,
            $notes
        ]);

        $resaId = $pdo->lastInsertId();

        // ✅ Mettre le logement en "occupé" (synchronisation du statut)
        $pdo->prepare("
            UPDATE logements SET statut = 'occupe', date_dispo = ?
            WHERE id = ?
        ")->execute([$depart, $logId]);

        // Instructions de paiement selon le moyen choisi
        $webmaster = '778890234';
        $montantFormate = number_format($prixTotal, 0, ',', ' ');

        $instructions = match($moyen) {
            'wave'         => "Envoyez {$montantFormate} FCFA au +221 {$webmaster} via Wave. Référence : RESA-{$resaId}",
            'orange_money' => "Envoyez {$montantFormate} FCFA au +221 {$webmaster} via Orange Money. Référence : RESA-{$resaId}",
            default        => "Réglez {$montantFormate} FCFA en espèces à la remise des clés. Référence : RESA-{$resaId}",
        };

        echo json_encode([
            'success'        => true,
            'message'        => 'Réservation créée avec succès !',
            'reservation_id' => $resaId,
            'prix_total'     => $prixTotal,
            'nb_nuits'       => $nbNuits,
            'instructions'   => $instructions,
            'webmaster'      => $webmaster,
            'redirect'       => 'profil.php?tab=reservations&success=1'
        ]);
        break;


    // ── ANNULER UNE RÉSERVATION ───────────────────────────
    case 'annuler':

        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Non connecté.']);
            exit;
        }

        $user   = getCurrentUser();
        $resaId = (int)($_POST['id'] ?? 0);

        if (!$resaId) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }

        $pdo = getDB();

        // Vérifier que la réservation appartient bien à cet utilisateur
        $stmt = $pdo->prepare("
            SELECT * FROM reservations
            WHERE id = ? AND client_id = ?
        ");
        $stmt->execute([$resaId, $user['id']]);
        $resa = $stmt->fetch();

        if (!$resa) {
            echo json_encode(['success' => false, 'message' => 'Réservation introuvable.']);
            exit;
        }

        if ($resa['statut'] === 'annulee') {
            echo json_encode(['success' => false, 'message' => 'Déjà annulée.']);
            exit;
        }

        // Annuler la réservation
        $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?")
            ->execute([$resaId]);

        // ✅ Vérifier s'il reste des réservations actives futures sur ce logement
        $activeStmt = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE logement_id = ?
              AND statut NOT IN ('annulee')
              AND id != ?
              AND date_depart > CURDATE()
        ");
        $activeStmt->execute([$resa['logement_id'], $resaId]);

        // Si plus aucune réservation active future → remettre le logement disponible
        if ((int)$activeStmt->fetchColumn() === 0) {
            $pdo->prepare("
                UPDATE logements SET statut = 'disponible', date_dispo = NULL
                WHERE id = ?
            ")->execute([$resa['logement_id']]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Réservation annulée avec succès.'
        ]);
        break;


    // ── ACTION INCONNUE ───────────────────────────────────
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
}