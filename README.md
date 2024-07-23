# GameStore - Application de Vente de Jeux Vidéo en Ligne

## Description du Projet

GameStore est une application web de vente de jeux vidéo développée dans le cadre d'un projet d'étude. Elle offre une plateforme complète pour la gestion d'un magasin de jeux vidéo en ligne, incluant des fonctionnalités pour les clients, les employés et les administrateurs.

## Table des Matières

1. [Prérequis](#prérequis)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Utilisation](#utilisation)
5. [Utilisation Web](#utilisation-web)
6. [Gestion de projet](#gestion-de-projet)
7. [Workflow Git](#workflow-git)
8. [Structure du Projet](#structure-du-projet)
9. [Technologies Utilisées](#technologies-utilisées)
10. [Auteurs](#auteurs)

## Prérequis

- PHP 8.2 ou supérieur
- Postgres 16.3
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
5. Configurez MySql :
 - Créer une base de données nommée `gamestoretp`
 - Importez les données dans le fichier MySql

5. Configurez MongoDB :
- Créez une base de données nommée `gamestore`
- Importez les données dans le fichier MongoDB

## Configuration

1. copier et Modifiez `connect_bdd.php` pour PstgresSQL et `connect_bdd_mongo.php` pour MongoDB avec vos paramètres de base de données et autres configurations.

## Utilisation

1. Démarrez votre serveur web local.
2. Télécharger xampp et mettez le dossier dans C:\xampp\htdocs\
3. Accédez à l'application via : http://localhost:63342/gamestore-develop/index.php
4. Utilisez les identifiants suivants pour tester les différents rôles :
- Client : username: Gamestore@Visiteur, password: Gamestore
- Employé : username: Gamestore@Employés, password: Gamestore
- Admin : username: Gamestore@Admin, password: Gamestore

## Utilisation Web
1. rendez-vous ici https://gamestoreprojet.fly.dev/ pour visualiser le site en version web
2. !! Attention !! il se paut que vous soyez déconecter tous seul actualiser la page jusqu'à être de nouveau connecter.
3. Utilisez les identifiants suivants pour tester les différents rôles :
- Client : username: Gamestore@Visiteur, password: Gamestore
- Employé : username: Gamestore@Employés, password: Gamestore
- Admin : username: Gamestore@Admin, password: Gamestore

## Gestion de projet
1. La gestion de projet a était réalisé avec :
 - Trellio => 
 - draw.io =>
 - Figma => https://www.figma.com/embed?embed_host=share&url=https%3A%2F%2Fwww.figma.com%2Fproto%2FKhrSEx4UK4m1yGRHcmuNHL%2FTP-Gamestore%3Fnode-id%3D226-2114%26t%3DKUuppvLjMVgcJZCn-1%26scaling%3Dscale-down%26content-scaling%3Dfixed%26page-id%3D224%253A296%26starting-point-node-id%3D226%253A2114" allowfullscreen>(https://www.figma.com/design/FohSgP3SzZErParSxwvCWg/TP-Gamestore-(Community)?node-id=0-1&t=Lm51Gg2zaw9JSS6e-1)
   
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
