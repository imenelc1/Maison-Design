# Maison Design 

E-commerce de meubles et décoration algérien.  
Projet de portfolio développé en PHP 8.3 avec Clean Architecture.

## Stack technique

- **Backend** : PHP 8.3, Clean Architecture (Domain / Application / Infrastructure)
- **Base de données** : MySQL
- **Frontend** : Tailwind CSS, Vanilla JS
- **Sécurité** : CSRF tokens, password_hash, validation serveur

## Architecture

```
src/
├── Domain/          ← Entités + interfaces (zéro dépendance)
├── Application/     ← Services métier
├── Infrastructure/  ← PDO, Repositories
├── Controller/      ← HTTP handlers
└── View/            ← Templates PHP
```

## Fonctionnalités

-  Catalogue produits avec filtres par catégorie (AJAX)
-  Panier en session
-  Commandes avec livraison
-  Espace client (profil + historique commandes)
-  Authentification sécurisée (CSRF + validation)
-  Panel admin (produits, commandes, clients)

## Installation

```bash
# Cloner le repo
git clone https://github.com/ton-user/maison-design.git
cd maison-design

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env.example .env
# Remplir DB_HOST, DB_NAME, DB_USER, DB_PASS

# Lancer le serveur
php -S localhost:8000 -t public/
```

## Accès

| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Accueil |
| `http://localhost:8000/categories` | Catalogue |
| `http://localhost:8000/connexion` | Connexion |
| `http://localhost:8000/admin` | Panel admin |

## Identifiants admin (démo)

```
Email    : admin.pass@maison-design.com
Password : adminpass
```