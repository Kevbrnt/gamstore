<?php
include 'connect_bdd.php';
session_start();

// Définir l'en-tête de la réponse en JSON
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour ajouter un jeu au panier.']);
    exit;
}

$user_id = $_SESSION['id'];
$game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;

// Récupérer les informations du jeu à ajouter au panier
$stmt = $pdo->prepare("SELECT id, name, price, promotion_price FROM gamestoretp.games WHERE id = :game_id");
$stmt->execute([':game_id' => $game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    echo json_encode(['success' => false, 'message' => 'Jeu non trouvé dans la base de données.']);
    exit;
}

// Calculer le prix final à ajouter au panier
$priceToAdd = !empty($game['promotion_price']) ? $game['promotion_price'] : $game['price'];

// Vérifier si l'article est déjà dans le panier
$stmt = $pdo->prepare("SELECT id, quantity FROM gamestoretp.cart WHERE user_id = :user_id AND game_id = :game_id");
$stmt->execute([':user_id' => $user_id, ':game_id' => $game_id]);
$cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cart_item) {
    // Si l'article est déjà dans le panier, incrémenter la quantité
    $stmt = $pdo->prepare("UPDATE gamestoretp.cart SET quantity = quantity + 1 WHERE id = :id");
    $stmt->execute([':id' => $cart_item['id']]);
} else {
    // Si l'article n'est pas dans le panier, l'ajouter avec une quantité de 1
    $stmt = $pdo->prepare("INSERT INTO gamestoretp.cart (user_id, game_id, price, quantity) VALUES (:user_id, :game_id, :price, 1)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':game_id' => $game_id,
        ':price' => $priceToAdd
    ]);
}

echo json_encode(['success' => true, 'message' => 'Jeu ajouté au panier avec succès.']);
exit;
?>
