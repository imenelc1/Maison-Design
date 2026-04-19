<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ProductService;

class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    // GET /
    public function home(): void
    {
        $this->render('pages/home', [
            'flash' => $this->getFlash(),
        ]);
    }

    // GET /categories
    public function index(array $params = []): void
    {
        $categorie = $this->request->get('category', 'all');

        // Requête AJAX → retourner JSON
        if ($this->request->get('ajax') === '1') {
            $produits = $categorie === 'all'
                ? $this->productService->getTousLesProduits()
                : $this->productService->getProduitsByCategorie($categorie);

            $this->json([
                'success'  => true,
                'products' => array_map(
                    fn($p) => $this->productToArray($p),
                    $produits
                ),
            ]);
            return;
        }

        // Page normale
        $produits = $categorie === 'all'
            ? $this->productService->getTousLesProduits()
            : $this->productService->getProduitsByCategorie($categorie);

        $this->render('pages/categories', [
            'produits'         => $produits,
            'selectedCategory' => $categorie,
        ]);
    }

    // GET /produit/{id}
    public function show(array $params = []): void
    {
        $id      = (int)($params['id'] ?? 0);
        $product = $this->productService->getProduit($id);

        if ($product === null) {
            $this->redirect('/categories');
            return;
        }

        // Produits similaires
        $similaires = $this->productService
            ->getProduitsByCategorie($product->getCategorie());

        // Exclure le produit actuel
        $similaires = array_filter(
            $similaires,
            fn($p) => $p->getId() !== $product->getId()
        );

        $this->render('pages/product', [
            'product'    => $product,
            'similaires' => array_slice(array_values($similaires), 0, 4),
        ]);
    }

    // Convertir un Product en tableau pour JSON
    private function productToArray($product): array
    {
        return [
            'IdProduit'    => $product->getId(),
            'NomProduit'   => $product->getNom(),
            'Prix'         => $product->getPrix(),
            'Stock'        => $product->getStock(),
            'Description'  => $product->getDescription(),
            'categorie'    => $product->getCategorie(),
            'image'        => $product->getImage(),
            'isFavorite'   => false,
        ];
    }
}