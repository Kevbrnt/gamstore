# GameStore - Application de Vente de Jeux Vidéo en Ligne

## Description du Projet

GameStore est une application web de vente de jeux vidéo développée dans le cadre d'un projet d'étude. Elle offre une plateforme complète pour la gestion d'un magasin de jeux vidéo en ligne, incluant des fonctionnalités pour les clients, les employés et les administrateurs.

## Table des Matières

1. [Prérequis](#prérequis)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Utilisation](#utilisation)
5. [Workflow Git](#workflow-git)
6. [Structure du Projet](#structure-du-projet)
7. [Technologies Utilisées](#technologies-utilisées)
8. [Auteurs](#auteurs)
9. [Licence](#licence)

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- MongoDB 4.4 ou supérieur
- Composer
- Serveur web (Apache ou Nginx)

## Installation

1. Clonez le dépôt :
git clone http://github.com/Kevbrnt/gamstore/master
cd gamestore
copy
2. Installez les dépendances avec Composer :
composer install
copy
3. Importez la base de données :
mysql -u KBStudio -p < database/schema.sql
mysql -u KBStudio -p < database/data.sql
copy
4. Configurez MongoDB :
- Créez une base de données nommée `gamestore`
- Importez les données initiales si nécessaire

## Configuration

1. Copiez le fichier de configuration exemple :
cp config.example.php config.php
Ccopy
2. Modifiez `connect_bdd.php` pour MySql et `connect_bdd_mongo.php` pour MongoDB avec vos paramètres de base de données et autres configurations.

## Utilisation

1. Démarrez votre serveur web local.
2. Télécharger xampp et mettez le dossier dans C:\xampp\htdocs\mettre le dossier telecharger ici
3. Accédez à l'application via : http://localhost:63342/TP/index.php
4. Utilisez les identifiants suivants pour tester les différents rôles :
- Client : username: Gamestore@Visiteur, password: Gamestore
- Employé : username: Gamestore@Employés, password: Gamestore
- Admin : username: Gamestore@Admin, password: Gamestore

Pour plus de détails sur l'utilisation, consultez le manuel d'utilisation dans `docs/manuel_utilisation.pdf`.

## Workflow Git

Nous suivons un workflow Git basé sur deux branches principales :

1. `master` : Branche de production stable
2. `develop` : Branche de développement

Pour chaque nouvelle fonctionnalité :
1. Créez une nouvelle branche à partir de `develop` :
git checkout develop
git checkout -b feature/nom-de-la-fonctionnalite
Copy2. Développez et testez votre fonctionnalité
3. Fusionnez dans `develop` une fois la fonctionnalité terminée :
git checkout develop
git merge feature/nom-de-la-fonctionnalite
Copy4. Une fois `develop` stable, fusionnez dans `main` pour la production :
git checkout main
git merge develop
Copy
## Structure du Projet
TP/
│
├── .github/        
├── asset/              
├── assets/
├── CSS/
├── JS/
├── Mongodb/
├── Mysql/
├── node_modules/
├── src/               # Code source PHP
├── vendor/            # Dépendances (géré par Composer)
├── .gitignore
├── composer.json
├── fichiers php
└── README.md
Copy
## Technologies Utilisées

- PHP 7.4
- MySQL
- MongoDB
- HTML5/CSS3
- JavaScript (jQuery)
- Bootstrap 4

## Auteurs

- Kevin BRONET - Développement initial
