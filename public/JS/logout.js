document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logout-btn');
    const messageContainer = document.getElementById('message-container');

    function showMessage(message, isError = false) {
        if (messageContainer) {
            messageContainer.textContent = message;
            messageContainer.className = isError ? 'alert alert-danger' : 'alert alert-success';
            messageContainer.style.display = 'block';
            setTimeout(() => {
                messageContainer.style.display = 'none';
            }, 5000);
        } else {
            alert(message);
        }
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                const csrf_token = this.getAttribute('data-csrf');
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../../src/controllers/logout.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');  // Ajouter cet en-tête
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                showMessage(response.message);
                                setTimeout(() => {
                                    window.location.href = '../../src/Pages/Accueil.php';
                                }, 2000);
                            } else {
                                showMessage('Erreur : ' + response.message, true);
                            }
                        } catch (e) {
                            console.error('Erreur lors du parsing de la réponse:', e);
                            showMessage('Une erreur est survenue lors de la déconnexion.', true);
                        }
                    } else {
                        showMessage('Erreur lors de la déconnexion', true);
                    }
                };
                xhr.onerror = function() {
                    showMessage('Erreur réseau lors de la déconnexion', true);
                };
                xhr.send('csrf_token=' + encodeURIComponent(csrf_token));
            }
        });
    }
});
