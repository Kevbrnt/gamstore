<?php
require 'connect_bdd.php';

$stmt = $pdo->prepare("SELECT id, name FROM games");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($games);
?>
