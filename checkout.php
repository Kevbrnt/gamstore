<?php
include 'connect_bdd.php';
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$cart_id = $_POST['cart_id']; // Suppose que le cart_id est passé via POST

try {
    $pdo->beginTransaction();

    // Recalculer le prix total à partir des prix actuels des articles
    $stmt = $pdo->prepare("
        SELECT games.id, games.price, cart_items.quantity
        FROM cart_items
        JOIN games ON cart_items.game_id = games.id
        WHERE cart_items.cart_id = :cart_id AND cart_items.user_id = :user_id
    ");
    $stmt->execute([':cart_id' => $cart_id, ':user_id' => $user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_price = 0;

    foreach ($items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // Créer la commande
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_price, status, created_at)
        VALUES (:user_id, :total_price, 'En attente', NOW())
    ");
    $stmt->execute([':user_id' => $user_id, ':total_price' => $total_price]);

    $order_id = $pdo->lastInsertId();

    // Insérer les articles de la commande
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, game_id, quantity, price)
            VALUES (:order_id, :game_id, :quantity, :price)
        ");
        $stmt->execute([
            ':order_id' => $order_id,
            ':game_id' => $item['id'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price']
        ]);
    }

    // Vider le panier de l'utilisateur
    $stmt = $pdo->prepare("
        DELETE FROM cart_items WHERE cart_id = :cart_id AND user_id = :user_id
    ");
    $stmt->execute([':cart_id' => $cart_id, ':user_id' => $user_id]);

    $pdo->commit();
    echo "Commande validée avec succès.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erreur lors de la validation de la commande : " . $e->getMessage();
}
?>
