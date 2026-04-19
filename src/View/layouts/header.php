<?php
// Calculer le nombre d'articles dans le panier
$nombreArticles = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $nombreArticles += $item['quantite'];
    }
}

$nomUtilisateur = $_SESSION['prenom'] ?? '';
$isLoggedIn     = isset($_SESSION['user_id']);
$isAdmin        = ($_SESSION['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Maison Design'; ?></title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="font-cormorant bg-background m-0 p-0">

<?php if (isset($flash) && $flash): ?>
<div class="fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg
    <?php echo $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
    <?php echo htmlspecialchars($flash['message']); ?>
</div>
<script>
    setTimeout(() => document.querySelector('.fixed.top-20').remove(), 3000);
</script>
<?php endif; ?>

<header class="fixed top-0 left-0 w-full h-16 sm:h-20 bg-white/50 backdrop-blur-sm flex items-center justify-center z-50 shadow-md">
    <div class="w-[95%] max-w-[1200px]">
        <nav class="flex items-center justify-between">
            <a href="/" class="flex items-center">
                <img src="/images/Logo3_1_1.png" alt="Maison Design" class="w-[45px] sm:w-[60px] h-auto">
            </a>

            <div class="hidden lg:flex flex-1 justify-center">
                <ul class="flex gap-8">
                    <li><a href="/" class="text-textColor hover:text-accent transition-colors">Accueil</a></li>
                    <li><a href="/categories" class="text-textColor hover:text-accent transition-colors">Catégories</a></li>
                </ul>
            </div>

            <div class="flex items-center gap-3">
                <!-- Panier -->
                <a href="/panier" class="relative p-2 text-textColor hover:text-accent">
                    <i class='bx bx-cart text-2xl'></i>
                    <?php if ($nombreArticles > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-accent text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        <?php echo $nombreArticles; ?>
                    </span>
                    <?php endif; ?>
                </a>

                <!-- User -->
                <?php if ($isLoggedIn): ?>
                <div class="relative hidden lg:block">
                    <a href="/compte" class="flex items-center gap-1 px-3 py-1.5 bg-primary rounded-full hover:bg-primary/80 text-sm">
                        <i class='bx bx-user'></i>
                        <?php echo htmlspecialchars($nomUtilisateur); ?>
                    </a>
                </div>
                <a href="/deconnexion" class="hidden lg:block text-sm text-red-500 hover:underline">
                    Déconnexion
                </a>
                <?php else: ?>
                <a href="/connexion" class="hidden lg:flex items-center gap-1 px-3 py-1.5 bg-accent text-white rounded-full text-sm">
                    <i class='bx bx-log-in'></i> Se connecter
                </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main class="pt-16 sm:pt-20">