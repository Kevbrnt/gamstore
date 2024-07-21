<?php
$host = getenv('DATABASE_HOST');
$dbname = getenv('DATABASE_NAME');
$user = getenv('DATABASE_USER');
$password = getenv('DATABASE_PASSWORD');

$dsn = "pgsql:host=$host;dbname=$dbname;";
$pdo = new PDO($dsn, $user, $password);
?>
