<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container mx-auto p-6 pb-10">
    <a href="index.php?action=delivery/dashboard" class="text-amber-900 hover:underline mb-4 inline-block">← Retour au tableau de bord</a>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-amber-200 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-4 text-amber-950">Comment fonctionne le système de livraison ?</h1>
        <p class="text-amber-700 mb-6">Notre système de livraison est conçu pour offrir rapidité, transparence et suivi en temps réel à chaque étape de votre commande.</p>

        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold mb-2 text-amber-700">1. Validation de la commande</h2>
                <p class="text-amber-700">Après avoir passé votre commande, elle est enregistrée et préparée par l'éleveur. Vous recevez ensuite une confirmation et un suivi.</p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold mb-2 text-amber-700">2. Assignation du livreur</h2>
                <p class="text-amber-700">Un livreur disponible est automatiquement assigné à votre commande.</p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold mb-2 text-amber-700">3. Suivi de la livraison</h2>
                <p class="text-amber-700">Une fois acceptée, la commande passe en statut « en cours de livraison ». Vous pouvez consulter l'état de votre livraison depuis votre espace client.</p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold mb-2 text-amber-700">4. Livraison et réception</h2>
                <p class="text-amber-700">Le livreur vous livre à l'adresse indiquée. Si la livraison échoue, vous serez informé et le livreur devra préciser la raison de l'échec.</p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold mb-2 text-amber-700">5. Service client</h2>
                <p class="text-amber-700">En cas de problème ou de question, vous pouvez contacter notre support pour un accompagnement rapide.</p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>