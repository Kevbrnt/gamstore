$('#login-form').submit(function (e) {
    e.preventDefault();
    $.ajax({
        url: 'load_login.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#alertMessage').text("Vous êtes désormais connectez !");
                $('#alertModal').modal('show');
                $('#alertModal').on('hidden.bs.modal', function () {
                    window.location.href = 'index.php';
                });
            } else {
                $('#alertMessage').text('Erreur : ' + response.message || 'Erreur Inconnue.');
                $('#alertModal').modal('show');
            }
        },
        error: function(xhr, status, error) {
            $('#alertMessage').text("Vous n'avez pas de compte ! Veuillez vous inscrire avant de vous connectez ! ");
            $('#alertModal').modal('show');
        }
    });
});