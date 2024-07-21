<?php
session_start();
require "menu.php";
include 'connect_bdd.php';

// Vérifiez que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];

// Récupérer les articles du panier
$stmt = $pdo->prepare("
    SELECT cart.id, games.name, 
        CASE 
            WHEN games.promotion_price > 0 THEN games.promotion_price 
            ELSE games.price 
        END AS price, 
        cart.quantity, 
        (CASE 
            WHEN games.promotion_price > 0 THEN games.promotion_price 
            ELSE games.price 
        END * cart.quantity) AS total_price, 
        games.image_url
    FROM cart
    INNER JOIN games ON cart.game_id = games.id
    WHERE cart.user_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_panier = array_sum(array_column($cart_items, 'total_price'));
$_SESSION['total_price'] = $total_panier; // Mettre à jour le total du panier dans la session

// Récupérer les adresses de retrait
$sql = "SELECT * FROM retrait";
$stmt = $pdo->query($sql);
$retails = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="CSS/Gamestore.css">
    <style>
        .message-success {
            color: green;
            font-weight: bold;
        }
        .message-error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="content4">
    <div class="container3">
        <h1>Votre Panier</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <p>Votre panier est vide.</p>
        <?php else: ?>

            <div class="card-container">
                <?php foreach ($cart_items as $item): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="card-img" onerror="this.src='https://via.placeholder.com/150?text=Image+non+disponible';">
                        <div class="card-content">
                            <h1 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h1>
                            <p class="card-quantity" style="color: black">Quantité : <?php echo htmlspecialchars($item['quantity']); ?></p>
                            <p class="card-total" style="color: black">Total : <?php echo number_format($item['total_price'], 2); ?> €</p>
                            <button class="btn btn-danger remove-item" data-id="<?php echo htmlspecialchars($item['id']); ?>">Supprimer</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total">
                <p><strong>Total du Panier : <span id="total-price"><?php echo number_format($total_panier, 2); ?> €</span></strong></p>
            </div>

        <?php endif; ?>

        <form id="order-form" method="POST" action="create_order.php">
            <h1>Adresse de Retrait</h1>
            <select id="retail-select" class="form-control retail-select" name="retail_id" required>
                <?php foreach ($retails as $retail): ?>
                    <option value="<?php echo htmlspecialchars($retail['id']); ?>">
                        <?php echo htmlspecialchars($retail['adresse']) . ', ' . htmlspecialchars($retail['ville']) . ' ' . htmlspecialchars($retail['code_postal']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br>

            <h1>Date de Retrait</h1>
            <div class="form-group">
                <input type="date" id="date_retrait" name="date_retrait" class="form-control" min="" max="" required>
                <small id="dateError" class="form-text text-danger" style="display: none;">La date doit être inférieure à 7 jours et ne peut être ni un lundi ni un dimanche.</small>
            </div>

            <input type="hidden" name="total_price" value="<?php echo number_format($total_panier, 2); ?>">
            <button type="submit" class="btn btn-success mt-3">Passer la Commande</button>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalMessageBody">
                <!-- Message from create_orders.php will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="JS/Date_limite.js"></script>
<script>
    // Mettre à jour le nombre d'articles dans le panier
    function updateCartCount() {
        fetch('count_cart_items.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(error => console.error('Erreur:', error));
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Définir la date minimum et maximum pour le champ date
        const today = new Date();
        const minDate = today.toISOString().split('T')[0];
        const maxDate = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        document.getElementById('date_retrait').setAttribute('min', minDate);
        document.getElementById('date_retrait').setAttribute('max', maxDate);

        // Valider la date de retrait
        document.getElementById('date_retrait').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const day = selectedDate.getDay();
            const dateError = document.getElementById('dateError');
            if (day === 0 || day === 1) {
                dateError.style.display = 'block';
                this.value = '';
            } else {
                dateError.style.display = 'none';
            }
        });

        // Gestion de la suppression des articles
        document.querySelectorAll('.remove-item').forEach(function(button) {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;

                fetch('remove_from_cart.php', {
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
                            alert('Erreur lors de la suppression de l\'article: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Erreur:', error));
            });
        });

        // Gestion de la soumission du formulaire de commande
        document.getElementById('order-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch('create_order.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Ajouter cette ligne pour déboguer
                    const modalMessageBody = document.getElementById('modalMessageBody');
                    if (data.success) {
                        modalMessageBody.textContent = 'Commande passée avec succès !';
                        $('#messageModal').modal('show');
                        setTimeout(() => {
                            window.location.href = 'cart.php';
                        }, 3000); // Redirige après 3 secondes
                    } else {
                        modalMessageBody.textContent = 'Erreur : ' + data.error;
                        $('#messageModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    const modalMessageBody = document.getElementById('modalMessageBody');
                    modalMessageBody.textContent = 'Une erreur est survenue lors de la création de la commande.';
                    $('#messageModal').modal('show');
                });
        });
    });

</script>
<script>
    // Calcule le nombre d'objets au panier
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
</body>
<?php require "footer.php";?>
</html>
