<?php
include 'connect_bdd.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id");
$stmt->execute([':id' => $id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($game);
?>