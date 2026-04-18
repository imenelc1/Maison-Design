<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controller\AuthController;
use App\Controller\ProductController;
use App\Controller\CartController;
use App\Controller\CheckoutController;
use App\Controller\AdminController;

$router = new Router();

// Pages principales
$router->get('/',            ProductController::class, 'home');
$router->get('/categories',  ProductController::class, 'index');
$router->get('/produit/{id}', ProductController::class, 'show');

// Authentification
$router->get('/connexion',   AuthController::class, 'showLogin');
$router->post('/connexion',  AuthController::class, 'login');
$router->get('/inscription', AuthController::class, 'showRegister');
$router->post('/inscription', AuthController::class, 'register');
$router->get('/deconnexion', AuthController::class, 'logout');

// Compte client
$router->get('/compte',      AuthController::class, 'compte');

// Panier
$router->get('/panier',      CartController::class, 'show');
$router->post('/panier/ajouter',   CartController::class, 'ajouter');
$router->post('/panier/supprimer', CartController::class, 'supprimer');
$router->post('/panier/modifier',  CartController::class, 'modifier');
$router->get('/panier/vider',      CartController::class, 'vider');

// Checkout
$router->get('/checkout',    CheckoutController::class, 'show');
$router->post('/checkout',   CheckoutController::class, 'process');

// Confirmation
$router->get('/confirmation/{id}', CheckoutController::class, 'confirmation');

// Admin
$router->get('/admin',              AdminController::class, 'dashboard');
$router->get('/admin/produits',     AdminController::class, 'produits');
$router->get('/admin/commandes',    AdminController::class, 'commandes');
$router->get('/admin/clients',      AdminController::class, 'clients');

// API Admin (AJAX)
$router->post('/api/admin/produits',          AdminController::class, 'apiProduits');
$router->post('/api/admin/produits/ajouter',  AdminController::class, 'apiAjouterProduit');
$router->post('/api/admin/produits/modifier', AdminController::class, 'apiModifierProduit');
$router->post('/api/admin/produits/supprimer', AdminController::class, 'apiSupprimerProduit');
$router->post('/api/admin/commandes/statut',  AdminController::class, 'apiChangerStatut');
$router->post('/api/admin/clients/supprimer', AdminController::class, 'apiSupprimerClient');

// API panier (AJAX)
$router->post('/api/cart/add',    CartController::class, 'ajouterApi');
$router->post('/api/cart/count',  CartController::class, 'count');

return $router;