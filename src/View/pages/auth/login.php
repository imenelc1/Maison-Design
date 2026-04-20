<?php $pageTitle = 'Connexion — Maison Design'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-background">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-lg p-8">
            <h2 class="text-3xl font-frunchy text-textColor mb-8 text-center">Connexion</h2>

            <form method="POST" action="/connexion" class="space-y-6">
                <div>
                    <label class="block text-textColor mb-2">Email</label>
                    <div class="relative">
                        <i class='bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-accent text-xl'></i>
                        <input type="email" name="email" required
                            placeholder="Votre email"
                            class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                </div>

                <div>
                    <label class="block text-textColor mb-2">Mot de passe</label>
                    <div class="relative">
                        <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-accent text-xl'></i>
                        <input type="password" name="password" required
                            placeholder="Votre mot de passe"
                            class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                    Se connecter
                </button>

                <p class="text-center text-sm">
                    Pas de compte ?
                    <a href="/inscription" class="text-accent hover:underline">S'inscrire</a>
                </p>
            </form>
        </div>
    </div>
</div>