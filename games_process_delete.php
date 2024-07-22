<?php
require 'connect_bdd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gameId = $_POST['game_id'] ?? null;

    if ($gameId === null) {
        echo json_encode(['success' => false, 'message' => 'ID du jeu non fourni.']);
        exit;
    }

    try {
        // Supprimer les enregistrements associés dans la table `sales`
        $deleteSalesQuery = $pdo->prepare("DELETE FROM gamestoretp.sales WHERE game_id = :game_id");
        $deleteSalesQuery->execute(['game_id' => $gameId]);

        // Récupérer les informations du jeu à supprimer
        $query = $pdo->prepare("SELECT image_url FROM gamestoretp.games WHERE id = :id");
        $query->execute(['id' => $gameId]);
        $game = $query->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            echo json_encode(['success' => false, 'message' => 'Jeu non trouvé.']);
            exit;
        }

        // Supprimer l'image du jeu
        if (!empty($game['image_url']) && file_exists($game['image_url'])) {
            unlink($game['image_url']);
        }

        // Supprimer le jeu de la base de données
        $deleteQuery = $pdo->prepare("DELETE FROM gamestoretp.games WHERE id = :id");
        $deleteQuery->execute(['id' => $gameId]);

        echo json_encode(['success' => true, 'message' => 'Jeu supprimé avec succès.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête non autorisée.']);
}
?>
