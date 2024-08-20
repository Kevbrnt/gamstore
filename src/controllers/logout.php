<?php

error_log("Début de logout.php");

// Désactiver l'affichage des erreurs
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Démarrer la capture de la sortie
ob_start();

require_once __DIR__ . '/../utils/session_management.php';
require_once __DIR__ . '/../utils/csrf_token.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

error_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);

// Vérification du token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
    exit;
}
error_log("Token CSRF reçu: " . ($_POST['csrf_token'] ?? 'non défini'));

error_log("Tentative de déconnexion");

// Déconnexion de l'utilisateur
endUserSession();

error_log("Déconnexion effectuée");

// Régénération de l'ID de session pour prévenir la fixation de session
session_regenerate_id(true);

// Nettoyer toute sortie précédente
ob_clean();

// Définir l'en-tête Content-Type
header('Content-Type: application/json');

error_log("Envoi de la réponse: " . json_encode(['success' => true, 'message' => 'Déconnexion réussie.']));

// Envoi de la réponse
echo json_encode(['success' => true, 'message' => 'Déconnexion réussie.']);

// Terminer le script
exit;
?>
