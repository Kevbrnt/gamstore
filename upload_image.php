<?php
session_start();
require_once 'connect_bdd.php';
require_once 'config.php'; // Inclure le fichier de configuration

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

$user_id = $_SESSION['id'];
$response = ['success' => false, 'message' => 'Une erreur s\'est produite.'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['profile_image']['tmp_name'];
    $file_name = basename($_FILES['profile_image']['name']);
    $upload_dir = 'assets/profiles/' . $user_id . '/';

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $destination = $upload_dir . $file_name;
    if (move_uploaded_file($file_tmp_name, $destination)) {
        $image_url = '/' . $destination;
        $query = "UPDATE users SET image_url = :image_url WHERE id = :user_id";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute(['image_url' => $image_url, 'user_id' => $user_id])) {
            $response['success'] = true;
            $response['message'] = 'Image téléchargée avec succès.';
            $response['image_url'] = $file_name; // Retourner seulement le nom du fichier

            // Ajouter l'URL de l'API
            $response['api_image_url'] = API_URL . '?key=' . API_KEY . '&image=' . urlencode($image_url);
        } else {
            $response['message'] = 'Erreur lors de la mise à jour de la base de données.';
        }
    } else {
        $response['message'] = 'Erreur lors de l\'upload du fichier.';
    }
} else {
    $response['message'] = 'Aucun fichier téléchargé ou erreur lors du téléchargement.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>