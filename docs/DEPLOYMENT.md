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
   git rm --cached --ignore-unmatch .env cookies.txt response.html lint_results.txt database.sql composer.lock || true
2. Ajoutez les fichiers sensibles à .gitignore
3. Documentez les valeurs requises dans .env.example

Sur l'environnement de production, fournissez les variables d'environnement via le système d'orchestration (systemd, docker secrets, CI/CD variables, etc.) et ne vous basez pas sur des fichiers .env livrés.
