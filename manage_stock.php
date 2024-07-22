<?php
require 'connect_bdd.php';

if (
    isset($_POST['game_id']) && !empty($_POST['game_id']) &&
    isset($_POST['stock_quantity']) && !empty($_POST['stock_quantity'])
) {
    $game_id = $_POST['game_id'];
    $stock_quantity = $_POST['stock_quantity'];

    $stmt = $pdo->prepare("
        UPDATE gamestoretp.games 
        SET stock = stock + :stock_quantity 
        WHERE id = :game_id
    ");
    $success = $stmt->execute([
        ':stock_quantity' => $stock_quantity,
        ':game_id' => $game_id
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Stock mis à jour avec succès !']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du stock.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes pour mettre à jour le stock.']);
}
?>
