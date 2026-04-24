<?php
// ============================================================
//  Sen Location — includes/db.php
//  Connexion à la base de données MySQL via PDO
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'senlocation');
define('DB_USER',    'root');
define('DB_PASS',    '');          // ← Laisser vide sur XAMPP par défaut
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    // Instance unique (singleton)
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";dbname="    . DB_NAME
             . ";charset="   . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En prod : logger l'erreur sans l'afficher
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Erreur de connexion à la base de données.'
            ]));
        }
    }

    return $pdo;
}
