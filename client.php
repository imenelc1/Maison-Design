<?php
// Démarrer la session et vérifier si l'utilisateur est connecté
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Déterminer l'onglet actif
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
        <script src="tailwind.config.js"></script>

    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-background">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-[1200px] mx-auto">
            <!-- Onglets -->
            <div class="mb-8 flex flex-wrap gap-2">
                <button class="tab-button <?php echo $activeTab === 'profile' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="profile">
                    <i class='bx bx-user-circle mr-1'></i> Profil
                </button>
                <button class="tab-button <?php echo $activeTab === 'orders' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="orders">
                    <i class='bx bx-package mr-1'></i> Mes Commandes
                </button>
                <button class="tab-button <?php echo $activeTab === 'addresses' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="addresses">
                    <i class='bx bx-map mr-1'></i> Mes Adresses
                </button>
                <button class="tab-button <?php echo $activeTab === 'wishlist' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="wishlist">
                    <i class='bx bx-heart mr-1'></i> Mes Favoris
                </button>
            </div>
            
            <!-- Contenu des onglets -->
            <div class="tab-content">
                <!-- Onglet Profil -->
                <div id="profile-tab" class="tab-pane <?php echo $activeTab === 'profile' ? 'block' : 'hidden'; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <h2 class="text-2xl text-accent mb-6">Informations personnelles</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Prénom</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['prenom'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Nom</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['nom'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Téléphone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['telephone'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Date d'inscription</p>
                                <p class="font-medium"><?php echo isset($_SESSION['date_inscription']) ? date('d/m/Y', strtotime($_SESSION['date_inscription'])) : ''; ?></p>
                            </div>
                        </div>
                        
                        <button class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
                            <i class='bx bx-edit mr-1'></i> Modifier mon profil
                        </button>
                    </div>
                </div>
                
                <!-- Onglet Commandes -->
                <div id="orders-tab" class="tab-pane <?php echo $activeTab === 'orders' ? 'block' : 'hidden'; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <h2 class="text-2xl text-accent mb-6">Mes commandes</h2>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <i class='bx bx-package text-3xl text-gray-400'></i>
                            </div>
                            <p class="text-gray-500 mb-4">Vous n'avez pas encore passé de commande.</p>
                            <a href="categories.php" class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                                <i class='bx bx-shopping-bag mr-1'></i> Découvrir nos produits
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Onglet Adresses -->
                <div id="addresses-tab" class="tab-pane <?php echo $activeTab === 'addresses' ? 'block' : 'hidden'; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl text-accent">Mes adresses</h2>
                            <button class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center">
                                <i class='bx bx-plus mr-1'></i> Ajouter une adresse
                            </button>
                        </div>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <i class='bx bx-map text-3xl text-gray-400'></i>
                            </div>
                            <p class="text-gray-500 mb-4">Vous n'avez pas encore ajouté d'adresse.</p>
                            <button class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                                <i class='bx bx-plus mr-1'></i> Ajouter une adresse
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Onglet Favoris -->
                <div id="wishlist-tab" class="tab-pane <?php echo $activeTab === 'wishlist' ? 'block' : 'hidden'; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <h2 class="text-2xl text-accent mb-6">Mes favoris</h2>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <i class='bx bx-heart text-3xl text-gray-400'></i>
                            </div>
                            <p class="text-gray-500 mb-4">Vous n'avez pas encore ajouté de produits à vos favoris.</p>
                            <a href="categories.php" class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                                <i class='bx bx-shopping-bag mr-1'></i> Découvrir nos produits
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="js/client.js"></script>
</body>
</html>
