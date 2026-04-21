<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Application\OrderService;

class CheckoutController extends Controller
{
    private CartService  $cartService;
    private OrderService $orderService;

    public function __construct(
        CartService  $cartService,
        OrderService $orderService
    ) {
        parent::__construct();
        $this->cartService  = $cartService;
        $this->orderService = $orderService;
    }

    // GET /checkout
    public function show(): void
    {
        $this->requireClient();

        if ($this->cartService->isEmpty()) {
            $this->redirect('/panier');
            return;
        }

        $items     = $this->cartService->getItems();
        $sousTotal = $this->cartService->getSousTotal();
        $livraison = 1000.0;
        $total     = $sousTotal + $livraison;

        $this->render('pages/checkout', [
            'items'     => $items,
            'sousTotal' => $sousTotal,
            'livraison' => $livraison,
            'total'     => $total,
            'flash'     => $this->getFlash(),
        ]);
    }

    // POST /checkout
   public function process(): void
{
    $this->requireClient();


    if ($this->cartService->isEmpty()) {
        $this->redirect('/panier');
        return;
    }

   $adresse = $this->request->getString('adresse_livraison');
$terms   = $this->request->post('terms');

$v = new \App\Core\Validator();
$v->required($adresse, 'adresse de livraison')
  ->minLength($adresse, 3, 'adresse de livraison');

if (!$v->isValid()) {
    $this->setFlash('error', $v->getFirstError());
    $this->redirect('/checkout');
    return;
}

if ($terms !== 'on') {
    $this->setFlash('error', 'Vous devez accepter les conditions');
    $this->redirect('/checkout');
    return;
}

    try {
        $this->requireCsrf();
        $userId = $this->request->getUserId();
        $items  = $this->cartService->getItems();

      

        $commandeId = $this->orderService->creerCommande(
            $userId,
            $items
        );

        // Insérer la livraison
        $this->orderService->ajouterLivraison($commandeId, $adresse);

        // Vider le panier
        $this->cartService->vider();

        $this->redirect('/confirmation/' . $commandeId);

    } catch (\RuntimeException $e) {
        error_log("Erreur checkout: " . $e->getMessage());
        $this->setFlash('error', $e->getMessage());
        $this->redirect('/checkout');
    }
}

    // GET /confirmation/{id}
    public function confirmation(array $params = []): void
    {
        $this->requireClient();

        $commandeId = (int)($params['id'] ?? 0);
        $commande   = $this->orderService->getCommande($commandeId);

        if ($commande === null) {
            $this->redirect('/compte');
            return;
        }

        $this->render('pages/confirmation', [
            'commande' => $commande,
        ]);
    }
}
