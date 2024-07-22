<?php
require 'connect_bdd.php';

if (
    isset($_POST['game_name']) && !empty($_POST['game_name']) &&
    isset($_POST['genre']) && !empty($_POST['genre']) &&
    isset($_POST['price']) && !empty($_POST['price']) &&
    isset($_POST['stock']) && !empty($_POST['stock']) &&
    isset($_POST['pegi']) && !empty($_POST['pegi']) &&
    isset($_POST['platform']) && !empty($_POST['platform']) &&
    isset($_POST['description']) && !empty($_POST['description'])
) {
    $game_name = $_POST['game_name'];
    $genre = $_POST['genre'];
    $price = $_POST['price'];
    $promotion_price = $_POST['promotion_price']; // Peut être vide
    $stock = $_POST['stock'];
    $pegi = $_POST['pegi'];
    $platform = $_POST['platform'];
    $description = $_POST['description'];

    // Gestion de l'upload de l'image
    $image_url = '';
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $fileTmpPath = $_FILES['image_url']['tmp_name'];
        $fileName = $_FILES['image_url']['name'];
        $fileSize = $_FILES['image_url']['size'];
        $fileType = $_FILES['image_url']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './asset/Jeux/';
            $dest_path = $uploadFileDir . $newFileName;
            move_uploaded_file($fileTmpPath, $dest_path);
            $image_url = 'asset/Jeux/' . $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Extension de fichier non autorisée.']);
            exit;
        }
    } else {
        $image_url = 'asset/Jeux/default.png'; // Image par défaut si aucune image n'est fournie
    }

    // Préparation de la requête SQL
    $stmt = $pdo->prepare("
        INSERT INTO gamestoretp.games (name, pegi, genre, price, promotion_price, stock, image_url, platform, add_at, Description) 
        VALUES (:name, :pegi, :genre, :price, :promotion_price, :stock, :image_url, :platform, NOW(), :description)
    ");

    // Exécution de la requête avec vérification de promotion_price
    $success = $stmt->execute([
        ':name' => $game_name,
        ':pegi' => $pegi,
        ':genre' => $genre,
        ':price' => $price,
        ':promotion_price' => $promotion_price !== '' ? $promotion_price : NULL,
        ':stock' => $stock,
        ':image_url' => $image_url,
        ':platform' => $platform,
        ':description' => $description
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Jeu vidéo créé avec succès !']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du jeu vidéo.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes pour créer le jeu vidéo.']);
}
?>
