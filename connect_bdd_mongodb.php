<?php
require 'vendor/autoload.php'; // Assurez-vous que Composer est correctement configuré

$uri = 'mongodb://vercel-admin-user:2309@atlas-sql-6698257b14587d4d00ef8c7f-1inwc.a.query.mongodb.net/Gamestore?ssl=true&authSource=admin';

try {
    // Créez un client MongoDB
    $client = new MongoDB\Client($uri);

    // Sélectionnez la base de données et la collection
    $collection = $client->Gamestore->sales;

    // Définissez les étapes de l'agrégation
    $pipeline = [
        [
            '$group' => [
                '_id' => [
                    'name' => '$name',
                    'date' => [
                        '$dateToString' => [
                            'format' => '%Y-%m-%d',
                            'date' => '$date'
                        ]
                    ]
                ],
                'total_sales' => ['$sum' => '$sales'],
                'total_revenue' => ['$sum' => '$revenue']
            ]
        ],
        ['$sort' => ['_id.date' => 1]]
    ];

    // Exécutez l'agrégation
    $cursor = $collection->aggregate($pipeline);

    // Affichez les résultats
    foreach ($cursor as $document) {
        echo json_encode($document) . "\n";
    }

} catch (Exception $e) {
    echo "Erreur : ", $e->getMessage(), "\n";
}
?>
