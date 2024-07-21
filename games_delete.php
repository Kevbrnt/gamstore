<?php
require 'connect_bdd.php';

try {
    // Sélectionnez les jeux disponibles
    $query = $pdo->prepare("SELECT id, name FROM games");
    $query->execute();
    $games = $query->fetchAll(PDO::FETCH_ASSOC);

    // Retournez les jeux au format JSON
    echo json_encode($games);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
