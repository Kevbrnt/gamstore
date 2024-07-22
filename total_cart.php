<?php
require "connect_bdd.php";

function appliquerPromotion($codePromotion, $totalPanier, $user_id) {
    global $pdo;

    // Vérifier si le code promo est valide et non encore utilisé par cet utilisateur
    $stmt = $pdo->prepare("
        SELECT p.id, p.pourcentage, p.date_debut, p.date_fin
        FROM gamestoretp.promotions p
        LEFT JOIN gamestoretp.used_promotions up ON p.id = up.promotion_id AND up.user_id = :user_id
        WHERE p.code = :code 
        AND CURDATE() BETWEEN p.date_debut AND p.date_fin 
        AND up.id IS NULL
    ");
    $stmt->execute([':user_id' => $user_id, ':code' => $codePromotion]);

    $promotion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promotion) {
        $pourcentage = $promotion['pourcentage'];
        $reduction = ($pourcentage / 100) * $totalPanier;
        $totalAvecReduction = $totalPanier - $reduction;

        // Enregistrez l'utilisation du code promo
        $stmt = $pdo->prepare("
            INSERT INTO gamestoretp.used_promotions (user_id, promotion_id) 
            VALUES (:user_id, :promotion_id)
        ");
        $stmt->execute([':user_id' => $user_id, ':promotion_id' => $promotion['id']]);

        return [
            'estValide' => true,
            'reduction' => $reduction,
            'totalAvecReduction' => $totalAvecReduction,
            'pourcentage' => $pourcentage
        ];
    } else {
        return [
            'estValide' => false,
            'message' => 'Le code de promotion est invalide, expiré ou déjà utilisé.'
        ];
    }
}

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $code_promotion = isset($_POST['code_promotion']) ? trim($_POST['code_promotion']) : '';
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
    $total_panier = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : 0.0; // Récupérer le total du panier depuis la session

    if ($user_id === null) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Utilisateur non connecté.'
        ]);
        exit;
    }

    if ($code_promotion) {
        $resultat = appliquerPromotion($code_promotion, $total_panier, $user_id);

        if ($resultat['estValide']) {
            $_SESSION['total_price'] = $resultat['totalAvecReduction']; // Mettre à jour le total du panier dans la session
            $_SESSION['promotion_code'] = $code_promotion; // Sauvegarder le code promo en session

            echo json_encode([
                'status' => 'success',
                'message' => "Promotion appliquée avec succès. Vous avez économisé {$resultat['reduction']} € !",
                'total_price' => number_format($resultat['totalAvecReduction'], 2)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => $resultat['message']
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Le code de promotion ne peut pas être vide.'
        ]);
    }
}
