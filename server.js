const express = require('express');
const app = express();
const port = process.env.PORT || 8080;

// Route pour la page d'accueil
app.get('/', (req, res) => {
    res.send('Bienvenue sur GameStore!');
});

// Route pour gérer les 404
app.use((req, res, next) => {
    res.status(404).send("Désolé, cette page n'existe pas!");
});

app.listen(port, '0.0.0.0', () => {
    console.log(`Server running on port ${port}`);
});

app.use(express.static('public'));
app.use((req, res, next) => {
    console.log(`Request received: ${req.method} ${req.url}`);
    next();
});