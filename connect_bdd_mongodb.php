<?php
require 'vendor/autoload.php'; // Composer autoload file


$mongoUri = getenv('MONGODB_URI');
$client = new MongoDB\Client($mongoUri);
$collection = $client->gamestore->sales;

// Votre code existant pour l'agrégation...
$result = $collection->aggregate([...]);