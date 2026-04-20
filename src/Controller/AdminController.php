<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ProductService;
use App\Application\OrderService;
use App\Application\AuthService;
use App\Domain\Entity\Product;

class AdminController extends Controller
{
    private ProductService $productService;
    private OrderService   $orderService;
    private AuthService $authService;

    public function __construct(
        ProductService $productService,
        OrderService   $orderService,
        AuthService $authService
    ) {
        parent::__construct();
        $this->productService = $productService;
        $this->orderService   = $orderService;
        $this->authService = $authService;
    }

    // GET /admin
    public function dashboard(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/dashboard');
    }

    // GET /admin/produits
    public function produits(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/products');
    }

    // GET /admin/commandes
    public function commandes(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/orders');
    }

    // GET /admin/clients
    public function clients(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/clients');
    }

    // POST /api/admin/produits — AJAX
    public function apiProduits(): void
    {
        $this->requireAdmin();

        $produits = $this->productService->getTousLesProduits();

        $this->json([
            'success' => true,
            'data'    => array_map(fn($p) => [
                'id'        => $p->getId(),
                'nom'       => $p->getNom(),
                'categorie' => $p->getCategorie(),
                'prix'      => $p->getPrix(),
                'stock'     => $p->getStock(),
                'image'     => $p->getImage(),
            ], $produits),
        ]);
    }

    // POST /api/admin/commandes — AJAX list
    public function apiCommandes(): void
    {
        $this->requireAdmin();

        $orders = $this->orderService->getAllOrders();

        $this->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    // POST /api/admin/commandes/statut — AJAX
    public function apiChangerStatut(): void
    {
        $this->requireAdmin();

        $id     = (int)$this->request->post('id', 0);
        $statut = $this->request->getString('statut');

        try {
            $this->orderService->changerStatut($id, $statut);
            $this->json([
                'success' => true,
                'message' => 'Statut mis à jour',
            ]);
        } catch (\RuntimeException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // POST /api/admin/produits/supprimer — AJAX
    public function apiSupprimerProduit(): void
    {
        $this->requireAdmin();

        $id = (int)$this->request->post('id', 0);

        try {
            $this->productService->supprimerProduit($id);
            $this->json(['success' => true, 'message' => 'Produit supprimé']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // POST /api/admin/clients — AJAX list
    public function apiClients(): void
    {
        $this->requireAdmin();

        $clients = $this->authService->getAllClients();

        $this->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }

    // POST /api/admin/clients/supprimer — AJAX
    public function apiSupprimerClient(): void
    {
        $this->requireAdmin();

        $id = (int)$this->request->post('id', 0);

        try {
            $this->authService->deleteClient($id);
            $this->json(['success' => true, 'message' => 'Client supprimé']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // POST /api/admin/produits/ajouter — AJAX
    public function apiAjouterProduit(): void
    {
        $this->requireAdmin();

        $nom = $this->request->getString('nom');
        $description = $this->request->getString('description');
        $prix = (float)$this->request->post('prix', 0);
        $stock = (int)$this->request->post('stock', 0);
        $categorie = $this->request->getString('categorie');

        try {
            $product = new Product(0, $nom, $description, $prix, $stock, $categorie);
            $this->productService->saveProduct($product);
            $this->json(['success' => true, 'message' => 'Produit ajouté']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // POST /api/admin/produits/modifier — AJAX
    public function apiModifierProduit(): void
    {
        $this->requireAdmin();

        $id = (int)$this->request->post('id', 0);
        $nom = $this->request->getString('nom');
        $description = $this->request->getString('description');
        $prix = (float)$this->request->post('prix', 0);
        $stock = (int)$this->request->post('stock', 0);
        $categorie = $this->request->getString('categorie');

        try {
            $product = new Product($id, $nom, $description, $prix, $stock, $categorie);
            $this->productService->saveProduct($product);
            $this->json(['success' => true, 'message' => 'Produit modifié']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
