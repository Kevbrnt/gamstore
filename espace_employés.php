<?php
session_start();
require 'connect_bdd.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employés') {
    echo "Accès non autorisé.";
    exit;
}

require 'menu.php';

// Variables pour les messages
$message = '';
$message_class = '';

// Connexion à MongoDB
require 'vendor/autoload.php'; // Composer autoload
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérification de l'inclusion de PHPMailer
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "Erreur : PHPMailer n'est pas trouvée. Vérifiez votre configuration.";
    exit;
}

try {
    $client = new MongoClient("mongodb://localhost:27017");
    $collection = $client->gamestore->sales;
} catch (Exception $e) {
    echo "Erreur de connexion à MongoDB : " . $e->getMessage();
    exit;
}

// Gestion de la commande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = 'LIVRÉ'; // On change le statut à 'LIVRÉ'

    // On met à jour le statut de la commande dans la base de données
    $stmt = $pdo->prepare("UPDATE orders SET status = :new_status WHERE id = :order_id AND status = 'VALIDÉ'");
    $stmt->execute(['new_status' => $new_status, 'order_id' => $order_id]);

    // Vérification du succès de la mise à jour
    if ($stmt->rowCount() > 0) {
        $message = "Commande mise à jour avec succès !";
        $message_class = 'alert-success';

        // Récupérer les détails de la commande pour mettre à jour MongoDB
        $stmt = $pdo->prepare("
            SELECT games.name, games.price, order_items.quantity
            FROM order_items
            JOIN games ON order_items.game_id = games.id
            WHERE order_items.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $order_id]);
        $order_items = $stmt->fetchAll();

        // Mettre à jour les ventes d'articles dans MongoDB
        foreach ($order_items as $item) {
            // Obtenir la date du jour en format MongoDB
            $currentDate = new UTCDateTime();

            $result = $collection->updateOne(
                ['name' => $item['name']],
                [
                    '$inc' => [
                        'sales' => $item['quantity'],
                        'revenue' => $item['price'] * $item['quantity']
                    ],
                    '$set' => ['date' => $currentDate]
                ],
                ['upsert' => true]
            );

            // Vérification de l'insertion ou mise à jour dans MongoDB
            if ($result->getModifiedCount() > 0) {
                // Succès
                // Optionnel : Vous pouvez ajouter un message de succès pour MongoDB si nécessaire
            } else {
                // Échec
                // Optionnel : Vous pouvez ajouter un message d'erreur pour MongoDB si nécessaire
            }
        }

        // Envoyer l'email de confirmation
        try {
            // Récupérer les informations de l'utilisateur depuis la base de données
            $stmt = $pdo->prepare("
        SELECT users.first_name, users.last_name, users.email, users.username,
               orders.total_price, orders.date_retrait, orders.retail_id,
               retrait.adresse, retrait.code_postal, retrait.ville
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN retrait ON orders.retail_id = retrait.id
        WHERE orders.id = :order_id 
    ");
            $stmt->execute(['order_id' => $order_id]);
            $user_info = $stmt->fetch();

            if ($user_info) {
                $first_name = $user_info['first_name'];
                $last_name = $user_info['last_name'];
                $email = $user_info['email'];
                $username = $user_info['username'];
                $price = $user_info['total_price'];
                $adresse = $user_info['adresse'];
                $code_postal = $user_info['code_postal'];
                $ville = $user_info['ville'];
                $date = $user_info['date_retrait'];

                // Vous pouvez maintenant utiliser $adresse, $ville et $code_postal comme suit :
                $retrait = $adresse . ', ' . $ville . ' ' . $code_postal;

                // Configurer le serveur SMTP
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Remplacez par le serveur SMTP de votre fournisseur d'email
                $mail->SMTPAuth = true;
                $mail->Username = 'praskavisuelle@gmail.com'; // Remplacez par votre adresse email
                $mail->Password = 'jezg kshu phxl rmnw'; // Remplacez par le mot de passe de votre adresse email
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Expéditeur et destinataire
                $mail->setFrom('noreply@gamestore.com', 'Gamestore');
                $mail->addAddress($email, $username);

                // Contenu de l'email
                $mail->isHTML(true);
                $mail->Subject = 'Votre commande Gamestore a été livrée !';
                $mail->Body = "<h1>Bonjour $first_name $last_name,</h1>
                                <p>Votre commande sur Gamestore a été retirer avec succès !</p>
                                <p>le $date au magasin situé au $retrait</p>
                                <p>Vous avez payez $price € .</p>
                                <p>Merci pour votre achat!</p>
                                <p>à bientôt,<br>L'équipe Gamestore</p>";

                $mail->send();
            } else {
                $message = "Échec de l'envoi de l'email : les informations de l'utilisateur ne sont pas disponibles.";
                $message_class = 'alert-danger';
            }
        } catch (Exception $e) {
            // En cas d'erreur d'envoi de l'email
            $message = "Erreur d'envoi d'email : " . $mail->ErrorInfo;
            $message_class = 'alert-danger';
        }

    } else {
        $message = "Échec de la mise à jour de la commande ou la commande n'est pas en statut 'VALIDÉ'.";
        $message_class = 'alert-danger';
    }
}

// Récupérer les commandes avec le statut 'VALIDÉ' et les informations des clients
$stmt = $pdo->query("
    SELECT orders.id, orders.created_at, orders.date_retrait, orders.total_price, orders.status, users.first_name, users.last_name
    FROM orders
    JOIN users ON orders.user_id = users.id
    WHERE orders.status = 'VALIDÉ'
");
$valid_orders = $stmt->fetchAll();

// Préparation des données pour le graphique
$chartData = [];

$pipeline = [
    [
        '$group' => [
            '_id' => ['name' => '$name', 'date' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$date']]],
            'total_sales' => ['$sum' => '$sales'],
            'total_revenue' => ['$sum' => '$revenue']
        ]
    ],
    ['$sort' => ['_id.date' => 1]]
];

$result = $collection->aggregate($pipeline);

foreach ($result as $entry) {
    $articleName = $entry['_id']['name'];
    $salesDate = $entry['_id']['date'];
    $totalSales = $entry['total_sales'];
    $totalRevenue = $entry['total_revenue'];

    if (!isset($chartData[$articleName])) {
        $chartData[$articleName] = [];
    }

    $chartData[$articleName][] = ['date' => $salesDate, 'sales' => $totalSales, 'revenue' => $totalRevenue];
}

// Convertir $chartData en JSON pour l'utiliser dans JavaScript
$chartDataJSON = json_encode($chartData);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Employé</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">

</head>
<body>
<div class="content4">
    <div class="container3">
        <h1>Espace Employé</h1>

        <!-- Bouton pour afficher les commandes en attente de livraison -->
        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#commandesModal">Commandes en attente de livraison</button>
        <div class="content4">
        <canvas id="salesChart3" width="400" height="300" style="background-color: rgba(255,255,255,0.83)"></canvas>
        <button id="detailsButton" class="btn btn-info mt-3">Détails des ventes par article</button>
            <!-- Modal pour les détails des ventes -->
            <div class="modal fade" id="salesDetailModal" tabindex="-1" role="dialog" aria-labelledby="salesDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="salesDetailModalLabel">Détails des ventes par article</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label for="articleFilter">Filtrer par article :</label>
                            <select id="articleFilter" class="form-control">
                                <option value="all">Tous</option>
                                <!-- Options seront ajoutées dynamiquement -->
                            </select>
                            <table id="salesDetailTable" class="table table-bordered mt-3">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Nom de l'article</th>
                                    <th>Nombre de ventes</th>
                                    <th>Prix total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Les données seront ajoutées dynamiquement -->
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div></div>
<!-- Modal des Commandes en Attente -->
<div class="modal fade" id="commandesModal" tabindex="-1" role="dialog" aria-labelledby="commandesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commandesModalLabel">Commandes en Attente de Livraison</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Date de retrait</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($valid_orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($order['date_retrait']); ?></td>
                            <td><?php echo htmlspecialchars($order['total_price']); ?> €</td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#orderDetailModal" data-order-id="<?php echo htmlspecialchars($order['id']); ?>" data-order-firstname="<?php echo htmlspecialchars($order['first_name']); ?>" data-order-lastname="<?php echo htmlspecialchars($order['last_name']); ?>" data-order-created="<?php echo htmlspecialchars($order['created_at']); ?>" data-order-retrait="<?php echo htmlspecialchars($order['date_retrait']); ?>" data-order-total="<?php echo htmlspecialchars($order['total_price']); ?>" data-order-status="<?php echo htmlspecialchars($order['status']); ?>">
                                    Voir les détails
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails de la commande -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Détails de la commande</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="orderDetailContent"></div>
                <form id="updateOrderForm" action="" method="POST">
                    <input type="hidden" name="order_id" id="order_id" value="">
                    <button type="submit" name="update_order_status" class="btn btn-success">Marquer comme LIVRÉ</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Message d'alerte -->
<?php if ($message): ?>
    <div class="alert <?php echo $message_class; ?> alert-dismissible fade show mt-3" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Script pour afficher les détails de la commande -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Configurer le modal pour afficher les détails de la commande et récupérer l'ID de la commande
    $('#orderDetailModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Bouton qui a déclenché le modal
        var orderId = button.data('order-id'); // Récupère l'ID de la commande
        var firstName = button.data('order-firstname'); // Récupère le prénom du client
        var lastName = button.data('order-lastname'); // Récupère le nom du client
        var createdAt = button.data('order-created'); // Récupère la date de la commande
        var dateRetrait = button.data('order-retrait'); // Récupère la date de retrait
        var totalPrice = button.data('order-total'); // Récupère le montant total
        var status = button.data('order-status'); // Récupère le statut de la commande

        var modal = $(this);
        modal.find('#order_id').val(orderId); // Met à jour l'input hidden avec l'ID de la commande

        // Met à jour le contenu du modal avec les détails de la commande
        modal.find('#orderDetailContent').html(`
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td>${orderId}</td>
                </tr>
                <tr>
                    <th>Client</th>
                    <td>${firstName} ${lastName}</td>
                </tr>
                <tr>
                    <th>Date de commande</th>
                    <td>${createdAt}</td>
                </tr>
                <tr>
                    <th>Date de retrait</th>
                    <td>${dateRetrait}</td>
                </tr>
                <tr>
                    <th>Montant</th>
                    <td>${totalPrice} €</td>
                </tr>
                <tr>
                    <th>Statut</th>
                    <td>${status}</td>
                </tr>
            </table>
        `);
    });
</script>

<!-- Script pour afficher le graphique des ventes -->
<script>
    var ctx = document.getElementById('salesChart3').getContext('2d');
    var chartData = <?php echo $chartDataJSON; ?>;

    var chartLabels = Object.keys(chartData[Object.keys(chartData)[0]].map(function (item) { return item.date; }));
    var chartDatasets = [];

    // Tableau de couleurs pour chaque article (optionnel pour les graphiques en barres)
    var colors = [
        '#FF5733', '#FFC300', '#36A2EB', '#4BC0C0', '#FF6384', '#9966FF', '#FF9900'
    ];

    Object.keys(chartData).forEach(function (articleName, index) {
        var sales = chartData[articleName].map(function (item) { return item.sales; });

        chartDatasets.push({
            label: articleName,
            data: sales,
            backgroundColor: colors[index % colors.length], // Couleur de fond pour les barres
            borderColor: colors[index % colors.length], // Couleur de bordure pour les barres
            borderWidth: 1 // Largeur de la bordure des barres (en pixels)
        });
    });

    var salesChart = new Chart(ctx, {
        type: 'bar', // Type de graphique en barres
        data: {
            labels: chartLabels,
            datasets: chartDatasets
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true // Axe Y commence à zéro
                }

            }
        }
    });
</script>
<script>
    // Récupère les données du graphique pour le filtre et le tableau des détails
    var chartData = <?php echo $chartDataJSON; ?>;

    // Fonction pour mettre à jour le tableau des détails de ventes en fonction du filtre
    function updateSalesDetailTable(selectedArticle) {
        var tableBody = $('#salesDetailTable tbody');
        tableBody.empty(); // Vide le corps du tableau

        if (selectedArticle === 'all') {
            // Affiche tous les articles
            Object.keys(chartData).forEach(function(articleName) {
                var salesDetails = chartData[articleName];
                salesDetails.forEach(function(item) {
                    var row = `<tr>
                                    <td>${item.date}</td>
                                    <td>${articleName}</td>
                                    <td>${item.sales}</td>
                                    <td>${item.revenue.toFixed(2)} €</td>
                                </tr>`;
                    tableBody.append(row);
                });
            });
        } else {
            // Affiche seulement l'article sélectionné
            var salesDetails = chartData[selectedArticle];
            salesDetails.forEach(function(item) {
                var row = `<tr>
                                <td>${item.date}</td>
                                <td>${selectedArticle}</td>
                                <td>${item.sales}</td>
                                <td>${item.revenue.toFixed(2)} €</td>
                            </tr>`;
                tableBody.append(row);
            });
        }
    }

    // Rafraîchit les options de filtre pour les articles
    function refreshArticleFilterOptions() {
        var articleFilter = $('#articleFilter');
        articleFilter.empty(); // Vide la liste déroulante

        // Ajoute une option pour chaque article dans le graphique
        Object.keys(chartData).forEach(function(articleName) {
            articleFilter.append(`<option value="${articleName}">${articleName}</option>`);
        });

        // Ajoute une option pour afficher tous les articles
        articleFilter.append(`<option value="all">Tous</option>`);
    }

    // Initialisation
    refreshArticleFilterOptions();

    // Gestion de l'événement de changement de sélection d'article
    $('#articleFilter').change(function() {
        var selectedArticle = $(this).val();
        updateSalesDetailTable(selectedArticle); // Met à jour le tableau des détails de ventes avec l'article sélectionné
    });

    // Affiche le modal des détails de ventes lors du clic sur le bouton
    $('#detailsButton').click(function() {
        updateSalesDetailTable('all'); // Affiche tous les articles par défaut
        $('#salesDetailModal').modal('show'); // Affiche le modal
    });
</script>



</body>
<footer><?php require "footer.php"?></footer>
</html>
