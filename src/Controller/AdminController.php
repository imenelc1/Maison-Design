<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\AuthService;
use App\Application\OrderService;
use App\Application\ProductService;
use App\Domain\Entity\Product;

class AdminController extends Controller
{
    private ProductService $productService;
    private OrderService $orderService;
    private AuthService $authService;

    public function __construct(
        ProductService $productService,
        OrderService $orderService,
        AuthService $authService
    ) {
        parent::__construct();
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->authService = $authService;
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/dashboard');
    }

    public function produits(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/products');
    }

    public function commandes(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/orders');
    }

    public function clients(): void
    {
        $this->requireAdmin();
        $this->render('pages/admin/clients');
    }

    public function apiProduits(): void
    {
        $this->requireAdmin();

        $produits = $this->productService->getTousLesProduits();

        $this->json([
            'success' => true,
            'data' => array_map(fn($p) => [
                'id' => $p->getId(),
                'nom' => $p->getNom(),
                'categorie' => $p->getCategorie(),
                'prix' => $p->getPrix(),
                'stock' => $p->getStock(),
                'image' => $p->getImage(),
            ], $produits),
        ]);
    }

    public function apiCommandes(): void
    {
        $this->requireAdmin();

        $orders = $this->orderService->getAllOrders();

        $this->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function apiChangerStatut(): void
    {
        $this->requireAdmin();

        $id = (int)$this->request->post('id', 0);
        $statut = $this->request->getString('statut');

        try {
            $this->orderService->changerStatut($id, $statut);
            $this->json([
                'success' => true,
                'message' => 'Statut mis a jour',
            ]);
        } catch (\RuntimeException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function apiSupprimerProduit(): void
    {
        $this->requireAdmin();

        $id = (int)$this->request->post('id', 0);

        try {
            $this->productService->supprimerProduit($id);
            $this->json(['success' => true, 'message' => 'Produit supprime']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function apiClients(): void
    {
        $this->requireAdmin();

        $clients = $this->authService->getAllClients();

        $this->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    public function apiSupprimerClient(): void
    {
        $this->requireAdmin();

        $id = (int)$this->request->post('id', 0);

        try {
            $this->authService->deleteClient($id);
            $this->json(['success' => true, 'message' => 'Client supprime']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

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
            $productId = $this->productService->saveProduct($product);

            if (isset($_FILES['image']) && (int)($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $imagePath = $this->uploadProductImage($_FILES['image']);

                try {
                    $this->productService->saveProductImage($productId, $imagePath);
                } catch (\Throwable $e) {
                    $fullPath = ROOT_PATH . '/public/' . $imagePath;
                    if (is_file($fullPath)) {
                        unlink($fullPath);
                    }

                    throw $e;
                }
            }

            $this->json(['success' => true, 'message' => 'Produit ajoute']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

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
            $this->json(['success' => true, 'message' => 'Produit modifie']);
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function uploadProductImage(array $file): string
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException("Erreur lors de l'envoi de l'image");
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Fichier image invalide');
        }

        $mimeType = mime_content_type($tmpName) ?: '';
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            throw new \RuntimeException('Format image non supporte');
        }

        $uploadDir = ROOT_PATH . '/public/images';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException("Impossible de creer le dossier d'images");
        }

        $originalName = (string)($file['name'] ?? 'image');
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '-', $baseName) ?: 'produit';
        $safeBaseName = trim($safeBaseName, '-');
        if ($safeBaseName === '') {
            $safeBaseName = 'produit';
        }

        $fileName = time() . '_' . $safeBaseName . '.' . $allowedMimeTypes[$mimeType];
        $targetPath = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \RuntimeException("Impossible d'enregistrer l'image");
        }

        return 'images/' . $fileName;
    }
}
