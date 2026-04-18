// Script pour vérifier quel API est utilisé
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 DIAGNOSTIC - Vérification des appels API');
    
    // Intercepter tous les appels fetch
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        console.log('🔍 FETCH appelé avec:', args[0]);
        return originalFetch.apply(this, args);
    };
    
    // Vérifier si get-products.php est utilisé quelque part
    const scripts = document.querySelectorAll('script');
    scripts.forEach(script => {
        if (script.src && script.src.includes('get-products')) {
            console.log('⚠️ ATTENTION: Script get-products détecté:', script.src);
        }
    });
    
    // Vérifier les appels AJAX dans le code
    setTimeout(() => {
        console.log('🔍 Variables globales disponibles:');
        console.log('- window.sessionData:', window.sessionData);
        console.log('- window.userFavorites:', window.userFavorites);
        console.log('- window.initialData:', window.initialData);
        
        // Tester l'endpoint principal
        fetch('categories.php?ajax=1&category=all')
            .then(response => response.json())
            .then(data => {
                console.log('🔍 Test categories.php?ajax=1:', data);
                console.log('🔍 Nombre de produits reçus:', data.products ? data.products.length : 0);
                console.log('🔍 Favoris reçus:', data.userFavorites);
            })
            .catch(error => {
                console.error('❌ Erreur test categories.php:', error);
            });
            
        // Tester l'endpoint get-products.php si il existe
        fetch('get-products.php?category=all')
            .then(response => response.json())
            .then(data => {
                console.log('🔍 Test get-products.php:', data);
                console.log('🔍 Nombre de produits reçus:', data.products ? data.products.length : 0);
            })
            .catch(error => {
                console.log('ℹ️ get-products.php non accessible (normal si pas utilisé)');
            });
    }, 1000);
});