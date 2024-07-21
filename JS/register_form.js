$('#register-form').submit(function (e) {
    e.preventDefault();
    $.ajax({
        url: 'load_register.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#alertMessage').text("Compte crée! Vous pouvez désormais vous connectez !");
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
            $('#alertMessage').text('Vous avez déjà un compte, veuillez vous connecter.');
            $('#alertModal').modal('show');
        }
    });
});