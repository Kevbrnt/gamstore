<?php
session_start();
require __DIR__ . '/../../build/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

require_once '../../src/models/connect_bdd.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function updatePassword($pdo, $email, $newPassword) {
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        return $stmt->execute([':password' => $hashedPassword, ':email' => $email]);
    } catch (Exception $e) {
        return false;
    }
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
    $subject = htmlspecialchars($username) . ' - Votre mot de passe a été modifié !';
    $body = "<h1>Bonjour " . htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) . ",</h1>
             <p>Vous venez de modifier votre mot de passe !</p>
             <p>Si vous n'êtes pas à l'origine de ce changement, veuillez contacter le service client !</p>
             <p>À bientôt sur Gamestore !</p>
             <p>Cordialement,<br>L'équipe Gamestore</p>";

    return sendEmail($email, $subject, $body);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $newPassword = $_POST['password'] ?? null;

    if (!$email) {
        $response['message'] = 'Email invalide.';
    } elseif (!$newPassword) {
        $response['message'] = 'Mot de passe manquant.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $response['message'] = 'Utilisateur non trouvé.';
            } elseif (updatePassword($pdo, $email, $newPassword)) {
                if (sendConfirmationEmail($email, $user['username'], $user['first_name'], $user['last_name'])) {
                    $response['success'] = true;
                    $response['message'] = 'Mot de passe mis à jour et email envoyé.';
                } else {
                    $response['message'] = 'Mot de passe mis à jour, mais échec de l\'envoi de l\'email.';
                }
            } else {
                $response['message'] = 'Échec de la mise à jour du mot de passe.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Une erreur est survenue lors de la réinitialisation du mot de passe.';
        }
    }

    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête non valide.']);
}
?>