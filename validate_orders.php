<?php
session_start();
header('Content-Type: application/json');

// Vérifiez que l'utilisateur est un employé
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employés') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit;
}

// Vérifiez que la requête est de type POST et que 'order_id' est défini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];

    // Connectez-vous à la base de données
    require 'connect_bdd.php';

    // Mettez à jour le statut de la commande
    $stmt = $pdo->prepare("UPDATE orders SET status = 'LIVRE' WHERE id = ?");
    if ($stmt->execute([$orderId])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation de la commande.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>
