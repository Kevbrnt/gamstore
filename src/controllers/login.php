<?php
// Activer la journalisation des erreurs
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_errors.log');

// Fonction pour logger les erreurs et les messages
function custom_error_log($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, __DIR__ . '/login_errors.log');
}

// Démarrer la session si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Démarrer la capture de la sortie
ob_start();

try {
    custom_error_log("Début du traitement de login.php");

    require_once __DIR__ . '/../models/connect_bdd.php';
    require_once __DIR__ . '/../utils/session_management.php';
    require_once __DIR__ . '/../utils/csrf_token.php';

    custom_error_log("Fichiers requis chargés avec succès");

    custom_error_log("Session ID: " . session_id());
    custom_error_log("Session data: " . print_r($_SESSION, true));
    custom_error_log("Cookies: " . print_r($_COOKIE, true));

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);
    }

    $postedToken = $_POST['csrf_token'] ?? '';
    custom_error_log("Posted CSRF token: " . $postedToken);
    custom_error_log("Stored CSRF token: " . ($_SESSION['csrf_token'] ?? 'Not set'));

    if (!verifyCSRFToken($postedToken)) {
        custom_error_log("CSRF verification failed");
        throw new Exception("Token CSRF invalide");
    }

    custom_error_log("CSRF verification passed");

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

    custom_error_log("Tentative de connexion pour l'utilisateur: " . $username);

    if (empty($username) || empty($password)) {
        throw new Exception("Veuillez remplir tous les champs.");
    }

    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username OR email = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception("Nom d'utilisateur ou mot de passe incorrect.");
    }

    custom_error_log("Connexion réussie pour l'utilisateur: " . $username);

    $sessionInfo = startUserSession($user['id'], $rememberMe);
    custom_error_log("Session démarrée: " . print_r($sessionInfo, true));

    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $user['id']]);

    $redirect = '../../src/Pages/Accueil.php';
    if ($user['role'] === 'Administrateur') {
        $redirect = '../../src/Pages/espace_admin.php';
    } elseif ($user['role'] === 'Employés') {
        $redirect = '../../src/Pages/espace_employés.php';
    }

    custom_error_log("Redirection vers: " . $redirect);

    $newCsrfToken = generateCSRFToken();

    $responseData = [
        'success' => true,
        'message' => 'Connexion réussie.',
        'redirect' => $redirect,
        'csrf_token' => $newCsrfToken
    ];

} catch (PDOException $e) {
    custom_error_log("Erreur PDO: " . $e->getMessage());
    custom_error_log("Trace PDO: " . $e->getTraceAsString());
    $responseData = ['success' => false, 'message' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.'];
} catch (Exception $e) {
    custom_error_log("Erreur: " . $e->getMessage());
    custom_error_log("Trace: " . $e->getTraceAsString());
    $responseData = ['success' => false, 'message' => $e->getMessage()];
} finally {
    // Capturer toute sortie inattendue
    $output = ob_get_clean();
    if (!empty($output)) {
        custom_error_log("Sortie inattendue: " . $output);
    }

    echo json_encode($responseData);
    custom_error_log("Réponse JSON envoyée: " . json_encode($responseData));
}
?>