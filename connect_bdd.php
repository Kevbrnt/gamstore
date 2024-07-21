<?php
$host = getenv('DATABASE_HOST') ?: '192.168.1.176';
$dbname = getenv('DATABASE_NAME') ?: 'gamestoretp';
$username = getenv('DATABASE_USER') ?: 'KBStudio';
$password = getenv('DATABASE_PASSWORD') ?: '2309';

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
