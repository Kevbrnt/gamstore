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
cd gamstore

3. Installez les dépendances avec Composer :
composer install

4. Importez la base de données :
données dans les dossiers:
5. Configurez PostgresSQL :
 - Créer une base de données nommée `gamestoretp`
 - Importez les données dans le fichier postgresSQL

5. Configurez MongoDB :
- Créez une base de données nommée `Gamestore`
- Créer une collection nommée `sales`
- Struturé le tous avec 

## Configuration

1. copier et Modifiez `connect_bdd.php` pour MySql et `connect_bdd_mongo.php` pour MongoDB avec vos paramètres de base de données et autres configurations.

## Utilisation

1. Démarrez votre serveur web local.
2. Télécharger xampp et mettez le dossier dans C:\xampp\htdocs\
3. Accédez à l'application via : http://localhost:63342/gamestore-develop/index.php
4. Utilisez les identifiants suivants pour tester les différents rôles :
- Client : username: Gamestore@Visiteur, password: Gamestore
- Employé : username: Gamestore@Employés, password: Gamestore
- Admin : username: Gamestore@Admin, password: Gamestore

## Workflow Git

Nous suivons un workflow Git basé sur deux branches principales :

1. `master` : Branche de production stable
2. `develop` : Branche de développement

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
