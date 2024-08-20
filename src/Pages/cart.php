<?php
require __DIR__ . '/../../build/vendor/autoload.php';
require_once __DIR__ . '/../utils/session_management.php';
require_once __DIR__ . '/../models/connect_bdd.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

//  Activer le rapport d'erreurs pour le débogage
/*
error_reporting(E_ALL);
ini_set('display_errors', 1); */

// Vérifier si l'utilisateur est connecté
$user = getUserSession();
if (!$user) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header('Location: /src/Pages/login.php');
    exit;
}

$user_id = $user['id'];

// Récupérer les articles du panier
$stmt = $pdo->prepare("
    SELECT cart.id, games.name, games.price, games.promotion_price,
           cart.quantity, games.image_url, cart.game_id
    FROM cart
    INNER JOIN games ON cart.game_id = games.id
    WHERE cart.user_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le total du panier
$total_panier = array_sum(array_map(function($item) {
    return ($item['promotion_price'] > 0 ? $item['promotion_price'] : $item['price']) * $item['quantity'];
}, $cart_items));

// Mettre à jour le total du panier dans la session
$_SESSION['total_price'] = $total_panier;

// Récupérer les adresses de retrait
$stmt = $pdo->query("SELECT * FROM retrait");
$retails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer les dates min et max pour le retrait
$date_min = date('Y-m-d', strtotime('+1 day'));
$date_max = date('Y-m-d', strtotime('+7 days'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier</title>
    <link rel="icon" type="image/png" href="../../public/asset/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/CSS/Gamestore.css">
</head>
<body>

<header>
    <?php require __DIR__ . "/../../src/views/menu.php" ?>
</header>

<div class="content4">
    <div class="container3">
        <h1>Votre Panier</h1>

        <div id="message-container" class="alert" style="display: none;"></div>

        <?php if (empty($cart_items)): ?>
            <p>Votre panier est vide.</p>
        <?php else: ?>
            <div class="card-container">
                <?php foreach ($cart_items as $item): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="card-img" onerror="this.src='https://via.placeholder.com/150?text=Image+non+disponible';">
                        <div class="card-content">
                            <p class="card-title" style="color: black;"><?php echo htmlspecialchars($item['name']); ?></p>
                            <p class="card-quantity" style="color: black;">Quantité : <?php echo htmlspecialchars($item['quantity']); ?></p>
                            <p class="card-price" style="color: black;">Prix unitaire : <?php echo number_format($item['promotion_price'] > 0 ? $item['promotion_price'] : $item['price'], 2); ?> €</p>
                            <p class="card-total" style="color: black;">Total : <?php echo number_format(($item['promotion_price'] > 0 ? $item['promotion_price'] : $item['price']) * $item['quantity'], 2); ?> €</p>
                            <button class="btn btn-danger remove-item" data-id="<?php echo htmlspecialchars($item['id']); ?>">Supprimer</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total">
                <p><strong>Total du Panier : <span id="total-price"><?php echo number_format($total_panier, 2); ?> €</span></strong></p>
            </div>

            <form id="order-form" method="POST" action="../controllers/create_order.php">
                <h1>Adresse de Retrait</h1><hr>
                <select id="retail-select" class="form-control retail-select" name="retail_id" required>
                    <?php foreach ($retails as $retail): ?>
                        <option value="<?php echo htmlspecialchars($retail['id']); ?>">
                            <?php echo htmlspecialchars($retail['adresse']) . ', ' . htmlspecialchars($retail['ville']) . ' ' . htmlspecialchars($retail['code_postal']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <h1>Date de Retrait</h1><hr>
                <div class="form-group">
                    <input type="date" id="date_retrait" name="date_retrait" class="form-control" required min="<?php echo $date_min; ?>" max="<?php echo $date_max; ?>">
                    <small id="dateError" class="form-text text-danger" style="display: none;">La date doit être comprise entre demain et dans 7 jours, et ne peut être ni un lundi ni un dimanche.</small>
                </div>

                <input type="hidden" name="total_price" value="<?php echo number_format($total_panier, 2); ?>">
                <button type="submit" class="btn btn-success mt-3">Passer la Commande</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<footer>
    <?php require __DIR__ . "/../../src/views/footer.php" ?>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function showMessage(message, type) {
        const messageContainer = document.getElementById('message-container');
        messageContainer.textContent = message;
        messageContainer.className = `alert alert-${type}`;
        messageContainer.style.display = 'block';
    }

    function updateCartCount() {
        fetch('../../src/utils/count_cart_items.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(error => console.error('Erreur:', error));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dateRetrait = document.getElementById('date_retrait');
        const dateError = document.getElementById('dateError');

        dateRetrait.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const day = selectedDate.getDay();
            if (day === 0 || day === 1) {
                dateError.style.display = 'block';
                this.value = '';
            } else {
                dateError.style.display = 'none';
            }
        });

        document.querySelectorAll('.remove-item').forEach(function(button) {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;

                fetch('../../src/utils/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'item_id': itemId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            updateCartCount();
                            location.reload();
                        } else {
                            showMessage('Erreur lors de la suppression de l\'article: ' + data.message, 'danger');
                        }
                    })
                    .catch(error => console.error('Erreur:', error));
            });
        });

        document.getElementById('order-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch('../../src/controllers/create_order.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '../../src/Pages/cart.php';
                        }, 3000);
                    } else {
                        showMessage('Erreur : ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showMessage('Une erreur est survenue lors de la création de la commande.', 'danger');
                });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        updateCartCount();
    });
</script>
</body>
</html>