<?php
// Connexion à MongoDB
require 'vendor/autoload.php';
use MongoDB\Client as MongoClient;

// Connexion à la base de données MongoDB
try {
    $client = new MongoClient("mongodb://localhost:27017");
    $collection = $client->Gamestore->sales;
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur de connexion à MongoDB : ' . $e->getMessage());
}

// Récupérer les données des ventes
$result = $collection->find([], ['projection' => ['date' => 1, 'sales' => 1]]);
$data = iterator_to_array($result);

// Retourner les données JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
