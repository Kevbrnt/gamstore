<?php
require_once __DIR__ . '/../models/connect_bdd.php';
require_once __DIR__ . '/../utils/session_management.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
$user = getUserSession();
if (!$user || !isset($user['id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $user['id'];

try {
    // Compter le nombre total d'articles dans le panier de l'utilisateur
    $stmt = $pdo->prepare("SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_items = $result['total_items'] ?? 0;

    // Assurez-vous que le résultat est un entier
    $total_items = (int)$total_items;

    echo json_encode(['count' => $total_items]);
} catch (PDOException $e) {
    // En cas d'erreur, on renvoie 0 pour éviter de révéler des informations sensibles
    error_log('Erreur lors du comptage des articles du panier : ' . $e->getMessage());
    echo json_encode(['count' => 0]);
}
?>