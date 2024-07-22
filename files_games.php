<?php
include 'connect_bdd.php';

$price = isset($_GET['price']) ? intval($_GET['price']) : 0;
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';

$query = "SELECT id, name, price, image_url FROM gamestoretp.games WHERE price <= :price";
$params = [':price' => $price];

if ($genre) {
    $query .= " AND genre = :genre";
    $params[':genre'] = $genre;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($games);
?>
