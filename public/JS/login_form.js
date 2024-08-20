$(document).ready(function() {
    $('#login-form').submit(function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '../../src/controllers/login.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    // Afficher le message d'erreur
                    $('#error-message').text(response.message).show();
                }
            },
            error: function () {
                $('#error-message').text('Une erreur est survenue. Veuillez réessayer.').show();
            }
        });
    });

    // Gestion du formulaire de réinitialisation de mot de passe
    $('#reset-password-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '../../src/controllers/reset_password.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Réponse du serveur:', response);
                if (response.success) {
                    alert(response.message);
                    $('#resetPasswordModal').modal('hide');
                } else {
                    alert('Erreur: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                console.log('Réponse du serveur:', xhr.responseText);
                alert('Une erreur est survenue. Veuillez vérifier la console pour plus de détails.');
            }
        });
    });
});