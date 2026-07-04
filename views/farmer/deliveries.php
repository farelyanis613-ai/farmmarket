<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container mx-auto p-6 space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Gestion des Livreurs</h1>
            <p class="text-gray-600 mt-2">Gérez vos livreurs et assignez-les aux commandes</p>
        </div>
        <a href="index.php?action=farmer/deliveries/add" class="bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 font-semibold">+ Ajouter un livreur</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Livreurs disponibles -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gradient-to-r from-orange-600 to-orange-500 text-white px-6 py-4">
            <h2 class="text-xl font-bold">Livreurs Disponibles</h2>
        </div>

        <?php if (empty($deliverers)): ?>
            <div class="px-6 py-8 text-center text-gray-600">
                <p>Aucun livreur enregistré. <a href="index.php?action=farmer/deliveries/add" class="text-emerald-600 hover:underline font-semibold">Ajouter un livreur</a></p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Téléphone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Adresse</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Commandes actives</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($deliverers as $driver): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($driver['name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($driver['email']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($driver['phone'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($driver['address'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <?= $driver['current_orders'] ?? 0 ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="index.php?action=farmer/deliveries/edit&id=<?= $driver['id'] ?>" class="inline-flex items-center px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-medium">
                                        Modifier
                                    </a>
                                    <a href="index.php?action=farmer/deliveries/delete&id=<?= $driver['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livreur ?')" class="inline-flex items-center px-3 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200 text-xs font-medium">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Assigner un livreur à une commande -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-500 text-white px-6 py-4">
            <h2 class="text-xl font-bold">Assigner un livreur à une commande</h2>
        </div>

        <?php if (empty($orders)): ?>
            <div class="px-6 py-8 text-center text-gray-600">
                <p>Aucune commande en attente de livraison</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Commande #</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Client</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Total</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Livreur assigné</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($order['delivery_person_name']): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                            ✓ <?= htmlspecialchars($order['delivery_person_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                            En attente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= getStatusBadgeClasses($order['status']) ?>">
                                        <?= htmlspecialchars(formatStatusLabel($order['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="index.php?action=farmer/assign-delivery&order_id=<?= $order['id'] ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700 text-xs font-semibold">
                                        Assigner
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
