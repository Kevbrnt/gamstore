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
?>