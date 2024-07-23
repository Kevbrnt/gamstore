$(document).ready(function() {
    // Gérer le soumission du formulaire de téléchargement d'image
    $('#upload-image-form').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'upload_image.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    // Mettre à jour l'image de profil
                    $('#profileImage').attr('src', response.image_url);

                    // Afficher la modal avec le message de succès
                    $('#uploadModal').modal('hide'); // Fermer la modal d'upload
                    $('#modal-message').text(response.message);
                    $('#successModal').modal('show'); // Modal de succès

                    // Cacher le formulaire de téléchargement si besoin
                    $('#upload-image-form').hide();
                    $('#changeImageButton').show(); // Afficher le bouton "Changer d'image"
                } else {
                    // Afficher la modal avec le message d'erreur
                    $('#uploadModal').modal('hide'); // Fermer la modal d'upload
                    $('#modal-message').text(response.message);
                    $('#errorModal').modal('show'); // Modal d'erreur
                }
            },
            error: function() {
                $('#uploadModal').modal('hide'); // Fermer la modal d'upload
                $('#modal-message').text('Erreur lors de la requête AJAX.');
                $('#errorModal').modal('show'); // Modal d'erreur générique
            }
        });
    });

    // Gérer le clic sur le bouton "Changer d'image"
    $('#changeImageButton').click(function() {
        $('#upload-image-form').show(); // Afficher le formulaire de téléchargement
        $(this).hide(); // Cacher le bouton "Changer d'image"
    });
});
$(document).ready(function() {
    // Gérer le soumission du formulaire de téléchargement d'image
    $('#upload-image-form').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'upload_image.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    // Mettre à jour l'image de profil
                    $('#profileImage').attr('src', response.image_url);

                    // Afficher la modal avec le message de succès
                    $('#uploadModal').modal('hide'); // Fermer la modal d'upload
                    $('#modal-message').text(response.message);
                    $('#successModal').modal('show'); // Modal de succès

                    // Cacher le formulaire de téléchargement si besoin
                    $('#upload-image-form').hide();
                    $('#changeImageButton').show(); // Afficher le bouton "Changer d'image"
                } else {
                    // Afficher la modal avec le message d'erreur
                    $('#uploadModal').modal('hide'); // Fermer la modal d'upload
                    $('#modal-message').text(response.message);
                    $('#errorModal').modal('show'); // Modal d'erreur
                }
            },
            error: function() {
                $('#uploadModal').modal('hide'); // Fermer la modal d'upload
                $('#modal-message').text('Erreur lors de la requête AJAX.');
                $('#errorModal').modal('show'); // Modal d'erreur générique
            }
        });
    });

    // Gérer le clic sur le bouton "Changer d'image"
    $('#changeImageButton').click(function() {
        $('#upload-image-form').show(); // Afficher le formulaire de téléchargement
        $(this).hide(); // Cacher le bouton "Changer d'image"
    });
});
