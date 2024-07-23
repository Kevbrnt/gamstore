<?php
// Récupérer l'ID de la commande depuis la requête GET
$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID de commande manquant');
}

// Inclure le gestionnaire de MongoDB
require 'vendor/autoload.php';
use MongoDB\Client as MongoClient;

// Connexion à la base de données MongoDB
try {
    $client = new MongoClient("mongodb://localhost:27017");
    $collection = $client->Gamestore->sales; // Changer "gamestore" au nom de votre base de données
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur de connexion à MongoDB : ' . $e->getMessage());
}

// Récupérer les détails des ventes pour cette commande spécifique
try {
    $result = $collection->find(['order_id' => $order_id], [
        'projection' => ['date' => 1, 'name' => 1, 'sales' => 1, 'revenue' => 1]
    ]);
    $data = iterator_to_array($result);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur lors de la récupération des données : ' . $e->getMessage());
}

// Retourner les données JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
