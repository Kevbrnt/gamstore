<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

require 'connect_bdd.php';  // Connexion à la base de données

// Obtenir les informations de la table retrait
$sql = "SELECT * FROM gamestoretp.retrait";
$stmt = $pdo->query($sql);
$retails = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getFullImageUrl($imageUrl) {
    $baseUrl = 'https://gamestore-unique123-lingering-pine-4132.fly.dev'; // Remplacez par l'URL réelle de votre site
    if (strpos($imageUrl, 'http') === 0) {
        return $imageUrl; // L'URL est déjà complète
    }
    return $baseUrl . $imageUrl;
}
?>

<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="shortcut icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">
    <style>
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
        .promotion-price {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>

<header> <?php require "menu.php"; ?> </header>
<div class="content4">
    <div class="container3">
        <div class="content4">
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

        <div style="border: 5px solid orangered; margin: 10px">
            <h1>Les derniers ajouts</h1><hr>
            <div id="latest-games" class="row"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajax({
            url: 'api.php',
            method: 'GET',
            success: function(response) {
                console.log('Données récupérées:', response);
                response.forEach(function(game) {
                    let priceToShow;
                    let priceClass = '';

                    if (parseFloat(game.promotion_price) > 0.00 && parseFloat(game.promotion_price) < parseFloat(game.price)) {
                        priceToShow = parseFloat(game.promotion_price);
                        priceClass = 'promotion-price';
                    } else {
                        priceToShow = parseFloat(game.price);
                        priceClass = 'regular-price';
                    }

                    let promoLogo = (parseFloat(game.promotion_price) < parseFloat(game.price) && parseFloat(game.promotion_price) > 0.00) ? '<img src="asset/promo.png" alt="Promotion" class="promo-badge">' : '';

                    $('#latest-games').append(`
                        <div class="col-md-2">
                            <div class="card">
                                ${promoLogo}
                                <img src="${getFullImageUrl(game.image_url)}" class="card-img-top" alt="${game.name}" style="width:400px;height:400px;" onerror="this.src='https://votre-site.fly.dev/asset/default-image.jpg'">
                                <div class="card-body"  style="height: 150px">
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

    document.addEventListener("DOMContentLoaded", function() {
        fetch('count_cart_items.php')
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
<footer> <?php require "footer.php" ?></footer>
</body>

</html>