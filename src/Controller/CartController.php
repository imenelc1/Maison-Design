<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;

class CartController extends Controller
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        parent::__construct();
        $this->cartService = $cartService;
    }

    // GET /panier
    public function show(): void
    {
        $this->requireClient();

        $items      = $this->cartService->getItems();
        $sousTotal  = $this->cartService->getSousTotal();
        $livraison  = 1000.0;
        $total      = $sousTotal + $livraison;

        $this->render('pages/cart', [
            'items'     => $items,
            'sousTotal' => $sousTotal,
            'livraison' => $livraison,
            'total'     => $total,
            'flash'     => $this->getFlash(),
        ]);
    }

    // POST /panier/ajouter
    public function ajouter(): void
    {
        $this->requireCsrf();
        $this->requireClient();

        $id       = (int)$this->request->post('produitId', 0);
        $quantite = (int)$this->request->post('quantite', 1);

        try {
            $this->cartService->ajouter($id, $quantite);
            $this->setFlash('success', 'Produit ajouté au panier');
        } catch (\RuntimeException $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/panier');
    }

    // POST /panier/supprimer
    public function supprimer(): void
    {
        $this->requireCsrf();
        $this->requireClient();

        $id = (int)$this->request->post('produitId', 0);
        $this->cartService->supprimer($id);
        $this->redirect('/panier');
    }

    // POST /panier/modifier
    public function modifier(): void
    {
        $this->requireCsrf();
        $this->requireClient();

        $id    = (int)$this->request->post('produitId', 0);
        $delta = (int)$this->request->post('delta', 0);
        $this->cartService->modifierQuantite($id, $delta);
        $this->redirect('/panier');
    }

    // GET /panier/vider
    public function vider(): void
    {
        $this->requireClient();

        $this->cartService->vider();
        $this->redirect('/panier');
    }

    // POST /api/cart/add — AJAX
    public function ajouterApi(): void
    {
        if (!$this->request->isLoggedIn()) {
            $this->json([
                'success' => false,
                'message' => 'Veuillez vous connecter',
            ], 401);
        }

        if ($this->request->getUserRole() === 'admin') {
            $this->json([
                'success' => false,
                'message' => 'Un administrateur ne peut pas utiliser le panier',
            ], 403);
        }

        $id       = (int)$this->request->post('produitId', 0);
        $quantite = (int)$this->request->post('quantite', 1);

        try {
            $this->cartService->ajouter($id, $quantite);
            $this->json([
                'success'   => true,
                'message'   => 'Produit ajouté au panier',
                'cartCount' => $this->cartService->getNombreArticles(),
            ]);
        } catch (\RuntimeException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // POST /api/cart/count — AJAX
    public function count(): void
    {
        if ($this->request->getUserRole() === 'admin') {
            $this->json([
                'success' => true,
                'count'   => 0,
            ]);
        }

        $this->json([
            'success' => true,
            'count'   => $this->cartService->getNombreArticles(),
        ]);
    }
}
