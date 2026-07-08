<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="space-y-5 pb-10 max-w-2xl mx-auto">

  <!-- Hero : confirmation -->
  <div class="relative overflow-hidden rounded-xl bg-white shadow-sm border border-slate-100">
    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-500 rounded-t-xl"></div>
    <div class="p-6 pt-7">
      <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
          <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Commande enregistrée</p>
          <h1 class="text-xl font-semibold text-slate-900">Commande validée avec succès</h1>
          <p class="text-sm text-slate-500 mt-1">Votre commande a été enregistrée. Un livreur vous sera assigné rapidement.</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 mt-6">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Numéro de commande</p>
          <p class="text-2xl font-mono font-semibold text-emerald-700">#<?= htmlspecialchars((string)($orderNumber ?? $orderId)) ?></p>
        </div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Statut</p>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-amber-50 text-amber-800 text-xs font-medium border border-amber-200">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <?= htmlspecialchars(formatStatusLabel($order['status'])) ?>
          </span>
        </div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Date</p>
          <p class="text-sm font-medium text-slate-700"><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
        </div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Paiement</p>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-emerald-50 text-emerald-800 text-xs font-medium border border-emerald-200">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Confirmé
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Timeline de suivi -->
  <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-6">
    <h2 class="text-sm font-semibold text-slate-900 mb-5">Suivi de commande</h2>
    <?php
      $steps = [
        ['label' => 'Commande reçue',       'sub' => 'Votre commande a été enregistrée',     'state' => 'done',    'time' => date('d/m/Y à H:i', strtotime($order['created_at']))],
        ['label' => 'Assignation livreur',  'sub' => 'Recherche d\'un livreur disponible…',   'state' => 'active',  'time' => ''],
        ['label' => 'En cours de livraison','sub' => 'Le livreur est en route',               'state' => 'pending', 'time' => ''],
        ['label' => 'Livré',                'sub' => 'Commande déposée à votre adresse',     'state' => 'pending', 'time' => ''],
      ];
      $stateClasses = [
        'done'    => 'bg-emerald-50 border-emerald-500 text-emerald-600',
        'active'  => 'bg-amber-50 border-amber-500 text-amber-600',
        'pending' => 'bg-slate-50 border-slate-300 text-slate-400',
      ];
      $lineClasses = ['done' => 'bg-emerald-200', 'active' => 'bg-slate-200', 'pending' => 'bg-slate-100'];
      foreach ($steps as $i => $step):
        $isLast = $i === count($steps) - 1;
    ?>
    <div class="flex gap-3 <?= $isLast ? '' : 'pb-5' ?>">
      <div class="flex flex-col items-center" style="width:20px">
        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 <?= $stateClasses[$step['state']] ?>">
          <?php if ($step['state'] === 'done'): ?>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <?php elseif ($step['state'] === 'active'): ?>
            <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
          <?php endif; ?>
        </div>
        <?php if (!$isLast): ?>
          <div class="flex-1 w-0.5 mt-1 <?= $lineClasses[$step['state']] ?>"></div>
        <?php endif; ?>
      </div>
      <div class="pt-0.5 pb-1">
        <p class="text-sm font-medium text-slate-900"><?= $step['label'] ?></p>
        <p class="text-xs text-slate-500 mt-0.5"><?= $step['sub'] ?></p>
        <?php if ($step['time']): ?>
          <p class="text-xs text-slate-400 mt-1 font-mono"><?= $step['time'] ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Infos client -->
  <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-6">
    <h2 class="text-sm font-semibold text-slate-900 mb-4">Vos informations</h2>
    <div class="grid grid-cols-2 gap-4">
      <?php foreach ([
        ['Nom',                 $order['customer_name']],
        ['Email',               $order['customer_email']],
        ['Téléphone',           $order['phone'] ?? 'N/A'],
        ['Adresse de livraison',$order['address'] ?? 'N/A'],
      ] as [$lbl, $val]): ?>
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1"><?= $lbl ?></p>
        <p class="text-sm font-medium text-slate-800"><?= htmlspecialchars($val) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Livraison + livreur -->
  <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-6">
    <h2 class="text-sm font-semibold text-slate-900 mb-4">Mode de livraison</h2>
    <div class="flex flex-col gap-3">
      <div class="rounded-lg p-4 border-l-[3px] border-emerald-500 border border-slate-100 bg-slate-50">
        <p class="text-sm font-medium text-slate-900">
          <?= $order['delivery_type'] === 'home' ? '🚚 Livraison à domicile' : '🏪 Retrait à la boutique' ?>
        </p>
        <?php if ($order['delivery_type'] === 'home'): ?>
          <p class="text-xs text-slate-500 mt-1">Livraison directement à votre adresse</p>
          <p class="text-sm font-semibold text-emerald-700 mt-2"><?= number_format($order['delivery_fee'], 0, '', ' ') ?> FCFA</p>
        <?php else: ?>
          <p class="text-xs text-slate-500 mt-1">Retrait gratuit à notre point de vente</p>
        <?php endif; ?>
      </div>

      <?php if ($order['delivery_person_id']): ?>
      <div class="rounded-lg p-4 border border-slate-100 bg-slate-50 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-sm font-semibold text-emerald-700 flex-shrink-0">
          <?= strtoupper(substr($order['delivery_person_name'], 0, 2)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($order['delivery_person_name']) ?></p>
          <?php if ($order['delivery_person_phone']): ?>
            <p class="text-xs text-slate-500"><?= htmlspecialchars($order['delivery_person_phone']) ?></p>
          <?php endif; ?>
          <?php if ($order['delivery_time']): ?>
            <p class="text-xs text-slate-400 mt-1 font-mono">Prévu : <?= date('d/m/Y à H:i', strtotime($order['delivery_time'])) ?></p>
          <?php endif; ?>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">Assigné</span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Articles commandés -->
  <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-6">
    <h2 class="text-sm font-semibold text-slate-900 mb-4">Détails de la commande</h2>
    <div class="divide-y divide-slate-100">
      <?php foreach ($orderItems as $item): ?>
      <div class="flex justify-between items-start py-3">
        <div>
          <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($item['product_name']) ?></p>
          <p class="text-xs text-slate-500 mt-0.5">
            <?= $item['quantity'] ?> × <?= number_format($item['price'], 0, '', ' ') ?> FCFA
          </p>
          <?php if ($item['farmer_name']): ?>
            <p class="text-xs text-emerald-600 mt-1">🌿 <?= htmlspecialchars($item['farmer_name']) ?></p>
          <?php endif; ?>
        </div>
        <p class="text-sm font-semibold text-emerald-700">
          <?= number_format($item['quantity'] * $item['price'], 0, '', ' ') ?> FCFA
        </p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Total -->
    <div class="mt-4 rounded-lg bg-slate-50 p-4 space-y-2">
      <div class="flex justify-between text-sm text-slate-600">
        <span>Sous-total</span>
        <span><?= number_format($order['total_price'] - $order['delivery_fee'], 0, '', ' ') ?> FCFA</span>
      </div>
      <?php if ($order['delivery_fee'] > 0): ?>
      <div class="flex justify-between text-sm text-slate-600">
        <span>Frais de livraison</span>
        <span><?= number_format($order['delivery_fee'], 0, '', ' ') ?> FCFA</span>
      </div>
      <?php endif; ?>
      <div class="flex justify-between items-center border-t border-slate-200 pt-2 mt-2">
        <span class="text-sm font-semibold text-slate-900">Total payé</span>
        <span class="text-xl font-semibold text-emerald-700"><?= number_format($order['total_price'], 0, '', ' ') ?> FCFA</span>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-6">
    <div class="flex flex-col sm:flex-row gap-3">
      <a href="index.php?action=orders"
         class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Voir mes commandes
      </a>
      <a href="index.php?action=products"
         class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Continuer les achats
      </a>
    </div>
  </div>

</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>