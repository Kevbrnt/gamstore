<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">
</head>
<style>
    .table-compact, .table-compact-small, .table-full {
        display: none;
    }

    @media (max-width: 800px) {
        .table-compact {
            display: table;
        }
    }

    @media (max-width: 500px) {
        .table-compact-small {
            display: table;
        }
        .table-compact {
            display: none;
        }
    }

    @media (min-width: 801px) {
        .table-full {
            display: table;
        }
    }
</style>

<body>
<?php require "menu.php"; ?>

<div style="margin: 20px;"><a class="btn btn-danger" href="espace.php">Retour</a></div>

<div class="content4">
    <div class="container3">
        <div class="content4">
            <h1>Mes Commandes</h1>
            <?php
            require "connect_bdd.php";

            // Vérifiez que l'utilisateur est connecté
            if (isset($_SESSION['id'])) {
                $user_id = $_SESSION['id'];

                // Dépannage : afficher l'ID utilisateur
                echo '<p>ID utilisateur : ' . htmlspecialchars($user_id) . '</p>';

                // Préparez la requête pour obtenir les commandes de l'utilisateur connecté
                $stmt = $pdo->prepare("SELECT orders.id, orders.total_price, orders.status, orders.created_at
                                       FROM orders
                                       WHERE orders.user_id = :user_id
                                       ORDER BY orders.created_at DESC");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($orders) {
                    echo '<table class="table table-compact-small">';
                    echo '<thead><tr><th>ID</th><th>Total</th><th>Status</th><th>Détails</th></tr></thead>';
                    echo '<tbody>';
                    foreach ($orders as $order) {
                        echo '<tr>';
                        echo '<td>' . $order['id'] . '</td>';
                        echo '<td>' . number_format($order['total_price'], 2) . '€</td>';
                        echo '<td>' . htmlspecialchars($order['status']) . '</td>';
                        echo '<td><button class="btn btn-info" data-toggle="modal" data-target="#detailsModal" data-order-id="' . $order['id'] . '">Détails</button></td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>Aucune commande trouvée.</p>';
                }
            } else {
                echo '<p>Vous devez être connecté pour voir vos commandes.</p>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Modale pour afficher les détails de la commande -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Détails de la commande</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Contenu des détails sera chargé ici via Ajax -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $('#detailsModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Bouton qui a déclenché la modal
        var orderId = button.data('order-id'); // Extrait l'information de l'attribut data-*

        var modal = $(this);
        $.ajax({
            url: 'orders.php',
            type: 'GET',
            data: { order_id: orderId },
            success: function (response) {
                modal.find('.modal-body').html(response);
            },
            error: function () {
                modal.find('.modal-body').html('<p>Une erreur est survenue lors du chargement des détails de la commande.</p>');
            }
        });
    });
</script>
</body>
<footer><?php require 'footer.php' ?></footer>
</html>
