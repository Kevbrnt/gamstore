<?php
require __DIR__ . '/../../build/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

try {
    // Ajout de sslmode=require dans la chaîne DSN
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
 /*    echo "Connexion réussie à la base de données PostgreSQL.";

   // Test simple pour vérifier la connexion
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "\nVersion de PostgreSQL : " . $version;*/
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage() . "\n";
    echo "Code d'erreur : " . $e->getCode() . "\n";
    echo "Trace de la pile : \n" . $e->getTraceAsString();
}

function cleanUrl($url) {
    $url = strtok($url, '?'); // Retire les paramètres GET
    $url = rtrim($url, '/'); // Retire le slash final s'il existe
    return $url;
}

$request = cleanUrl($_SERVER['REQUEST_URI']);

switch ($request) {
    case '':
    case '/':
    case '/Accueil.php':
        require __DIR__ . '/../controllers/Accueil.php';
        break;
    case '/Boutique':
        require __DIR__ . '/../controllers/games.php';
        break;
    case '/Inscription':
        require __DIR__ . '/../controllers/register.php';
        break;
    case '/Connexion':
        require __DIR__ . '/../controllers/login.php';
        break;
    case '/Espace Administrateur':
        require __DIR__ . '/../controllers/espace_admin.php';
        break;
    case '/Espace Utilisateur':
        require __DIR__ . '/../controllers/espace.php';
        break;
    case '/Espace Employé':
        require __DIR__ . '/../controllers/espace_employés.php';
        break;
    case '/Panier':
        require __DIR__ . '/../controllers/cart.php';
        break;
}
?>