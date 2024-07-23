<?php
session_start();
include 'connect_bdd.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté']);
    exit;
}

$user_id = $_SESSION['id'];

if (isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    // Supprimer l'article du panier
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :item_id AND user_id = :user_id");
    $stmt->execute([':item_id' => $item_id, ':user_id' => $user_id]);

    // Recalculer le total du panier
    $stmt = $pdo->prepare("
        SELECT 
            SUM(
                CASE 
                    WHEN games.promotion_price > 0 THEN games.promotion_price * cart.quantity 
                    ELSE games.price * cart.quantity 
                END
            ) AS total_price
        FROM cart
        INNER JOIN games ON cart.game_id = games.id
        WHERE cart.user_id = :user_id
    ");
    $stmt->execute([':user_id' => $user_id]);
    $total_price = $stmt->fetchColumn();

    if ($total_price === false) {
        $total_price = 0.00;
    }

    $_SESSION['total_price'] = $total_price; // Mettre à jour le total du panier dans la session

    echo json_encode(['status' => 'success', 'total_price' => number_format($total_price, 2)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de l\'article du panier non fourni']);
}
?>

