<?php
include 'connect_bdd.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['id'];

// Comptez le nombre d'articles dans le panier de l'utilisateur
$stmt = $pdo->prepare("SELECT COUNT(*) AS item_count FROM cart WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['count' => $result['item_count']]);
?>
