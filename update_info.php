<?php
session_start();

require_once 'connect_bdd.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

$user_id = $_SESSION['id'];

// Récupérer les données du formulaire
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
$first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
$last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
$address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

// Vérifier que l'email est valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Email invalide.']);
    exit();
}

// Préparer la requête de mise à jour
$query = "UPDATE users SET email = :email, username = :username, first_name = :first_name, last_name = :last_name, address = :address WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$result = $stmt->execute([
    'email' => $email,
    'username' => $username,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'address' => $address,
    'user_id' => $user_id
]);

header('Content-Type: application/json');

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour des informations.']);
}
?>
