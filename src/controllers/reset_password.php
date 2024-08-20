<?php

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../utils/session_management.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../utils/csrf_token.php';
require_once __DIR__ . '/../models/connect_bdd.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction de journalisation personnalisée
function custom_error_log($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, __DIR__ . '/reset_password_error.log');
}

custom_error_log("Début de l'exécution de reset_password.php");

require_once __DIR__ . '/../../build/vendor/autoload.php';
custom_error_log("Autoload chargé");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();
custom_error_log("Variables d'environnement chargées");

// Utilisez PHPMailer pour envoyer des emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../build/vendor/autoload.php';

header('Content-Type: application/json');

// Vérifiez si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Vérifiez le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
    exit;
}

// Récupérez l'email
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
    exit;
}

try {
    // Vérifiez si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Ne pas révéler si l'email existe ou non pour des raisons de sécurité
        echo json_encode(['success' => true, 'message' => 'Si votre email est enregistré, vous recevrez un lien de réinitialisation.']);
        exit;
    }

    // Générez un token unique
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600); // Le token expire dans 1 heure

    // Stockez le token dans la base de données
    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires) VALUES (:user_id, :token, :expires)");
    $stmt->execute([
        ':user_id' => $user['id'],
        ':token' => $token,
        ':expires' => $expires
    ]);

    // Configurez PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['HOST_MAIL'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['USERNAME_MAIL'];
    $mail->Password = $_ENV['PASSWORD_MAIL'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['PORT_MAIL'];

// Paramètres de l'email
    $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Réinitialisation de votre mot de passe';

    $baseUrl = rtrim($_ENV['APP_URL'], '/');
    $resetUrl = $baseUrl . "/src/Pages/password_change.php?token=" . $token;

// Log de l'URL pour débogage
    error_log("URL de réinitialisation générée : " . $resetUrl);

// Corps de l'email
    $mail->Body = "
<html lang='fr'>
<body>
    <h2>Réinitialisation de votre mot de passe</h2>
    <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour procéder :</p>
    <p><a href='{$resetUrl}'>Réinitialiser mon mot de passe</a></p>
    <p>Ce lien expirera dans 1 heure.</p>
    <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
</body>
</html>
";

    $mail->send();

    custom_error_log("Base URL: " . $baseUrl);
    custom_error_log("Reset URL: " . $resetUrl);
    custom_error_log("Réponse JSON : " . json_encode(['success' => true, 'message' => 'Si votre email est enregistré, vous recevrez un lien de réinitialisation.']));

    echo json_encode(['success' => true, 'message' => 'Si votre email est enregistré, vous recevrez un lien de réinitialisation.']);
    exit;

    echo json_encode(['success' => true, 'message' => 'Si votre email est enregistré, vous recevrez un lien de réinitialisation.']);
    exit;
} catch (Exception $e) {
    error_log('Erreur lors de la réinitialisation du mot de passe : ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.']);
    exit;
}

?>