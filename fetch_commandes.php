<?php
session_start();
require_once 'connect_bdd.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_POST['user_id'];

// Récupérer les commandes de l'utilisateur depuis la base de données
$query = "
    SELECT c.id, c.date_commande, c.montant, p.nom_produit, lc.quantite, lc.prix
    FROM gamestoretp.orders c
    INNER JOIN gamestoretp.lignes_commande lc ON c.id = lc.commande_id
    INNER JOIN gamestoretp.order_items p ON lc.produit_id = p.id
    WHERE c.user_id = :user_id
    ORDER BY c.date_commande DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);

// Organiser les commandes par ID
$commandes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $commande_id = $row['id'];
    if (!isset($commandes[$commande_id])) {
        $commandes[$commande_id] = [
            'id' => $commande_id,
            'date_commande' => $row['date_commande'],
            'montant' => $row['montant'],
            'lignes' => []
        ];
    }
    $commandes[$commande_id]['lignes'][] = [
        'nom_produit' => $row['nom_produit'],
        'quantite' => $row['quantite'],
        'prix' => $row['prix']
    ];
}

// Répondre avec les données au format JSON
echo json_encode([
    'success' => true,
    'commandes' => array_values($commandes)  // Convertir le tableau associatif en tableau indexé
]);
?>
