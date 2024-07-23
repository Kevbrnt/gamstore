<?php
include 'connect_bdd.php';

$stmt = $pdo->prepare("SELECT id, name, price, promotion_price, image_url, add_at FROM games ORDER BY add_at desc LIMIT 6");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($games);
?>
