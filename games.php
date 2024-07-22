<?php
session_start();
include 'connect_bdd.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['id']);

// Récupérer tous les genres uniques
$genreQuery = "SELECT DISTINCT genre FROM gamestoretp.games ORDER BY genre";
$genreStmt = $pdo->prepare($genreQuery);
$genreStmt->execute();
$genres = $genreStmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer toutes les plateformes
$platformQuery = "SELECT platform FROM gamestoretp.games ORDER BY platform";
$platformStmt = $pdo->prepare($platformQuery);
$platformStmt->execute();
$console = $platformStmt->fetchAll(PDO::FETCH_COLUMN);

// Initialiser les filtres
$highestPrice = isset($_GET['highestPrice']);
$genre = isset($_GET['genre']) ? htmlspecialchars($_GET['genre']) : '';
$platform = isset($_GET['platform']) ? htmlspecialchars($_GET['platform']) : '';
$maxPrice = isset($_GET['max_price']) ? filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT) : 0;

// SQL query pour récupérer les jeux
$sql = "SELECT * FROM gamestoretp.games WHERE 1=1";

// Ajouter des filtres
if ($genre) {
    $sql .= " AND genre = :genre";
}

if ($maxPrice > 0) {
    $sql .= " AND (promotion_price IS NULL AND price <= :max_price OR (promotion_price IS NOT NULL AND promotion_price <= :max_price))";
}
if ($platform) {
    $sql .= " AND platform = :platform";
}

$stmt = $pdo->prepare($sql);

if ($genre) {
    $stmt->bindParam(':genre', $genre, PDO::PARAM_STR);
}

if ($maxPrice > 0) {
    $stmt->bindParam(':max_price', $maxPrice, PDO::PARAM_STR);
}
if ($platform) {
    $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);
}

if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête : " . $stmt->errorInfo()[2]);
}

$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getFullImageUrl($imageUrl) {
    $baseUrl = 'https://gamestore-unique123-lingering-pine-4132.fly.dev'; // Remplacez par l'URL réelle de votre site
    if (strpos($imageUrl, 'http') === 0) {
        return $imageUrl; // L'URL est déjà complète
    }
    return $baseUrl . $imageUrl;
}
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">
    <style>
        .product-container {
            position: relative;
            display: inline-block;
        }

        .out-of-stock {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(128, 128, 128, 0.8);
            color: #fff;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            pointer-events: none;
        }

        .out-of-stock .text {
            color: white;
            font-weight: bold;
        }

        .out-of-stock img {
            filter: grayscale(100%);
        }

        .product-image {
            width: 400px;
            height: 400px;
        }
    </style>
</head>

<body>
<header>
    <?php require "menu.php"; ?>
</header>

<div class="content4">
    <div class="container3" style="color: black;">
        <div class="content4">

            <!-- Formulaire de recherche par genre -->
            <form method="GET" class="col">
                <div class="form-row">
                    <div class="col">
                        <label for="genre">Genre:</label>
                        <select id="genre" name="genre" class="form-control">
                            <option value="">Tous les genres</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $g === $genre ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Formulaire de recherche par plateformes -->
            <form method="GET" class="col">
                <div class="form-row">
                    <div class="col">
                        <label for="platform">Plateformes:</label>
                        <select id="platform" name="platform" class="form-control">
                            <option value="">Tous les plateformes</option>
                            <?php foreach ($console as $c): ?>
                                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $c === $platform ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <br>
                <label for="priceRange">Prix maximum:</label>
                <input type="range" name="max_price" id="priceRange" class="form-control-range" min="0" max="<?php echo htmlspecialchars($highestPrice) ?>" value="<?php echo htmlspecialchars($maxPrice); ?>" oninput="updatePriceLabel(this.value)">
                <label class="color_write">Prix: <span id="priceLabel"><?php echo htmlspecialchars($maxPrice); ?></span>€</label>
                <div><button type="submit" class="btn btn-primary">Rechercher</button></div>
            </form>

            <br>
            <div class="row">
                <?php
                // Vérifier s'il y a des résultats
                if ($stmt->rowCount() > 0) {
                    foreach ($games as $row) {
                        // Calculer la remise
                        $hasDiscount = !empty($row['promotion_price']) && $row['promotion_price'] < $row['price'];
                        $finalPrice = $hasDiscount ? $row['promotion_price'] : $row['price'];
                        $isOutOfStock = $row['stock'] <= 0;

                        echo "<div class='product-container'>";
                        if ($hasDiscount) {
                            echo "<div class='promo-badge'></div>";
                        }
                        echo "<div class='out-of-stock' style='display: " . ($isOutOfStock ? 'block' : 'none') . ";'><div class='text'>Rupture de stock</div></div>";
                        echo "<img src='" . htmlspecialchars(getFullImageUrl($row['image_url'])) . "' class='product-image' alt='" . htmlspecialchars($row['name']) . "' data-toggle='modal' data-target='#detailsModal" . htmlspecialchars($row['id']) . "' onerror=\"this.src='https://votre-site.fly.dev/asset/default-image.jpg'\">";
                        echo "</div>";

                        // Modal pour chaque jeu
                        echo "<div class='modal fade' id='detailsModal" . htmlspecialchars($row['id']) . "' tabindex='-1' role='dialog' aria-labelledby='detailsModalLabel" . htmlspecialchars($row['id']) . "' aria-hidden='true'>";
                        echo "<div class='modal-dialog modal-dialog-centered' role='document'>";
                        echo "<div class='modal-content'>";
                        echo "<div class='modal-header'>";
                        echo "<h5 class='modal-title' id='detailsModalLabel" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</h5>";
                        echo "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
                        echo "<span aria-hidden='true'>&times;</span>";
                        echo "</button>";
                        echo "</div>";
                        echo "<div class='modal-body'>";
                        echo "<img src='" . htmlspecialchars(getFullImageUrl($row['image_url'])) . "' class='card-img-top' alt='" . htmlspecialchars($row['name']) . "' onerror=\"this.src='https://votre-site.fly.dev/asset/default-image.jpg'\">";
                        echo "<p><strong>Description:</strong> " . htmlspecialchars($row['description']) . "</p>";
                        echo "<p><strong>Genre:</strong> " . htmlspecialchars($row['genre']) . "</p>";
                        echo "<p><strong>PEGI:</strong> " . htmlspecialchars($row['pegi']) . "</p>";
                        echo "<p><strong>Console:</strong> " . htmlspecialchars($row['platform']) . "</p>";
                        echo "<p><strong>Stock:</strong> " . htmlspecialchars($row['stock']) . "</p>";
                        if ($hasDiscount) {
                            echo "<p class='card-text'><strong>Prix:</strong> <span style='color:red;text-decoration:line-through;'>" . htmlspecialchars($row['price']) . "€</span> <span style='color:green;'>" . htmlspecialchars($finalPrice) . "€</span></p>";
                        } else {
                            echo "<p class='card-text'><strong>Prix:</strong> " . htmlspecialchars($finalPrice) . "€</p>";
                        }
                        echo "</div>";
                        echo "<div class='modal-footer'>";
                        echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Fermer</button>";
                        if ($isLoggedIn && !$isOutOfStock) {
                            echo "<button class='btn btn-primary addToCartBtn' data-game-id='" . htmlspecialchars($row['id']) . "'>Ajouter au panier</button>";
                        } elseif ($isLoggedIn) {
                            echo "<button class='btn btn-secondary' disabled>Rupture de stock</button>";
                        } else {
                            echo "<button class='btn btn-secondary' disabled>Connectez-vous pour ajouter au panier</button>";
                        }
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='col-md-10' style='color: white;'>0 résultat trouvé</p>";
                }
                ?>
            </div></div></div></div>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function updateCartCount() {
        fetch('count_cart_items.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(error => console.error('Erreur:', error));
    }

    function updatePriceLabel(value) {
        document.getElementById('priceLabel').innerText = value;
    }

    window.onload = function() {
        const priceRange = document.getElementById('priceRange');
        updatePriceLabel(priceRange.value);
    };

    $(document).ready(function() {
        $('.addToCartBtn').click(function(e) {
            e.preventDefault();
            const gameId = $(this).data('game-id');
            $.ajax({
                type: 'POST',
                url: 'add_to_cart.php',
                data: { game_id: gameId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        updateCartCount();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX:', error);
                }
            });
        });

        // Affichage du modal au clic sur l'image
        $('.product-image').click(function() {
            const modalId = $(this).data('target');
            $(modalId).modal('show');
        });

        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const message = urlParams.get('message');

        if (success !== null && message !== null) {
            $('#successModal').modal('show');
            $('#modalMessage').text(message);
        }
    });
</script>

<!-- Footer -->
<footer>
    <?php require "footer.php"; ?>
</footer>

</body>
</html>