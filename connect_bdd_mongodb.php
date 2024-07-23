<?php
require 'vendor/autoload.php';

use MongoDB\Client;

$mongoUri = "mongodb+srv://bronetkevin:2309@cluster0.rjk4mdl.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";

try {
    $client = new Client($mongoUri);
    $database = $client->selectDatabase('Gamestore');
    $collection = $database->selectCollection('sales');

    // Vérifiez la connexion
    $client->listDatabases();

    //echo "Connexion à MongoDB établie avec succès.";
} catch (Exception $e) {
    die("Erreur de connexion à MongoDB : " . $e->getMessage());
}