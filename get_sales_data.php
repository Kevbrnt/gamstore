<?php
require 'connect_bdd.php';
require 'vendor/autoload.php'; // Autoload file for Composer

use MongoDB\Client as MongoClient;

$client = new MongoClient("mongodb://localhost:27017");
$collection = $client->store->sales;

$cursor = $collection->aggregate([
    [
        '$group' => [
            '_id' => [
                'date' => '$date',
                'article' => '$name'
            ],
            'sales' => ['$sum' => '$sales'],
            'revenue' => ['$sum' => '$revenue']
        ]
    ],
    [
        '$sort' => ['_id.date' => 1]
    ]
]);

$sales_data = [];
foreach ($cursor as $document) {
    $sales_data[] = [
        'date' => $document['_id']['date'],
        'article' => $document['_id']['article'],
        'sales' => $document['sales'],
        'revenue' => $document['revenue']
    ];
}

echo json_encode($sales_data);
?>
