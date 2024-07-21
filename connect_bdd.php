$host = getenv('192.168.1.176');
$dbname = getenv('gamestoretp');
$username = getenv('KBStudio');
$password = getenv('2309');

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
