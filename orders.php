<?php
session_start();
include 'connect_bdd.php';

$user_id = $_SESSION['id'];
$order_id = $_GET['id'] ?? null;

if ($order_id === null) {
    echo 'Aucune commande spécifiée.';
    exit;
}

$sql = "SELECT orders.id, orders.total_price, orders.status, orders.created_at, order_items.quantity, order_items.price, games.name
        FROM gamestoretp.orders
        JOIN gamestoretp.order_items ON orders.id = order_items.order_id
        JOIN gamestoretp.games ON order_items.game_id = games.id
        WHERE orders.user_id = :user_id AND orders.id = :order_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id, 'order_id' => $order_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($items) {
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Nom du jeu</th><th>Quantité</th><th>Prix Unitaire</th><th>Prix Total</th></tr></thead>";
    echo "<tbody>";
    foreach ($items as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['quantity']) . "</td>";
        echo "<td>" . number_format($item['price'], 2) . "€</td>";
        echo "<td>" . number_format($item['quantity'] * $item['price'], 2) . "€</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "<p><strong>Prix total de la commande:</strong> " . number_format($items[0]['total_price'], 2) . "€</p>";
    echo "<p><strong>Statut:</strong> " . htmlspecialchars($items[0]['status']) . "</p>";
    echo "<p><strong>Date de création:</strong> " . date('d/m/Y H:i', strtotime($items[0]['created_at'])) . "</p>";
} else {
    echo "<p>Aucun détail trouvé pour cette commande.</p>";
}
?>
