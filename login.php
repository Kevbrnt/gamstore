
<!DOCTYPE HTML>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" type="image/png" href="/asset/favicon.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="CSS/Gamestore.css">
</head>

<body>

<header>
    <?php require "menu.php"; ?>
</header>

<div class="content4">
    <div class="container3">
        <h1>Se connecter</h1>
        <form id="login-form">
            <div class="form-group">
                <label for="username">Pseudo</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Entrez votre pseudo" required>
            </div>

            <div class="form-group">
                <label for="login-password">Mot de passe</label>
                <input type="password" class="form-control" id="login-password" name="password" placeholder="Entrez votre mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>

        <br><h4 style="color:white; padding: 10px; display: inline-block;">Mot de passe oublié : </h4>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#resetModal">Réinitialiser le mot de passe</button>
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
                        <label for="reset-email">Email</label>
                        <input type="email" class="form-control" id="reset-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="reset-password">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="reset-password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal alerts -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Alerte</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="alertMessage">
                <!-- possibilité d'ajouter des alertes -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="JS/login_form.js"></script>
<script>
    // Reset password form submission
    $('#reset-password-form').submit(function(e) {
        e.preventDefault();
        var password = $('#reset-password').val();
        var confirmPassword = $('#confirm-password').val();

        if (password !== confirmPassword) {
            alert('Les mots de passe ne correspondent pas.');
            return;
        }

        $.ajax({
            url: 'password_update.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Mot de passe mis à jour avec succès.');
                    window.location.href = 'index.php';  // Redirige vers la page de l'acceuil
                } else {
                    alert('Erreur : ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erreur AJAX:', textStatus, errorThrown);
                alert('Erreur : ' + textStatus + ' ' + errorThrown);
            }
        });
    });
</script>
<footer> <?php require "footer.php" ?> </footer>
</body>
</html>
