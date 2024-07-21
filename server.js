const express = require('express');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 80;

// Configuration pour servir des fichiers statiques
app.use(express.static(path.join(__dirname, 'public')));

// Exemple de route pour une page d'accueil
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.php'));
});

// Lancement du serveur sur toutes les interfaces réseau
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server is running on port ${PORT}`);
});
