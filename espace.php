<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">
</head>
<body id="espace">
<?php include 'menu.php'; ?>
<div class="content4">
<div class="container3">
    <?php
    require_once 'connect_bdd.php';

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    // Récupérer l'ID de l'utilisateur depuis la session
    $user_id = $_SESSION['id'];

    // Récupérer les informations de l'utilisateur depuis la base de données
    $query = "SELECT * FROM gamestoretp.users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<div class='text-center my-4'>";
        // Afficher l'image de profil si elle existe, sinon afficher un placeholder
        $profileImage = !empty($user['image_url']) ? $user['image_url'] : 'asset/profils/defaut.png';
        echo "<img id='profileImage' src='{$profileImage}' alt='Image de profil' class='rounded-circle' style='width: 80px; height: 80px; display: inline-flex'>";
        echo "<br><br>";
        echo "<button id='changeImageButton' class='btn btn-primary' data-toggle='modal' data-target='#uploadModal'>Changer d'image</button>";
        echo "<br><br>";
        echo "<p class='nameP'>{$user['username']}</p>";
        echo "</div>";

        echo "<div class='profilInfo'>";
        echo "<div class='infoEspace'><h2>Email: <p>{$user['email']}</p></h2></div>";
        echo "<div class='infoEspace'><h2>Pseudo: <p>{$user['username']}</p></h2></div>";
        echo "<div class='infoEspace'><h2>Prénom: <p>{$user['first_name']}</p></h2></div>";
        echo "<div class='infoEspace'><h2>Nom: <p>{$user['last_name']}</p></h2></div>";
        echo "<div class='infoEspace'><h2>Adresse : <p>{$user['address']}</p></h2></div>";
        echo "<div class='infoEspace'><h2>Compte créé : <p>{$user['add_at']}</p></h2></div>";
        echo "</div>";
    } else {
        echo "<p>Utilisateur non trouvé.</p>";
    }
    ?>

    <h1 class="mt-4 mb-3">Modifier infos personnelles</h1>
    <div class="text-center mb-4">
        <!-- Bouton pour ouvrir la modal de réinitialisation du mot de passe -->
        <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#resetModal">Réinitialiser le mot de passe</button>

        <!-- Bouton pour ouvrir la modal de mise à jour des informations personnelles -->
        <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#updateModal">Modifier les informations personnelles</button>
<div class="text-center mb-4">
        <h1 class="mt-4 mb-3">Mes commandes</h1>
        <!-- Bouton pour ouvrir la modal de mes commandes -->
        <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#commandeModal">Mes commandes</button>
</div></div>
</div></div>
    <!-- Modal pour réinitialiser le mot de passe -->
    <div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetModalLabel">Réinitialiser le mot de passe</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="reset-password-form">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal pour télécharger l'image -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Téléchargement d'image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="upload-image-form" action="upload_image.php" method="post" enctype="multipart/form-data">
                        <input type="file" name="profile_image" accept="image/*" required>
                        <br><br>
                        <input type="submit" class="btn btn-success" value="Télécharger">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal pour mettre à jour les informations personnelles -->
    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Modifier les informations personnelles</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="update-info-form">
                        <div class="form-group">
                            <label for="update_email">Email</label>
                            <input type="email" class="form-control" id="update_email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="update_username">Pseudo</label>
                            <input type="text" class="form-control" id="update_username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="update_first_name">Prénom</label>
                            <input type="text" class="form-control" id="update_first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="update_last_name">Nom</label>
                            <input type="text" class="form-control" id="update_last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="update_address">Adresse</label>
                            <input type="text" class="form-control" id="update_address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="update-info-form">Mettre à jour</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal pour afficher les commandes -->
    <div class="modal fade" id="commandeModal" tabindex="-1" role="dialog" aria-labelledby="commandeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commandeModalLabel">Mes Commandes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php
                    // Récupérer les commandes de l'utilisateur depuis la base de données
                    $query = "SELECT * FROM gamestoretp.orders WHERE user_id = :user_id ORDER BY created_at DESC";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(['user_id' => $user_id]);
                    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($commandes) {
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>ID</th><th>Date</th><th>Total</th><th>État</th><th>Détails</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($commandes as $commande) {
                            $date = htmlspecialchars($commande['created_at']);
                            $total = htmlspecialchars($commande['total_price']);
                            $etat = htmlspecialchars($commande['status']);
                            $id = htmlspecialchars($commande['id']);
                            echo "<tr>";
                            echo "<td>{$id}</td>";
                            echo "<td>{$date}</td>";
                            echo "<td>{$total} €</td>";
                            echo "<td>{$etat}</td>";
                            echo "<td><button class='btn btn-info' data-toggle='modal' data-target='#detailsModal' data-id='{$id}'>Détails</button></td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p>Aucune commande trouvée.</p>";
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les détails de la commande -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Détails de la Commande</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="commande-details">
                    <!-- Les détails de la commande seront chargés ici par AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>


<!-- Scripts nécessaires pour Bootstrap et jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="JS/Modif_img.js"></script>
<script>
    // Validation du formulaire de réinitialisation du mot de passe
    $('#reset-password-form').submit(function(e) {
        e.preventDefault();

        let email = $('#email').val();
        let password = $('#password').val();
        let confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            alert('Les mots de passe ne correspondent pas.');
            return;
        }

        $.post('password_update.php', {email: email, password: password}, function(response) {
            if (response.success) {
                alert('Mot de passe réinitialisé avec succès.');
                $('#resetModal').modal('hide'); // Fermer la modal après succès
            } else {
                alert(response.message);
            }
        }, 'json')
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert('Une erreur est survenue lors de la réinitialisation du mot de passe.');
                console.error('Error:', textStatus, errorThrown);
            });
    });


    // Validation du formulaire de mise à jour des informations personnelles
    $('#update-info-form').submit(function(e) {
        e.preventDefault();

        let email = $('#update_email').val();
        let username = $('#update_username').val();
        let firstName = $('#update_first_name').val();
        let lastName = $('#update_last_name').val();
        let address = $('#update_address').val();

        $.post('update_info.php', {email: email, username: username, first_name: firstName, last_name: lastName, address: address}, function(response) {
            if(response.success) {
                alert('Informations mises à jour avec succès.');
                location.reload();  // Recharger la page pour mettre à jour les informations affichées
            } else {
                alert(response.message);
            }
        }, 'json')
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert('Une erreur est survenue lors de la mise à jour des informations.');
                console.error('Error:', textStatus, errorThrown);
            }); });

    // Récupération des détails de la commande lors du clic sur le bouton "Détails"
    $('#detailsModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let commandeId = button.data('id');

        $.get('orders.php', {id: commandeId}, function(response) {
            $('#commande-details').html(response);
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
