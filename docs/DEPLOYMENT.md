Déploiement — rappel de sécurité

Ne commitez jamais les fichiers suivants dans le dépôt de code source ou les packages de livraison:

- .env (ou toute variante .env.*)
- cookies.txt
- response.html
- lint_results.txt
- database.sql
- composer.lock (si vous fournissez un package distribué sous forme autre que PHP vendor)

Avant de livrer/packager :

1. Exécutez :
   git rm --cached --ignore-unmatch .env cookies.txt response.html lint_results.txt database.sql || true
2. Ajoutez les fichiers sensibles à .gitignore
3. Documentez les valeurs requises dans .env.example

Migrations versionnées

- Les scripts de migration SQL/PHP doivent être stockés dans `migrations/` et exécutés explicitement.
- Ne placez plus d'`ALTER TABLE` conditionnels dans la couche modèle.
- Exemple d'ordre d'exécution :
  1. `php migrations/001_roles.php`
  2. `php migrations/002_profile.php`
  3. `php migrations/003_delivery.php`
  4. `php migrations/004_delivery_address.php`
  5. `php migrations/005_delivery_options.php`
  6. `php migrations/006_add_order_columns.php`
  7. `php migrations/007_add_gps_coordinates.php`
  8. `php migrations/008_add_user_image.php`
  9. `php migrations/009_add_product_updated_at.php`

Sur l'environnement de production, fournissez les variables d'environnement via le système d'orchestration (systemd, docker secrets, CI/CD variables, etc.) et ne vous basez pas sur des fichiers .env livrés.
