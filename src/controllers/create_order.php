<?php
ob_start();

// Configuration des erreurs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../build/vendor/autoload.php';
require_once __DIR__ . '/../utils/session_management.php';
require_once __DIR__ . '/../models/connect_bdd.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier que l'utilisateur est connecté
    $user = getUserSession();
    if (!$user || !isset($user['id'])) {
        throw new Exception('Vous devez être connecté pour passer une commande.');
    }

    $user_id = $user['id'];

    $total_price = filter_input(INPUT_POST, 'total_price', FILTER_VALIDATE_FLOAT);
    $retail_id = filter_input(INPUT_POST, 'retail_id', FILTER_VALIDATE_INT);
    $date_retrait = filter_input(INPUT_POST, 'date_retrait', FILTER_SANITIZE_STRING);

    if ($retail_id === false || $date_retrait === false || $total_price === false) {
        throw new Exception('Données de commande invalides.');
    }

    // Valider la date de retrait
    $date_today = new DateTime();
    $date_retrait = new DateTime($date_retrait);
    $day_of_week = $date_retrait->format('N');

    if ($date_retrait <= $date_today) {
        throw new Exception('La date de retrait doit être dans le futur.');
    }

    $max_date_retrait = (clone $date_today)->modify('+7 days');
    if ($date_retrait > $max_date_retrait) {
        throw new Exception('La date de retrait doit être dans les 7 jours à partir d\'aujourd\'hui.');
    }

    if ($day_of_week == 1 || $day_of_week == 7) {
        throw new Exception('La date de retrait ne peut pas être un lundi ou un dimanche.');
    }

    // Commencer une transaction
    $pdo->beginTransaction();

    // Insérer la commande dans la table orders
    $sql = "INSERT INTO orders (user_id, total_price, status, created_at, retail_id, date_retrait) 
            VALUES (:user_id, :total_price, 'VALIDE', NOW(), :retail_id, :date_retrait)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':total_price' => $total_price,
        ':retail_id' => $retail_id,
        ':date_retrait' => $date_retrait->format('Y-m-d')
    ]);

    $order_id = $pdo->lastInsertId();

    // Récupérer les articles du panier
    $sql = "SELECT cart.game_id, cart.quantity, 
                   CASE 
                       WHEN games.promotion_price > 0 THEN games.promotion_price 
                       ELSE games.price 
                   END AS price,
                   games.name,
                   games.image_url
            FROM cart
            JOIN games ON cart.game_id = games.id
            WHERE cart.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        throw new Exception('Le panier est vide.');
    }

    // Insérer chaque article du panier dans la table order_items
    $sql = "INSERT INTO order_items (order_id, game_id, quantity, price) 
            VALUES (:order_id, :game_id, :quantity, :price)";
    $stmt = $pdo->prepare($sql);

    foreach ($cart_items as $item) {
        $stmt->execute([
            ':order_id' => $order_id,
            ':game_id' => $item['game_id'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price']
        ]);

        // Déduire la quantité achetée du stock
        $sql = "UPDATE games SET stock = stock - :quantity WHERE id = :game_id AND stock >= :quantity";
        $stmt_update = $pdo->prepare($sql);
        $result = $stmt_update->execute([
            ':quantity' => $item['quantity'],
            ':game_id' => $item['game_id']
        ]);

        if ($stmt_update->rowCount() === 0) {
            throw new Exception('Stock insuffisant pour le jeu ' . $item['name']);
        }
    }

    // Supprimer les articles du panier de l'utilisateur
    $sql = "DELETE FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    // Valider la transaction
    $pdo->commit();

    // Envoyer l'e-mail
    $mail = new PHPMailer(true);

    //Configuration Sécurisé
    $host = $_ENV['HOST_MAIL'];
    $nameMail = $_ENV["USERNAME_MAIL"];
    $password = $_ENV["PASSWORD_MAIL"];
    $port = $_ENV["PORT_MAIL"];

        // Configurer le serveur SMTP
        $mail->isSMTP();
        $mail->Host = $host ;  // Remplacez par le serveur SMTP de votre fournisseur d'email
        $mail->SMTPAuth = true;
        $mail->Username = $nameMail; // Remplacez par votre adresse email
        $mail->Password = $password; // Remplacez par le mot de passe de votre adresse email
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;

    $mail->setFrom($_ENV["USERNAME_MAIL"], 'Gamestore');
    $mail->addAddress($user['email'], $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de votre commande';

    $items_html = '';
    foreach ($cart_items as $item) {
        $items_html .= "
            <div style='border: 3px solid #ddd; padding: 10px; margin-bottom: 10px;'>
                <p><strong>" . htmlspecialchars($item['name']) . "</strong></p>
                <p>Quantité: " . htmlspecialchars($item['quantity']) . "</p>
                <p>Prix unitaire: " . htmlspecialchars($item['price']) . " €</p>
            </div>
        ";
    }

    $mail->Body = "
        <h1>Merci pour votre commande, " . htmlspecialchars($user['username']) . " !</h1>
        <p>Votre commande numéro <strong>" . htmlspecialchars($order_id) . "</strong> a été reçue avec succès.</p>
        <p><strong>Total :</strong> " . htmlspecialchars($total_price) . " €</p>
        <p><strong>Date de retrait :</strong> " . htmlspecialchars($date_retrait->format('d/m/Y')) . "</p>
        <h2>Détails de la commande</h2>
        " . $items_html . "
        <p>Nous vous enverrons un autre e-mail lorsque votre commande sera prête pour le retrait.</p>
        <p>Merci de nous faire confiance !</p>
        <p>L'équipe GameStore</p>
    ";

    $mail->send();
    $response['success'] = true;
    $response['message'] = 'Commande créée et e-mail envoyé avec succès.';
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur : " . $e->getMessage());
    $response['message'] = 'Une erreur est survenue : ' . $e->getMessage();
}

// Nettoyage du tampon de sortie
ob_end_clean();

// Envoi de la réponse JSON
echo json_encode($response);
exit;
?>