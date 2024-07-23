<?php
require 'connect_bdd.php';
require 'vendor/autoload.php';  // Inclure le fichier autoload de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => 'Une erreur est survenue.'];  // Message d'erreur par défaut

if (
    isset($_POST['username']) && !empty($_POST['username']) &&
    isset($_POST['password']) && !empty($_POST['password']) &&
    isset($_POST['first_name']) && !empty($_POST['first_name']) &&
    isset($_POST['last_name']) && !empty($_POST['last_name']) &&
    isset($_POST['email']) && !empty($_POST['email']) &&
    isset($_POST['address']) && !empty($_POST['address'])
) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $passwordMes = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    // Vérifier si le username est déjà utilisé
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $response = ['success' => false, 'message' => 'Le pseudo est déjà utilisé.'];
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO users (image_url, username, password, first_name, last_name, email, address, role, add_at) 
            VALUES ('asset/profils/defaut.png', :username, :password, :first_name, :last_name, :email, :address, 'Employés', NOW())
        ");
        $success = $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':address' => $address
        ]);

        if ($success) {
            // Envoi de l'email de confirmation
            $mail = new PHPMailer(true); // Pass true pour activer les exceptions

            try {
                // Paramètres du serveur
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Remplacez par le serveur SMTP de votre choix
                $mail->SMTPAuth = true;
                $mail->Username   = 'praskavisuelle@gmail.com'; // Votre adresse e-mail
                $mail->Password   = 'jezg kshu phxl rmnw'; // Votre mot de passe
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Destinataires
                $mail->setFrom('administration@gamestore.com', 'Administrateur Gamestore');
                $mail->addAddress($email);

                // Contenu
                $mail->isHTML(true);
                $mail->Subject = 'Confirmation de création de compte';
                $mail->Body    = '<h1>Bonjour ' . htmlspecialchars($first_name) . htmlspecialchars($last_name).',</h1>
                                  <p>Votre compte a été créé avec succès.</p>
                                  <p>Voici vos identifiants :</p>
                                  <p>Pseudo : ' . htmlspecialchars($username) . '</p>
                                  <p>Mot de passe: ' . htmlspecialchars($passwordMes) . '</p>
                                  <p>Bienvenue dans l\'équipe Gamestore</p>
                                  <p>l\'administration Gamestore</p>';

                $mail->AltBody = 'Bonjour ' . htmlspecialchars($first_name) . ', Votre compte a été créé avec succès.';

                $mail->send();
                $response = ['success' => true, 'message' => 'Employé ajouté avec succès !'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Message non envoyé. Erreur: ' . $mail->ErrorInfo];
            }
        } else {
            $response = ['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'employé.'];
        }
    }
} else {
    $response = ['success' => false, 'message' => 'Veuillez remplir tous les champs.'];
}

echo json_encode($response);
?>
