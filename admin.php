<?php
session_start();

// Vérification de l'authentification (optionnel)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: connexion.php');
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <!-- Header avec le même style que l'original -->
    <header class="fixed top-0 left-0 w-full h-20 bg-white/90 backdrop-blur-sm flex items-center z-50 shadow-md">
        <div class="w-[90%] max-w-[1200px] mx-auto flex items-center justify-between relative">
            <!-- Logo à gauche -->
            <div class="w-[20%] flex items-center">
                <a class="logo-content flex items-center">
                    <img src="images/Logo3.png" alt="Logo Maison Design" class="w-[60px] md:w-[70px] h-auto">
                </a>
            </div>

            <!-- Navigation tabs centrée -->
            <nav class="absolute left-1/2 transform -translate-x-1/2 flex items-center gap-6">
                <button class="tab-trigger active" data-tab="clients">
                    Clients
                </button>
                <button class="tab-trigger" data-tab="produits">
                    Produits
                </button>
                <button class="tab-trigger" data-tab="commandes">
                    Commandes
                </button>
            </nav>

            <!-- Profil à droite -->
            <div class="relative">
                <button id="profile-toggle" class="profile-btn p-2 rounded-full hover:bg-primary/20 transition-colors">
                    <i class='bx bx-user-circle text-3xl text-textColor'></i>
                </button>
                <div id="profile-dropdown" class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-2 w-48 hidden">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b">Admin</div>
                    <a href="index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Retour au site
                    </a>
                    <a href="php/deconnexion.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6">
        <!-- Tabs -->
        <div class="tabs mt-8">
            <!-- Clients Tab -->
            <div id="clients-tab" class="tab-content active">
                <section>
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h2>Gestion des Clients</h2>  
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full"> 
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prenom</th>
                                    <th class="hidden md:table-cell">Email</th>
                                    <th class="hidden md:table-cell">Téléphone</th>
                                    <th class="hidden lg:table-cell">Adresse</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="clients-table-body">
                                <!-- Client rows will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-2 py-4">
                        <button id="clients-prev-page" class="px-3 py-1 border rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="text-sm" id="clients-pagination">Page 1 sur 1</span>
                        <button id="clients-next-page" class="px-3 py-1 border rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </section>
            </div>

            <!-- Products Tab -->
            <div id="produits-tab" class="tab-content hidden">
                <section>
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h2>Gestion des Produits</h2>
                        <button id="add-product-btn" class="btn-ajouter flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span>Ajouter un produit</span>
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th class="hidden md:table-cell">Catégorie</th>
                                    <th>Prix</th>
                                    <th class="hidden md:table-cell">Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="products-table-body">
                                <!-- Product rows will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-2 py-4">
                        <button id="products-prev-page" class="px-3 py-1 border rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="text-sm" id="products-pagination">Page 1 sur 1</span>
                        <button id="products-next-page" class="px-3 py-1 border rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </section>
            </div>

            <!-- Orders Tab -->
            <div id="commandes-tab" class="tab-content hidden">
                <section>
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h2>Gestion des Commandes</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th class="hidden md:table-cell">Date</th>
                                    <th>Statut</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="orders-table-body">
                                <!-- Order rows will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-2 py-4">
                        <button id="orders-prev-page" class="px-3 py-1 border rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="text-sm" id="orders-pagination">Page 1 sur 1</span>
                        <button id="orders-next-page" class="px-3 py-1 border rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Modals -->
    <!-- Delete Client Modal -->
    <div id="delete-client-modal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex">
        <div class="modal-content relative p-6 bg-white w-full max-w-md m-auto rounded-md shadow-lg">
            <button class="modal-close absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4">Confirmer la suppression</h3>
            <p class="mb-6">Êtes-vous sûr de vouloir supprimer ce client?</p>
            <form id="delete-client-form">
                <input type="hidden" name="id">
                <div class="flex justify-end gap-2">
                    <button type="button" class="modal-close btn-fermer">Annuler</button>
                    <button type="submit" class="btn-supprimer">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="add-product-modal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex">
        <div class="modal-content relative p-6 bg-white w-full max-w-md m-auto rounded-md shadow-lg">
            <button class="modal-close absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4">Ajouter un nouveau produit</h3>
            <form id="add-product-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="nom" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-md" ></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select name="categorie" class="w-full px-3 py-2 border rounded-md" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="Lits">Lits</option>
                        <option value="Armoires">Armoires</option>
                        <option value="Canapés">Canapés</option>
                        <option value="Chaises">Chaises</option>
                        <option value="Tables">Tables</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (DA)</label>
                    <input type="number" name="prix" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <input type="number" name="stock" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border rounded-md">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" class="btn-fermer">Annuler</button>
                    <button type="submit" class="btn-ajouter">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="edit-product-modal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex">
        <div class="modal-content relative p-6 bg-white w-full max-w-md m-auto rounded-md shadow-lg">
            <button class="modal-close absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4">Modifier le produit</h3>
            <form id="edit-product-form">
                <input type="hidden" name="id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="nom" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-md" placeholder="Description du produit..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select name="categorie" class="w-full px-3 py-2 border rounded-md" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="Lits">Lits</option>
                        <option value="Armoires">Armoires</option>
                        <option value="Canapés">Canapés</option>
                        <option value="Chaises">Chaises</option>
                        <option value="Tables">Tables</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (DA)</label>
                    <input type="number" name="prix" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <input type="number" name="stock" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border rounded-md">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" class="modal-close btn-fermer">Annuler</button>
                    <button type="submit" class="btn-modifier">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="delete-product-modal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex">
        <div class="modal-content relative p-6 bg-white w-full max-w-md m-auto rounded-md shadow-lg">
            <button class="modal-close absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4">Confirmer la suppression</h3>
            <p class="mb-6">Êtes-vous sûr de vouloir supprimer ce produit?</p>
            <form id="delete-product-form">
                <input type="hidden" name="id">
                <div class="flex justify-end gap-2">
                    <button type="button" class="modal-close btn-fermer">Annuler</button>
                    <button type="submit" class="btn-supprimer">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Order Details Modal -->
    <div id="view-order-modal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex">
        <div class="modal-content relative p-6 bg-white w-full max-w-3xl m-auto rounded-md shadow-lg">
            <button class="modal-close absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4">Détails de la commande #<span id="order-id"></span></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-500">Client</p>
                    <p class="font-medium" id="order-client"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date</p>
                    <p class="font-medium" id="order-date"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Statut</p>
                    <p id="order-status"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="font-medium" id="order-total"></p>
                </div>
            </div>

            <h4 class="font-semibold mb-2">Produits commandés</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="order-details-body">
                        <!-- Order details will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="modal-close btn-fermer">Fermer</button>
            </div>
        </div>
    </div>

    <!-- Change Order Status Modal -->
    <div id="change-status-modal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex">
        <div class="modal-content relative p-6 bg-white w-full max-w-md m-auto rounded-md shadow-lg">
            <button class="modal-close absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4">Changer le statut</h3>
            <form id="change-status-form">
                <input type="hidden" name="id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="statut" class="w-full px-3 py-2 border rounded-md" required>
                        <option value="En attente">En attente</option>
                        <option value="En cours">En cours</option>
                        <option value="Livré">Livré</option>
                        <option value="Annulé">Annulé</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" class="btn-fermer">Annuler</button>
                    <button type="submit" class="btn-modifier">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg z-50">
        <p id="toast-message"></p>
    </div>

    <script src="js/script2.js"></script>
</body>
</html>