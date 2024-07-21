const getTotalPrice = async () => {
    try {
        const response = await fetch('get_cart.php');
        const cartItems = await response.json();

        if (cartItems.error) {
            console.error('Erreur lors de la récupération des articles du panier :', cartItems.error);
            return 0;
        }

        return cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
    } catch (error) {
        console.error('Erreur de la requête fetch:', error);
        return 0;
    }
};

const createOrder = async () => {
    const retailId = document.getElementById('retail_id').value;
    const dateRetrait = document.getElementById('date_retrait').value;
    const promotionCode = document.getElementById('promotion_code').value;

    if (!retailId || !dateRetrait) {
        alert('Veuillez sélectionner un magasin de retrait et une date de retrait.');
        return;
    }

    const totalPrice = await getTotalPrice();

    if (totalPrice <= 0) {
        alert('Le panier est vide.');
        return;
    }

    try {
        const response = await fetch('create_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                total_price: totalPrice,
                promotion_code: promotionCode,
                retail_id: retailId,
                date_retrait: dateRetrait
            })
        });

        const result = await response.json();
        alert(result);
    } catch (error) {
        console.error('Erreur:', error);
    }
};
