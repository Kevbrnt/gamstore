const express = require('express');
const app = express();
const port = process.env.PORT || 80;

// Middleware pour logger les requêtes
app.use((req, res, next) => {
    console.log(`Request received: ${req.method} ${req.url}`);
    next();
});

// Route pour exécuter le fichier PHP
app.get('/', (req, res) => {
    const phpScriptPath = path.join(__dirname, 'index.php'); // Spécifiez le fichier PHP à exécuter
    exec(`php ${phpScriptPath}`, (error, stdout, stderr) => {
        if (error) {
            console.error(`exec error: ${error}`);
            res.status(500).send(`Server Error: ${error}`);
            return;
        }
        if (stderr) {
            console.error(`stderr: ${stderr}`);
        }
        res.send(stdout);
    });
    module.exports = app;
});

/*
app.listen(port, '0.0.0.0', () => {
    console.log(`Server running on port ${port}`);
});


// Middleware pour gérer les 404
app.use((req, res, next) => {
    res.status(404).send("Désolé, cette page n'existe pas!");
});

app.listen(port, '0.0.0.0', () => {
    console.log(`Server running on port ${port}`);
});*/

