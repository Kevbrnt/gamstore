<?php session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à MongoDB
require 'vendor/autoload.php'; // Composer autoload file

require "connect_bdd.php";
require "connect_bdd_mongodb.php";
$chartData = [];
// Assurez-vous que $collection est défini avant de l'utiliser
if (!isset($collection)) {
die("La connexion à la base de données n'a pas été établie correctement.");
}

$result = $collection->aggregate($pipeline = [
    [
        '$group' => [
            '_id' => ['name' => '$name', 'date' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$date']]],
            'total_sales' => ['$sum' => '$sales'],
            'total_revenue' => ['$sum' => '$revenue']
        ]
    ],
    ['$sort' => ['_id.date' => 1]]
]);

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
    <title>Espace Administrateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">
</head>
<body>

<header><?php require 'menu.php'; ?></header>

<div class="content4">
    <div class="container3">
        <div class="content4">
            <div id="admin-content" class="row"></div>

            <!-- Ajout des boutons pour afficher les modals -->
            <div class="content4 mt-3">
                <button class="btn btn-success mb-2" data-toggle="modal" data-target="#createGameModal">Créer un Jeu Vidéo</button>
                <button class="btn btn-warning mb-2" data-toggle="modal" data-target="#manageStockModal">Gérer le Stock</button>
                <button class="btn btn-info mb-2" data-toggle="modal" data-target="#salesDashboardModal">Voir le Dashboard des Ventes</button>
                <button class="btn btn-primary mb-2" data-toggle="modal" data-target="#addEmployeeModal">Créer un Compte Employé</button>
            </div></div></div></div>
<div class="container3">
<div class="content4">
    <canvas id="salesChart" width="400" height="300" style="background-color: rgba(255,255,255,0.83)"></canvas>
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
</div>

            <!-- Modal Créer un Jeu Vidéo -->
            <div class="modal fade" id="createGameModal" tabindex="-1" aria-labelledby="createGameModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createGameModalLabel">Créer un Jeu Vidéo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="create-game-form" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="game_name">Nom du Jeu</label>
                                    <input type="text" class="form-control" id="game_name" name="game_name" placeholder="ex: Super Mario" required>
                                </div>
                                <div class="form-group">
                                    <label for="genre">Genre</label>
                                    <input type="text" class="form-control" id="genre" name="genre" placeholder="ex: Aventure" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Prix</label>
                                    <input type="number" class="form-control" id="price" name="price" placeholder="ex: 59.99" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="promotion_price">Prix Promotionnel</label>
                                    <input type="number" class="form-control" id="promotion_price" name="promotion_price" placeholder="ex: 49.99" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label for="stock">Quantité en Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" placeholder="ex: 20" required>
                                </div>
                                <div class="form-group">
                                    <label for="pegi">PEGI</label>
                                    <input type="text" class="form-control" id="pegi" name="pegi" placeholder="ex: 7" required>
                                </div>
                                <div class="form-group">
                                    <label for="platform">Plateforme</label>
                                    <input type="text" class="form-control" id="platform" name="platform" placeholder="ex: Nintendo Switch" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="ex: Un jeu de plateforme classique." required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="image_url">Image du Jeu</label>
                                    <input type="file" class="form-control" id="image_url" name="image_url" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary">Créer le Jeu</button>
                            </form>
                            <div id="createGameMessage" class="alert d-none mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Modal Gérer le Stock -->
        <div class="modal fade" id="manageStockModal" tabindex="-1" aria-labelledby="manageStockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manageStockModalLabel">Gérer le Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="manage-stock-form">
                            <div class="form-group">
                                <label for="game_id">Jeu</label>
                                <select class="form-control" id="game_id" name="game_id" required></select>
                            </div>
                            <div class="form-group">
                                <label for="stock_quantity">Quantité de Stock supplémentaire</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" placeholder="ex: 20" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Mettre à Jour le Stock</button>
                        </form>
                        <div id="manageStockMessage" class="alert d-none mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Voir le Dashboard des Ventes -->
        <div class="modal fade" id="salesDashboardModal" tabindex="-1" aria-labelledby="salesDashboardModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="salesDashboardModalLabel">Dashboard des Ventes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h2>Meilleures ventes de l'année</h2>
                        <canvas id="salesChart2"></canvas>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal Créer un Compte Employé -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Créer un Compte Employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-employee-form">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                    <div id="message" class="d-none"></div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- Modal HTML -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Réponse</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-message"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script src="JS/admin.js"></script>
<script>
    var ctx = document.getElementById('salesChart').getContext('2d');
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

<footer><?php require "footer.php"; ?></footer>
</body>
</html>