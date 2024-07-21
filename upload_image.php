<?php
session_start();
require_once 'connect_bdd.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['id'];

// Préparer la réponse JSON par défaut
$response = array('success' => false, 'message' => 'Une erreur s\'est produite.');

// Vérifier si un fichier a été téléchargé
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['profile_image']['tmp_name'];
    $file_name = basename($_FILES['profile_image']['name']);
    $upload_dir = 'assets/profiles/' . $user_id . '/';

    // Créer le répertoire s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Déplacer le fichier téléchargé vers le répertoire spécifique
    $destination = $upload_dir . $file_name;
    if (move_uploaded_file($file_tmp_name, $destination)) {
        // Mettre à jour le chemin de l'image dans la base de données
        $image_url = '/' . $destination; // URL relative
        $query = "UPDATE users SET image_url = :image_url WHERE id = :user_id";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute(['image_url' => $image_url, 'user_id' => $user_id])) {
            $response['success'] = true;
            $response['message'] = 'Image téléchargée avec succès.';
            $response['image_url'] = $image_url; // Retourner le chemin de l'image
        } else {
            $response['message'] = 'Erreur lors de la mise à jour de la base de données.';
        }
    } else {
        $response['message'] = 'Erreur lors de l\'upload du fichier.';
    }
} else {
    $response['message'] = 'Aucun fichier téléchargé ou erreur lors du téléchargement.';
}

// Retourner la réponse JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
