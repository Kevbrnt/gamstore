<?php
// dashboard.php

// Démarrage de la session
session_start();

// Inclusion des informations de connexion à la base de données
require 'connect_bdd.php'; // Assurez-vous que ce fichier contient les informations de connexion à la base de données

try {
    // Requête pour récupérer les ventes par genre
    $stmt = $pdo->query("
        SELECT games.genre AS genre, COUNT(order_items.id) AS ventes
        FROM order_items 
        JOIN games ON order_items.game_id = games.id
        JOIN orders ON order_items.order_id = orders.id
        WHERE orders.status = 'LIVRE'
        GROUP BY games.genre
        ORDER BY COUNT(order_items.id) DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $salesData = [];

    foreach ($data as $row) {
        $labels[] = $row['genre'];
        $salesData[] = $row['ventes'];
    }

    // Réponse JSON avec les données pour le graphique
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'data' => $salesData
    ]);
} catch (PDOException $e) {
    // En cas d'erreur, renvoyer une réponse JSON avec l'erreur
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
