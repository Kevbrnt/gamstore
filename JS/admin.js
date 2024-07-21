$(document).ready(function() {
    // Gérer la soumission du formulaire de création de jeu vidéo
    $('#create-game-form').on('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'create_game.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                var result = JSON.parse(response);
                var messageDiv = $('#createGameMessage');
                if (result.success) {
                    messageDiv.removeClass('alert-danger').addClass('alert-success').text(result.message).removeClass('d-none');
                } else {
                    messageDiv.removeClass('alert-success').addClass('alert-danger').text(result.message).removeClass('d-none');
                }
            }
        });
    });

    // Gérer la soumission du formulaire de gestion du stock
    $('#manage-stock-form').on('submit', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'manage_stock.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var result = JSON.parse(response);
                var messageDiv = $('#manageStockMessage');
                if (result.success) {
                    messageDiv.removeClass('alert-danger').addClass('alert-success').text(result.message).removeClass('d-none');
                } else {
                    messageDiv.removeClass('alert-success').addClass('alert-danger').text(result.message).removeClass('d-none');
                }
            }
        });
    });

    $(document).ready(function() {
        $('#add-employee-form').on('submit', function(event) {
            event.preventDefault();

            // Affiche les données du formulaire dans la console pour débogage
            console.log($(this).serialize());

            $.ajax({
                url: 'load_employes.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    console.log('Response received:', response);

                    try {
                        // Assurez-vous que la réponse est bien au format JSON
                        var result = JSON.parse(response);  // Si vous obtenez un JSON string, parsez-le
                        console.log('Parsed Result:', result);

                        var messageDiv = $('#modal-message');
                        var modalTitle = $('#responseModalLabel');

                        if (result.success) {
                            console.log('Success Message:', result.message);
                            messageDiv.removeClass('alert-danger').addClass('alert-success').text(result.message);
                            modalTitle.text('Succès');
                        } else {
                            console.log('Failure Message:', result.message);
                            messageDiv.removeClass('alert-success').addClass('alert-danger').text(result.message);
                            modalTitle.text('Erreur');
                        }

                        $('#responseModal').modal('show');
                    } catch (e) {
                        console.error('Error processing response:', e);
                        $('#modal-message').removeClass('alert-success').addClass('alert-danger').text('Erreur de traitement de la réponse');
                        $('#responseModalLabel').text('Erreur');
                        $('#responseModal').modal('show');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('AJAX Error:', textStatus, errorThrown);
                    $('#modal-message').removeClass('alert-success').addClass('alert-danger').text('Erreur de serveur');
                    $('#responseModalLabel').text('Erreur');
                    $('#responseModal').modal('show');
                }
            });
        });
    });


    // Récupérer les jeux pour le formulaire de gestion du stock
    $.ajax({
        url: 'get_games.php',
        type: 'GET',
        success: function(response) {
            var games = JSON.parse(response);
            var select = $('#game_id');
            select.empty();  // Vide le sélecteur avant de le remplir
            games.forEach(function(game) {
                select.append('<option value="' + game.id + '">' + game.name + '</option>');
            });
        }
    });

    // Ouvrir le modal du dashboard des ventes et afficher le graphique
    $('#salesDashboardModal').on('show.bs.modal', function() {
        $.ajax({
            url: 'dashboard.php',
            type: 'GET',
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    var ctx = $('#salesChart2')[0].getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: result.labels,
                            datasets: [{
                                label: 'Ventes par Genre',
                                data: result.data,
                                backgroundColor: 'rgba(30,144,255,0.8)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                } else {
                    $('#salesChart').html('<p class="text-danger">Erreur: ' + result.message + '</p>');
                }
            }
        });
    });
});
