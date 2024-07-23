$(document).ready(function() {
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
                    // Utiliser l'API pour l'URL de l'image
                    var apiImageUrl = API_URL + '?key=' + API_KEY + '&image=' + encodeURIComponent(response.image_url);
                    $('#profileImage').attr('src', apiImageUrl);

                    $('#uploadModal').modal('hide');
                    $('#modal-message').text(response.message);
                    $('#successModal').modal('show');

                    $('#upload-image-form').hide();
                    $('#changeImageButton').show();
                } else {
                    $('#uploadModal').modal('hide');
                    $('#modal-message').text(response.message);
                    $('#errorModal').modal('show');
                }
            },
            error: function() {
                $('#uploadModal').modal('hide');
                $('#modal-message').text('Erreur lors de la requête AJAX.');
                $('#errorModal').modal('show');
            }
        });
    });

    $('#changeImageButton').click(function() {
        $('#upload-image-form').show();
        $(this).hide();
    });
});