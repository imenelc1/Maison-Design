<?php $pageTitle = 'Inscription — Maison Design'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-background">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-lg p-8">
            <h2 class="text-3xl font-frunchy text-textColor mb-8 text-center">Inscription</h2>

            <form method="POST" action="/inscription" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-textColor mb-1">Nom *</label>
                        <input type="text" name="nom" required
                            class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-textColor mb-1">Prénom *</label>
                        <input type="text" name="prenom" required
                            class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                </div>

                <div>
                    <label class="block text-textColor mb-1">Email *</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <div>
                    <label class="block text-textColor mb-1">Téléphone *</label>
                    <input type="tel" name="numtel" required placeholder="05XXXXXXXX"
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <div>
                    <label class="block text-textColor mb-1">Adresse *</label>
                    <input type="text" name="adresse" required
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <div>
                    <label class="block text-textColor mb-1">Mot de passe *</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <div>
                    <label class="block text-textColor mb-1">Confirmer *</label>
                    <input type="password" name="confirm-password" required
                        class="w-full px-4 py-2 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
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
</div>