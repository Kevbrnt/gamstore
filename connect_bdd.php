<?php
$host = getenv('192.168.1.176') ?: 'valeur_par_defaut_host';
$dbname = getenv('gamestoretp') ?: 'valeur_par_defaut_dbname';
$username = getenv('KBStudio') ?: 'valeur_par_defaut_user';
$password = getenv('2309') ?: 'valeur_par_defaut_password';

echo "Tentative de connexion à la base de données...\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n";

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    echo "Connexion réussie!";
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
