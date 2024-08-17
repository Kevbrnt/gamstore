<?php
session_start();
require '../../src/models/connect_bdd.php';
require "../../config/config.php";

require __DIR__ . '/../../build/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
error_reporting(0); // Désactive l'affichage des erreurs

function sendJsonResponse($success, $message, $error = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'error' => $error
    ]);
    exit();
}

if (!isset($_SESSION['id'])) {
    sendJsonResponse(false, 'Utilisateur non connecté.');
}

$user_id = $_SESSION['id'];

function sanitizeString($str) {
    return htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
}

function sendEmail($to, $subject, $body, $isHtml = true) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['USERNAME_MAIL'];
        $mail->Password   = $_ENV['PASSWORD_MAIL'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['PORT_MAIL'];

        $mail->setFrom($_ENV['USERNAME_MAIL'], 'Gamestore');
        $mail->addAddress($to);
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

function sendConfirmationEmail($email, $username, $first_name, $last_name) {
    $subject = htmlspecialchars($username) . ' - Vos informations ont été modifiées !';
    $body = "<h1>Bonjour " . htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) . ",</h1>
         <p>Vos informations personnelles ont été mises à jour avec succès !</p>
         <p>Si vous n'êtes pas à l'origine de ce changement, veuillez contacter le service client immédiatement !</p>
         <p>À bientôt sur Gamestore !</p>
         <p>Cordialement,<br>L'équipe Gamestore</p>";

    return sendEmail($email, $subject, $body);
}

function updatePassword($pdo, $email, $newPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    return $stmt->execute([':password' => $hashedPassword, ':email' => $email]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $username = sanitizeString($_POST['username'] ?? '');
    $first_name = sanitizeString($_POST['first_name'] ?? '');
    $last_name = sanitizeString($_POST['last_name'] ?? '');
    $address = sanitizeString($_POST['address'] ?? '');
    $newPassword = $_POST['password'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Email invalide.');
    }

    try {
        $pdo->beginTransaction();

        // Mise à jour des informations de l'utilisateur
        $query = "UPDATE users SET email = :email, username = :username, first_name = :first_name, last_name = :last_name, address = :address WHERE id = :user_id";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            'email' => $email,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $address,
            'user_id' => $user_id
        ]);

        if (!$result) {
            throw new Exception('Erreur lors de la mise à jour des informations.');
        }

        // Mise à jour du mot de passe si fourni
        if ($newPassword) {
            if (!updatePassword($pdo, $email, $newPassword)) {
                throw new Exception('Échec de la mise à jour du mot de passe.');
            }
        }

        $pdo->commit();

        // Envoi de l'email de confirmation
        if (sendConfirmationEmail($email, $username, $first_name, $last_name)) {
            sendJsonResponse(true, 'Informations mises à jour avec succès et email de confirmation envoyé.');
        } else {
            sendJsonResponse(true, 'Informations mises à jour avec succès, mais échec de l\'envoi de l\'email de confirmation.');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse(false, 'Une erreur est survenue.', $e->getMessage());
    }
} else {
    sendJsonResponse(false, 'Méthode de requête non valide.');
}
?>