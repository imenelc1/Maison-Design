<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
?>
<?php $pageTitle = 'Inscription — Maison Design'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-background">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-lg p-8">
            <h2 class="text-3xl font-frunchy text-textColor mb-8 text-center">Inscription</h2>

            <form method="POST" action="/inscription" class="space-y-4">
                 <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-textColor mb-1">Nom *</label>
                        <input type="text" name="nom" required 
                        value="<?php echo htmlspecialchars($old['nom'] ?? ''); ?>"
                            class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-textColor mb-1">Prénom *</label>
                        <input type="text" name="prenom" required
                        value="<?php echo htmlspecialchars($old['prenom'] ?? ''); ?>"
                            class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                </div>

                <div>
                    <label class="block text-textColor mb-1">Email *</label>
                    <input type="email" name="email" required
                    value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <div>
                    <label class="block text-textColor mb-1">Téléphone *</label>
                    <input type="tel" name="numtel" required placeholder="05XXXXXXXX"
                    value="<?php echo htmlspecialchars($old['numtel'] ?? ''); ?>"
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <div>
                    <label class="block text-textColor mb-1">Adresse *</label>
                    <input type="text" name="adresse" required
                     value="<?php echo htmlspecialchars($old['adresse'] ?? ''); ?>"
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

    <div>
    <label class="block text-textColor mb-1">Mot de passe *</label>
    <div class="relative">
        <input type="password" name="password" required minlength="6"
               id="password"
               class="w-full px-4 py-2 pr-12 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
        <button type="button" onclick="togglePassword('password', 'eye1')"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-accent">
            <i id="eye1" class='bx bx-hide text-xl'></i>
        </button>
    </div>
</div>

<div>
    <label class="block text-textColor mb-1">Confirmer *</label>
    <div class="relative">
        <input type="password" name="confirm-password" required
               id="confirm-password"
               class="w-full px-4 py-2 pr-12 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
        <button type="button" onclick="togglePassword('confirm-password', 'eye2')"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-accent">
            <i id="eye2" class='bx bx-hide text-xl'></i>
        </button>
    </div>
</div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="terms" required id="terms">
                    <label for="terms" class="text-sm">J'accepte les conditions</label>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                    S'inscrire
                </button>

                <p class="text-center text-sm">
                    Déjà un compte ?
                    <a href="/connexion" class="text-accent hover:underline">Se connecter</a>
                </p>
            </form>
        </div>
    </div>
    <script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bx-hide', 'bx-show');
    } else {
        input.type = 'password';
        icon.classList.replace('bx-show', 'bx-hide');
    }
}
</script>
</div>