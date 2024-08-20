<?php
require __DIR__ . '/../../src/utils/session_management.php';
require __DIR__ . '/../../build/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['id'];

require_once '../../src/models/connect_bdd.php';

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

// Fonction pour échapper les sorties HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace</title>
    <link rel="icon" type="image/png" href="../../public/asset/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/CSS/Gamestore.css">
</head>
<body id="espace">
<?php include '../../src/views/menu.php'; ?>

<!-- Zone d'affichage des messages -->
<div id="message-container" class="alert" style="display: none;"></div>

<div class="content4">
    <div class="container3">
        <?php if ($user): ?>
            <div class='text-center my-4'>
                <?php
                $profileImage = !empty($user['image_url']) ? e($user['image_url']) : '/../../public/asset/profils/defaut.png';
                ?>
                <img id='profileImage' src='<?php echo $profileImage; ?>' alt='Image de profil' class='rounded-circle' style='width: 80px; height: 80px; display: inline-flex'>
                <br><br>
                <button id='changeImageButton' class='btn btn-primary' data-toggle='modal' data-target='#uploadModal'>Changer d'image</button>
                <br><br>
                <p class='nameP'><?php echo e($user['username']); ?></p>
            </div>

            <div class='profilInfo'>
                <div class='infoEspace'><h2>Email: <p><?php echo e($user['email']); ?></p></h2></div>
                <div class='infoEspace'><h2>Pseudo: <p><?php echo e($user['username']); ?></p></h2></div>
                <div class='infoEspace'><h2>Prénom: <p><?php echo e($user['first_name']); ?></p></h2></div>
                <div class='infoEspace'><h2>Nom: <p><?php echo e($user['last_name']); ?></p></h2></div>
                <div class='infoEspace'><h2>Adresse : <p><?php echo e($user['address']); ?></p></h2></div>
                <div class='infoEspace'><h2>Compte créé : <p><?php echo e($user['add_at']); ?></p></h2></div>
            </div>
        <?php else: ?>
            <p>Utilisateur non trouvé.</p>
        <?php endif; ?>

        <h1 class="mt-4 mb-3">Modifier infos personnelles</h1>
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#resetPasswordModal">Réinitialiser le mot de passe</button>
            <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#updateModal">Modifier les informations personnelles</button>
        </div>
        <div class="text-center mb-4">
            <h1 class="mt-4 mb-3">Mes commandes</h1>
            <button type="button" class="btn btn-primary mx-2" data-toggle="modal" data-target="#commandeModal">Mes commandes</button>
        </div>
    </div>
</div>

<!-- Modal de réinitialisation de mot de passe -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Réinitialiser le mot de passe</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reset-password-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="reset-email">Adresse e-mail</label>
                        <input type="email" class="form-control" id="reset-email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer le lien de réinitialisation</button>
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
                <form id="upload-image-form" action="../utils/upload_image.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="update_email">Email</label>
                        <input type="email" class="form-control" id="update_email" name="email" value="<?php echo e($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="update_username">Pseudo</label>
                        <input type="text" class="form-control" id="update_username" name="username" value="<?php echo e($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="update_first_name">Prénom</label>
                        <input type="text" class="form-control" id="update_first_name" name="first_name" value="<?php echo e($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="update_last_name">Nom</label>
                        <input type="text" class="form-control" id="update_last_name" name="last_name" value="<?php echo e($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="update_address">Adresse</label>
                        <input type="text" class="form-control" id="update_address" name="address" value="<?php echo e($user['address']); ?>" required>
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
                                <td><?php echo e($commande['id']); ?></td>
                                <td><?php echo e($commande['created_at']); ?></td>
                                <td><?php echo e($commande['total_price']); ?> €</td>
                                <td><?php echo e($commande['status']); ?></td>
                                <td>
                                    <button class='btn btn-info' data-toggle='modal' data-target='#detailsModal' data-id='<?php echo e($commande['id']); ?>'>
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
<script>
    // Fonction pour afficher les messages
    function showMessage(message, isError = false) {
        const messageContainer = $('#message-container');
        messageContainer.removeClass('alert-success alert-danger').addClass(isError ? 'alert-danger' : 'alert-success');
        messageContainer.text(message).fadeIn().delay(3000).fadeOut();
    }

    // Gestion du formulaire de téléchargement d'image
    $('#upload-image-form').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: '../../src/utils/upload_image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showMessage('Image de profil mise à jour avec succès.');
                    $('#profileImage').attr('src', response.imageUrl);
                    $('#uploadModal').modal('hide');
                } else {
                    showMessage(response.message || 'Une erreur est survenue lors du téléchargement de l\'image.', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                showMessage('Une erreur est survenue lors du téléchargement de l\'image.', true);
            }
        });
    });

    // Gestion du formulaire de réinitialisation du mot de passe
    $('#reset-password-form').submit(function(e) {
        e.preventDefault();
        let email = $('#reset-email').val();
        let csrfToken = $('input[name="csrf_token"]', this).val();

        $.ajax({
            url: '../../src/controllers/reset_password.php',
            type: 'POST',
            data: {
                email: email,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('Un lien de réinitialisation a été envoyé à votre adresse e-mail.');
                    $('#resetPasswordModal').modal('hide');
                } else {
                    showMessage(response.message || 'Une erreur est survenue lors de la réinitialisation du mot de passe.', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                showMessage('Une erreur est survenue lors de la réinitialisation du mot de passe.', true);
            }
        });
    });

    // Gestion du formulaire de mise à jour des informations personnelles
    $('#update-info-form').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: '../../src/controllers/update_info.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showMessage('Informations mises à jour avec succès.');
                    $('#updateModal').modal('hide');
                    updateDisplayedInfo(response.user);
                } else {
                    showMessage('Erreur: ' + (response.message || 'Une erreur inconnue est survenue.'), true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                console.error('Réponse du serveur:', xhr.responseText);
                showMessage('Une erreur est survenue lors de la mise à jour des informations.', true);
            }
        });
    });

    // Fonction pour mettre à jour les informations affichées
    function updateDisplayedInfo(user) {
        $('.nameP').text(user.username);
        $('.infoEspace h2:contains("Email") p').text(user.email);
        $('.infoEspace h2:contains("Pseudo") p').text(user.username);
        $('.infoEspace h2:contains("Prénom") p').text(user.first_name);
        $('.infoEspace h2:contains("Nom") p').text(user.last_name);
        $('.infoEspace h2:contains("Adresse") p').text(user.address);
    }

    // Récupération des détails de la commande
    $('#detailsModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let commandeId = button.data('id');

        $.ajax({
            url: '../../src/controllers/orders.php',
            type: 'GET',
            data: {id: commandeId},
            success: function(response) {
                $('#commande-details').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                $('#commande-details').html('Une erreur est survenue lors de la récupération des détails de la commande.');
            }
        });
    });

    // Calcul du nombre d'objets dans le panier
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
            .catch(error => {
                console.error('Erreur lors du comptage des articles du panier:', error);
            });
    });
</script>
</body>
<?php require "../../src/views/footer.php";?>
</html>