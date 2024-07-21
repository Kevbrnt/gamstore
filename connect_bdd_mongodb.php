<?php
require 'vendor/autoload.php'; // Composer autoload file

use MongoDB\Client as MongoClient;

try {
// Connexion à MongoDB
$client = new MongoClient("mongodb://192.168.1.176:27017");
$database = $client->selectDatabase('gamestore');
$collection = $database->selectCollection('sales');
} catch (Exception $e) {
echo 'Erreur de connexion à MongoDB : ',  $e->getMessage(), "\n";
exit();
}
?>