<?php

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=192.168.1.176:8080;dbname=gamestoretp;charset=utf8', 'KBStudio', '2309');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // S'assurer que les caractères sont encodés en UTF-8
    $pdo->exec('SET NAMES utf8');
} catch (PDOException $e) {
    // Message d'erreur en cas d'échec de la connexion
    echo 'Nous avons pas pu vous connecter : ' . $e->getMessage();
    exit();  // Terminer l'exécution du script si la connexion échoue
}
?>
