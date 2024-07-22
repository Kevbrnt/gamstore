<?php
require 'vendor/autoload.php'; // Composer autoload file

$mongoUri = getenv('MONGODB_URI');

try {
    $client = new MongoDB\Client($mongoUri);
    $collection = $client->gamestore->sales;

    // Exemple d'agrégation simple
    $result = $collection->aggregate([
        ['$match' => []],  // Ceci sélectionne tous les documents
        ['$limit' => 10]   // Limite le résultat à 10 documents
    ]);

    // Vous pouvez maintenant itérer sur $result
    foreach ($result as $document) {
        // Traitement de chaque document
        print_r($document);
    }

} catch (MongoDB\Driver\Exception\Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}