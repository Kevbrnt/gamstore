<?php
require __DIR__ . '/../../build/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

use MongoDB\Client;

$mongoUri = $_ENV["URL_MONGO"];
$database = $_ENV["DATABASE_MONGO"];
$collect= $_ENV["COLLECT_MONGO"];

try {
    $client = new Client($mongoUri);
    $database = $client->selectDatabase($database);
    $collection = $database->selectCollection($collect);

    // Vérifiez la connexion
    $client->listDatabases();

    //echo "Connexion à MongoDB établie avec succès.";
} catch (Exception $e) {
    die("Erreur de connexion à MongoDB : " . $e->getMessage());
}