<?php
session_start();

require __DIR__ . '/../../build/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

include '../../src/models/connect_bdd.php';  // Connexion à la base de données
require '../../config/config.php';

// Obtenir les informations de la table retrait
$sql = "
    SELECT *
    FROM retrait
";

// Exécuter la requête
$stmt = $pdo->query($sql);
$retails = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/@twicpics/components@latest/dist/twicpics-components.js"></script>
    <link rel="shortcut icon" type="image/png" href="/public/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/CSS/Gamestore.css">
    <style>
        /* Style pour le logo promo */
        .promo-logo {
            position: absolute;
            top: 0;
            start: 0;
            background-color: white;
            margin-top: 10px;
            margin-left: 10px;
            width: 40px;
            height: 40px;
        }
        /* Style pour le prix promotionnel */
        .promotion-price {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>

<header> <?php require "../../src/views/menu.php"; ?> </header>
<div class="content4">
    <div class="container3">
        <div class="content4">

            <!-- Description -->

            <h1>Bienvenue chez GameStore</h1><hr>

            <p style="color: aliceblue">Gamestore est une entreprise spécialisée dans la vente de jeu vidéo.
                Nous avons 5 magasins en France à</p>

            <br>

            <div>
                <table class="tableauInfos col-12">
                    <thead>
                    <tr>
                        <th>Adresse</th><th>Ville</th><th>Code postal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($retails as $retail): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($retail['adresse']); ?></td>
                            <td><?php echo htmlspecialchars($retail['ville']); ?></td>
                            <td><?php echo htmlspecialchars($retail['code_postal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <br>

            <p style="color: aliceblue">Nous proposons des jeux vidéo sur toutes les plateformes connues à ce jour.</p>
        </div>

        <!-- Selection des meilleures ventes -->
            <?php
            // obtenir les meilleures ventes en fonction des commandes et des commandes mag
            $sql = "
            SELECT games.name AS game_name, SUM(total_sales) AS total_sales
FROM (
    SELECT order_items.game_id, SUM(order_items.quantity) AS total_sales
    FROM order_items
    INNER JOIN orders ON order_items.order_id = orders.id
    WHERE orders.status = 'LIVRE'
    GROUP BY order_items.game_id
) AS combined_sales
INNER JOIN games ON combined_sales.game_id = games.id
GROUP BY games.name
ORDER BY total_sales DESC
LIMIT 5";
 // Limiter aux 5 meilleurs ventes, par exemple

            $stmt = $pdo->query($sql);
            $topSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Préparer les données
            $gameNames = [];
            $totalSales = [];

            foreach ($topSales as $sale) {
                $gameNames[] = $sale['game_name'];
                $totalSales[] = $sale['total_sales'];
            }
            ?>


        <!-- Les derniers Jeux ajoutés -->
        <div style="border: 5px solid orangered; margin: 10px">
            <h1>Les derniers ajouts</h1><hr>
            <div id="latest-games" class="row"></div>
        </div>
    </div>
</div>

<!-- Les scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajax({
            url: '../../src/utils/api.php',
            method: 'GET',
            success: function(response) {
                console.log('Données récupérées:', response);  // Ajout de log pour vérifier la réponse
                response.forEach(function(game) {
                    // Comparer promotion_price et price, et choisir le bon prix à afficher
                    let priceToShow;
                    let priceClass = '';

                    if (parseFloat(game.promotion_price) > 0.00 && parseFloat(game.promotion_price) < parseFloat(game.price)) {
                        priceToShow = parseFloat(game.promotion_price);
                        priceClass = 'promotion-price';
                    } else {
                        priceToShow = parseFloat(game.price);
                        priceClass = 'regular-price';
                    }

                    // Crée un HTML avec ou sans logo promo
                    let promoLogo = (parseFloat(game.promotion_price) < parseFloat(game.price) && parseFloat(game.promotion_price) > 0.00) ? '<img src="../../public/asset/promo.png" alt="Promotion" class="promo-badge">' : '';

                    $('#latest-games').append(`
                        <div class="col-md-2">
                            <div class="card">
                                ${promoLogo}
                                        <img src="${game.image_url}" alt="${game.name}" style="width:400px;height:400px;">
                                     <div class="card-body">
                                    <h5 class="card-title">${game.name}</h5>
                                    <p class="card-text ${priceClass}">${priceToShow.toFixed(2)} €</p>
                                </div>
                            </div>
                        </div>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
            }
        });
    });
</script>

<script>
    // Calcule le nombre d'objets au panier
    document.addEventListener("DOMContentLoaded", function() {
        fetch('../../src/utils/count_cart_items.php')
            .then(response => response.json())
            .then(data => {
                var cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                } else {
                    console.error('Élément avec ID cart-count non trouvé');
                }
            })
            .catch(error => console.error('Erreur:', error));
    });
</script>

<br>

</body>
<footer> <?php require "../../src/views/footer.php" ?></footer>
</html>
