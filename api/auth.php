<?php
// ============================================================
//  Sen Location — api/auth.php
//  Gère : connexion, inscription, déconnexion
// ============================================================

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$action = $_POST['action'] ?? '';

switch ($action) {

    // ── CONNEXION ─────────────────────────────────────────
    case 'login':

        $email = sanitize($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';

        // Validation basique
        if (!$email || !$mdp) {
            echo json_encode([
                'success' => false,
                'message' => 'Email et mot de passe sont obligatoires.'
            ]);
            exit;
        }

        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérifier email + mot de passe
        if (!$user || !password_verify($mdp, $user['mot_de_passe'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.'
            ]);
            exit;
        }

        // Stocker en session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nom']  = $user['nom'];
        $_SESSION['user_role'] = $user['role'];

        // Redirection selon le rôle
        // $redirect = ($user['role'] === 'admin' || $user['role'] === 'proprietaire')
        //     ? '/senlocation/admin/'
        //     : '/senlocation/index.php';

        // Redirection selon le rôle
        $redirect = ($user['role'] === 'admin')
            ? '/senlocation/admin/'
            : '/senlocation/index.php';

        echo json_encode([
            'success'  => true,
            'message'  => 'Connexion réussie ! Bienvenue ' . $user['nom'],
            'redirect' => $redirect,
            'user'     => [
                'nom'  => $user['nom'],
                'role' => $user['role']
            ]
        ]);
        break;


    // ── INSCRIPTION ───────────────────────────────────────
    case 'register':

        $nom   = sanitize($_POST['nom']          ?? '');
        $email = sanitize($_POST['email']         ?? '');
        $tel   = sanitize($_POST['telephone']     ?? '');
        $mdp   = $_POST['mot_de_passe']           ?? '';
        $mdp2  = $_POST['confirmer_mdp']          ?? '';

        // Validations
        if (!$nom || !$email || !$mdp || !$mdp2) {
            echo json_encode([
                'success' => false,
                'message' => 'Tous les champs obligatoires (*) doivent être remplis.'
            ]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Adresse email invalide.'
            ]);
            exit;
        }

        if (strlen($mdp) < 6) {
            echo json_encode([
                'success' => false,
                'message' => 'Le mot de passe doit faire au moins 6 caractères.'
            ]);
            exit;
        }

        if ($mdp !== $mdp2) {
            echo json_encode([
                'success' => false,
                'message' => 'Les mots de passe ne correspondent pas.'
            ]);
            exit;
        }

        // Vérifier si email déjà utilisé
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Cet email est déjà utilisé. Connectez-vous.'
            ]);
            exit;
        }

        // Hash du mot de passe (sécurisé)
        $hash = password_hash($mdp, PASSWORD_BCRYPT);

        // Insérer en base
        $ins = $pdo->prepare("
            INSERT INTO users (nom, email, telephone, mot_de_passe)
            VALUES (?, ?, ?, ?)
        ");
        $ins->execute([$nom, $email, $tel, $hash]);
        $newId = $pdo->lastInsertId();

        // Connecter automatiquement
        $_SESSION['user_id']   = $newId;
        $_SESSION['user_nom']  = $nom;
        $_SESSION['user_role'] = 'client';

        echo json_encode([
            'success'  => true,
            'message'  => 'Compte créé avec succès ! Bienvenue ' . $nom,
            'redirect' => '/senlocation/index.php'
        ]);
        break;


    // ── DÉCONNEXION ───────────────────────────────────────
    case 'logout':

        session_destroy();

        echo json_encode([
            'success'  => true,
            'redirect' => '/senlocation/index.php'
        ]);
        break;


    // ── ACTION INCONNUE ───────────────────────────────────
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action inconnue.'
        ]);
}
