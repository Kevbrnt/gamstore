<?php
include 'connect_bdd.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['id'];

// Préparez la requête pour récupérer les informations des jeux dans le panier
$stmt = $pdo->prepare("SELECT cart.id AS cart_id, games.id AS game_id, games.name, games.price, games.promotion_price, games.image_url
                       FROM cart
                       JOIN games ON cart.game_id = games.id
                       WHERE cart.user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
?>
