<?php
require 'connect_bdd.php';

// Inclure les classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger les fichiers PHPMailer
require 'vendor/autoload.php';

// Récupérer les données du formulaire
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$address = $_POST['address'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$username = $_POST['username'];

// Vérifier si l'email existe déjà
$stmt = $pdo->prepare("SELECT * FROM gamestoretp.users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(['success' => false, 'message' => 'Email déjà utilisé.']);
    exit;
}

// Insérer les données dans la base de données
$stmt = $pdo->prepare("INSERT INTO gamestoretp.users (first_name, last_name, email, address, password, username, image_url) VALUES (:first_name, :last_name, :email, :address, :password, :username, :image_url)");
$success = $stmt->execute([
    ':first_name' => $first_name,
    ':last_name' => $last_name,
    ':email' => $email,
    ':address' => $address,
    ':password' => $password,
    ':username' => $username,
    ':image_url' => 'asset/profils/defaut.png'
]);

if ($success) {
    // Envoyer l'email de confirmation
    $mail = new PHPMailer(true); // Instancier PHPMailer

    try {
        // Configurer le serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Remplacez par le serveur SMTP de votre fournisseur d'email
        $mail->SMTPAuth = true;
        $mail->Username = 'praskavisuelle@gmail.com'; // Remplacez par votre adresse email
        $mail->Password = 'jezg kshu phxl rmnw'; // Remplacez par le mot de passe de votre adresse email
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('noreply@gamestore.com', 'Gamestore');
        $mail->addAddress($email, $username);

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenue sur Gamestore !';
        $mail->Body    = "<h1>Bonjour " . $first_name . " " . $last_name . ",</h1>
                          <p>Merci de vous être inscrit sur Gamestore. Votre compte a été créé avec succès !</p>
                          <p>Vous pouvez maintenant vous connecter avec votre pseudo et votre mot de passe.</p>
                          <p>À bientôt sur Gamestore !</p>
                          <p>Cordialement,<br>L'équipe Gamestore</p>";

        $mail->send();
    } catch (Exception $e) {
        // En cas d'erreur d'envoi de l'email, vous pouvez gérer l'erreur ici
        error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
    }

    echo json_encode(['success' => true, 'message' => 'Compte créé avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte.']);
}
?>
