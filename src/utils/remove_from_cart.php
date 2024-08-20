<?php
require_once __DIR__ . '/../models/connect_bdd.php';
require_once __DIR__ . '/../utils/session_management.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
$user = getUserSession();
if (!$user || !isset($user['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté']);
    exit;
}

$user_id = $user['id'];

if (!isset($_POST['item_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de l\'article du panier non fourni']);
    exit;
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
if ($item_id === false || $item_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'ID de l\'article invalide']);
    exit;
}

try {
    // Commencer une transaction
    $pdo->beginTransaction();

    // Vérifier si l'article existe dans le panier de l'utilisateur
    $check_stmt = $pdo->prepare("SELECT id FROM cart WHERE id = :item_id AND user_id = :user_id");
    $check_stmt->execute([':item_id' => $item_id, ':user_id' => $user_id]);

    if ($check_stmt->rowCount() === 0) {
        throw new Exception('Article non trouvé dans le panier');
    }

    // Supprimer l'article du panier
    $delete_stmt = $pdo->prepare("DELETE FROM cart WHERE id = :item_id AND user_id = :user_id");
    $delete_stmt->execute([':item_id' => $item_id, ':user_id' => $user_id]);

    // Recalculer le total du panier
    $total_stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(
                CASE 
                    WHEN games.promotion_price > 0 THEN games.promotion_price * cart.quantity 
                    ELSE games.price * cart.quantity 
                END
            ), 0) AS total_price
        FROM cart
        INNER JOIN games ON cart.game_id = games.id
        WHERE cart.user_id = :user_id
    ");
    $total_stmt->execute([':user_id' => $user_id]);
    $total_price = $total_stmt->fetchColumn();

    // Mettre à jour le total du panier dans la session
    $_SESSION['total_price'] = $total_price;

    // Valider la transaction
    $pdo->commit();

    echo json_encode(['status' => 'success', 'total_price' => number_format($total_price, 2)]);
} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();
    error_log('Erreur lors de la suppression de l\'article du panier : ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Une erreur est survenue lors de la suppression de l\'article: ' . $e->getMessage()]);
}
?>