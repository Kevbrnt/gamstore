<?php
session_start();
include 'connect_bdd.php';

// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Inclut l'autoloader de Composer

// Vérifiez que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Vous devez être connecté pour passer une commande.']);
    exit;
}

$user_id = $_SESSION['id'];
$total_price = $_POST['total_price'] ?? 0;
$retail_id = $_POST['retail_id'] ?? null;
$date_retrait = $_POST['date_retrait'] ?? null;

if ($retail_id === null || $date_retrait === null) {
    echo json_encode(['error' => 'Veuillez sélectionner un magasin de retrait et une date de retrait.']);
    exit;
}

// Valider que la date de retrait est dans un futur proche et ne peut pas être un lundi ou un dimanche
$date_today = new DateTime();
$date_retrait = new DateTime($date_retrait);
$day_of_week = $date_retrait->format('N');

// Vérifier que la date de retrait est dans le futur
if ($date_retrait <= $date_today) {
    echo json_encode(['error' => 'La date de retrait doit être dans le futur.']);
    exit;
}

// Vérifier que la date de retrait est dans les 7 jours à partir d'aujourd'hui
$max_date_retrait = (clone $date_today)->modify('+7 days');
if ($date_retrait > $max_date_retrait) {
    echo json_encode(['error' => 'La date de retrait doit être dans les 7 jours à partir d\'aujourd\'hui.']);
    exit;
}

// Vérifier que la date de retrait n'est pas un lundi ou un dimanche
if ($day_of_week == 1 || $day_of_week == 7) {
    echo json_encode(['error' => 'La date de retrait ne peut pas être un lundi ou un dimanche.']);
    exit;
}

// Commencer une transaction
$pdo->beginTransaction();

try {
    // Insérer la commande dans la table orders
    $sql = "INSERT INTO orders (user_id, total_price, status, created_at, retail_id, date_retrait) 
            VALUES (:user_id, :total_price, 'VALIDE', NOW(), :retail_id, :date_retrait)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':total_price' => $total_price,
        ':retail_id' => $retail_id,
        ':date_retrait' => $date_retrait->format('Y-m-d')  // Format correct pour MySQL
    ]);

    // Obtenir l'ID de la commande nouvellement créée
    $order_id = $pdo->lastInsertId();

    // Récupérer les articles du panier pour cet utilisateur avec les informations supplémentaires
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
        $sql = "UPDATE games SET stock = stock - :quantity WHERE id = :game_id";
        $stmt_update = $pdo->prepare($sql);
        $stmt_update->execute([
            ':quantity' => $item['quantity'],
            ':game_id' => $item['game_id']
        ]);
    }

    // Supprimer les articles du panier de l'utilisateur
    $sql = "DELETE FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    // Valider la transaction
    $pdo->commit();

    // Récupérer l'adresse e-mail et le username de l'utilisateur
    $user_query = $pdo->prepare("SELECT email, username FROM users WHERE id = :user_id");
    $user_query->execute([':user_id' => $user_id]);
    $user = $user_query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Utilisateur non trouvé.');
    }

    $user_email = $user['email']; // L'email de l'utilisateur connecté
    $user_name = $user['username']; // Le username de l'utilisateur

    // Construire la liste des articles commandés pour l'email
    $items_html = '';
    foreach ($cart_items as $item) {
        $items_html .= "
            <div style='border: 3px solid #ddd; padding: 10px; margin-bottom: 10px;'>
                <p><strong>{$item['name']}</strong></p>
                <p>Quantité: {$item['quantity']}</p>
                <p>Prix unitaire: {$item['price']} €</p>
            </div>
        ";
    }

    // Créez une instance de PHPMailer
    $mail = new PHPMailer(true);

    // Paramètres du serveur SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Remplacez par l'adresse de votre serveur SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'praskavisuelle@gmail.com'; // Votre adresse e-mail
    $mail->Password   = 'jezg kshu phxl rmnw'; // Votre mot de passe
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Paramètres de l'e-mail
    $mail->setFrom('noreply@gamestore.com', 'Gamestore');
    $mail->addAddress($user_email, $user_name); // Ajout de l'adresse e-mail de l'utilisateur

    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de votre commande';

    // Corps du message
    $mail->Body = "
        <h1>Merci pour votre commande, {$user_name} !</h1>
        <p>Votre commande numéro <strong>{$order_id}</strong> a été reçue avec succès.</p>
        <p><strong>Total :</strong> {$total_price} €</p>
        <p><strong>Date de retrait :</strong> {$date_retrait->format('d/m/Y')}</p>
        <h2>Détails de la commande</h2>
        {$items_html}
        <p>Nous vous enverrons un autre e-mail lorsque votre commande sera prête pour le retrait.</p>
        <p>Merci de nous faire confiance !</p>
        <p>L'équipe GameStore</p>
    ";

    $mail->send();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Message d'erreur : " . $e->getMessage());
    echo json_encode(['error' => 'Une erreur est survenue lors de la création de la commande: ' . $e->getMessage()]);
}
?>
