<?php
session_start();

require "connect_bdd.php";

$username = $_POST["username"];
$password = $_POST["password"];

$stmt = $pdo->prepare("SELECT * FROM gamestoretp.users WHERE username = :username ");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if($user && password_verify($password, $user['password'])){
    // Assigner des variables de session
    $_SESSION['name'] = $user['username'];
    $_SESSION['id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    echo json_encode(['success' => true, 'role' => $user['role']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
}
?>