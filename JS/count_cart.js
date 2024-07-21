// Fonction pour mettre à jour le nombre d'articles dans le panier
function updateCartCount() {
    fetch('count_cart_items.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('cart-count').textContent = data.count;
        })
        .catch(error => console.error('Erreur:', error));
}

// Mettre à jour le nombre d'articles au chargement de la page
window.onload = function() {
    var priceRange = document.getElementById('priceRange');
    updatePriceLabel(priceRange.value);
    updateCartCount();  // Mettre à jour le nombre d'articles dans le panier
};

// Mettre à jour le nombre d'articles lors de l'ajout au panier
$(document).ready(function() {
    $('.addToCartBtn').click(function(e) {
        e.preventDefault();
        var gameId = $(this).data('game-id');
        $('#modal' + gameId).modal('show');
        updateCartCount();  // Mettre à jour le nombre d'articles dans le panier
    });

    // Récupérer les paramètres GET pour afficher la modal de message
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const message = urlParams.get('message');

    if (success !== null && message !== null) {
        $('#successModal').modal('show');
        $('#modalMessage').text(message);
    }
});
