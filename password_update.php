<?php
session_start();
include 'connect_bdd.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $newPassword = $_POST['password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Vérifie si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Met à jour le mot de passe pour cet utilisateur
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->execute([':password' => $hashedPassword, ':email' => $email]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email non trouvé.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête non valide.']);
}
?>
