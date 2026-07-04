# Farmmarket

Plateforme web de gestion et de vente pour élevages de lapins et de poulets.

## Structure du projet

- `config/` : configuration de la base de données
- `core/` : classes de base MVC
- `controllers/` : logique de routage et traitement
- `models/` : accès aux données MySQL
- `views/` : interfaces utilisateurs
- `public/` : assets front-end (CSS, JS, images)
- `index.php` : point d'entrée unique

## Installation

1. Copier le dossier `FARMMARKET` dans le répertoire de votre serveur local (par exemple `xampp/htdocs/farmmarket`).
2. Créer une base MySQL nommée `farmmarket`.
3. Importer le fichier `database.sql` dans PhpMyAdmin ou via la ligne de commande.
4. Vérifier les paramètres de connexion dans `config/database.php`.
5. Accéder à `http://localhost/farmmarket/index.php`.

## Comptes de test

- Admin : `admin@farmmarket.test` / `Admin123!`
- Client : `client@farmmarket.test` / `Client123!`

## Fonctionnalités présentes

- Inscription / connexion / déconnexion
- Consultation de produits
- Ajout et suppression dans le panier
- Validation de commande avec paiement simulé
- Historique des commandes
- Tableau de bord administrateur simplifié

## Notes techniques

- Architecture MVC procédurale en PHP
- PDO avec requêtes préparées
- Validation côté serveur
- Sessions PHP pour authentification
- Sorties HTML échappées avec `htmlspecialchars`

## Améliorations possibles

- Gestion complète CRUD produits/catégories/admin
- Filtrage AJAX par catégorie
- Gestion des paiements et des commandes avancée
- Ajout de CSRF pour tous les formulaires
