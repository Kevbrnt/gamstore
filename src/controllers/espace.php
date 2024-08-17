<?php

require __DIR__ . '/../../build/vendor/autoload.php';
require "../../build/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

session_start();
require_once '../../src/models/connect_bdd.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['id'];

// Fonction pour récupérer les informations de l'utilisateur
function getUserInfo($pdo, $user_id) {
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les commandes de l'utilisateur
function getUserOrders($pdo, $user_id) {
    $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$user = getUserInfo($pdo, $user_id);
$commandes = getUserOrders($pdo, $user_id);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/png" href="/public/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/CSS/Gamestore.css">
</head>
<body id="espace">
<?php include '../../src/views/menu.php'; ?>
<div class="content4">
    <div class="container3">
        <?php if ($user): ?>
            <div class='text-center my-4'>
                <?php
                $profileImage = !empty($user['image_url']) ? htmlspecialchars($user['image_url']) : '/../../public/asset/profils/defaut.png';
                ?>
                <img id='profileImage' src='<?php echo $profileImage; ?>' alt='Image de profil' class='rounded-circle' style='width: 80px; height: 80px; display: inline-flex'>
                <br><br>
                <button id='changeImageButton' class='btn btn-primary' data-toggle='modal' data-target='#uploadModal'>Changer d'image</button>
                <br><br>
                <p class='nameP'><?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div class='profilInfo'>
                <div class='infoEspace'><h2>Email: <p><?php echo htmlspecialchars($user['email']); ?></p></h2></div>
                <div class='infoEspace'><h2>Pseudo: <p><?php echo htmlspecialchars($user['username']); ?></p></h2></div>
                <div class='infoEspace'><h2>Prénom: <p><?php echo htmlspecialchars($user['first_name']); ?></p></h2></div>
                <div class='infoEspace'><h2>Nom: <p><?php echo htmlspecialchars($user['last_name']); ?></p></h2></div>
                <div class='infoEspace'><h2>Adresse : <p><?php echo htmlspecialchars($user['address']); ?></p></h2></div>
                <div class='infoEspace'><h2>Compte créé : <p><?php echo htmlspecialchars($user['add_at']); ?></p></h2></div>
            </div>
        <?php else: ?>
            <p>Utilisateur non trouvé.</p>
        <?php endif; ?>

        <h1 class="mt-4 mb-3">Modifier infos personnelles</h1>
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#resetModal">Réinitialiser le mot de passe</button>
            <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#updateModal">Modifier les informations personnelles</button>
        </div>
        <div class="text-center mb-4">
            <h1 class="mt-4 mb-3">Mes commandes</h1>
            <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#commandeModal">Mes commandes</button>
        </div>
    </div>
</div>

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
                <form id="upload-image-form" action="../../src/utils/upload_image.php" method="post" enctype="multipart/form-data">
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
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
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
                <?php if ($commandes): ?>
                    <table class='table table-striped'>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>État</th>
                            <th>Détails</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($commande['id']); ?></td>
                                <td><?php echo htmlspecialchars($commande['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($commande['total_price']); ?> €</td>
                                <td><?php echo htmlspecialchars($commande['status']); ?></td>
                                <td>
                                    <button class='btn btn-info' data-toggle='modal' data-target='#detailsModal' data-id='<?php echo htmlspecialchars($commande['id']); ?>'>
                                        Détails
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Aucune commande trouvée.</p>
                <?php endif; ?>
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
<script src="../../public/JS/Modif_img.js"></script>
<script>
    $('#upload-image-form').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: '../../src/utils/upload_image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    alert('Image de profil mise à jour avec succès.');
                    $('#profileImage').attr('src', response.imageUrl);
                    $('#uploadModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            },
            error: function() {
                alert('Une erreur est survenue lors du téléchargement de l\'image.');
            }
        });
    });

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

        $.post('../../src/controllers/password_update.php', {email: email, password: password}, function(response) {
            if (response.success) {
                alert('Mot de passe réinitialisé avec succès.');
                $('#resetModal').modal('hide');
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

        let formData = $(this).serialize();

        $.ajax({
            url: '../../src/controllers/update_info.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Réponse du serveur:', response);
                if(response.success) {
                    alert('Informations mises à jour avec succès.');
                    location.reload();
                } else {
                    alert('Erreur: ' + (response.message || 'Une erreur inconnue est survenue.'));
                    if(response.error) {
                        console.error('Erreur détaillée:', response.error);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erreur AJAX:', textStatus, errorThrown);
                console.error('Réponse du serveur:', jqXHR.responseText);
                alert('Une erreur est survenue lors de la mise à jour des informations. Vérifiez la console pour plus de détails.');
            }
        });
    });

    // Récupération des détails de la commande lors du clic sur le bouton "Détails"
    $('#detailsModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let commandeId = button.data('id');

        $.get('../../src/controllers/orders.php', {id: commandeId}, function(response) {
            $('#commande-details').html(response);
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
</body>
<?php require "../../src/views/footer.php";?>
</html>