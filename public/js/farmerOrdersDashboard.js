document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('farmerOrdersApp');
  const dataNode = document.getElementById('farmerOrdersData');
  if (!container || !dataNode) return;

  let data = {};
  try {
    data = JSON.parse(dataNode.textContent || '{}');
  } catch (e) {
    console.error('Impossible de parser les données des commandes.');
    return;
  }

  const orders = data.orders || [];
  const deliveries = data.deliveries || [];
  let selectedOrderId = orders.length ? orders[0].id : null;

  // Position de référence de la boutique (second repère affiché sur la carte)
  const SHOP_LAT = 6.3570077;
  const SHOP_LNG = 2.3790333;

  const statusMap = {
    pending: { label: 'Nouvelle', class: 'bg-blue-100 text-blue-800' },
    in_progress: { label: 'En préparation', class: 'bg-yellow-100 text-yellow-800' },
    accepted: { label: 'Prête', class: 'bg-orange-100 text-orange-800' },
    delivered: { label: 'Livrée', class: 'bg-green-100 text-green-800' },
    failed: { label: 'Échouée', class: 'bg-red-100 text-red-800' },
    rejected: { label: 'Rejetée', class: 'bg-red-100 text-red-800' },
  };

  const getStatus = (order) => statusMap[normalizeStatus(order.status)] || { label: order.status, class: 'bg-slate-100 text-slate-800' };

  const hasValidCoords = (order) => {
    if (order.latitude == null || order.longitude == null || order.latitude === '' || order.longitude === '') return false;
    const lat = Number(order.latitude);
    const lng = Number(order.longitude);
    return !Number.isNaN(lat) && !Number.isNaN(lng);
  };

  const render = () => {
    container.innerHTML = '';

    const wrapper = document.createElement('div');
    wrapper.className = 'space-y-6';

    const orderList = document.createElement('div');
    orderList.className = 'space-y-3';

    orders.forEach((order) => {
      const orderStatus = getStatus(order);
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `w-full text-left rounded-3xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300 transition ${selectedOrderId === order.id ? 'ring-2 ring-emerald-500' : ''}`;
      button.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="text-sm text-slate-500">Commande #${escapeHtml(order.id)} • ${new Date(order.created_at).toLocaleDateString('fr-FR')}</div>
            <div class="text-lg font-semibold text-slate-900">${escapeHtml(order.customer_name)}</div>
            <div class="text-sm text-slate-600">${escapeHtml(order.customer_phone || 'Téléphone non renseigné')}</div>
          </div>
          <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${orderStatus.class}">${escapeHtml(orderStatus.label)}</span>
        </div>
        <div class="mt-3 text-sm text-slate-600">
          ${escapeHtml(order.customer_address || 'Adresse non renseignée')}
        </div>
      `;
      button.addEventListener('click', () => {
        selectedOrderId = order.id;
        render();
      });
      orderList.appendChild(button);
    });

    wrapper.appendChild(orderList);
    container.appendChild(wrapper);

    const selectedOrder = orders.find((item) => item.id === selectedOrderId);
    if (selectedOrder) {
      const selectedStatus = getStatus(selectedOrder);

      const details = document.createElement('div');
      details.className = 'rounded-3xl border border-slate-200 bg-white p-5 shadow-sm';
      details.innerHTML = `
        <h3 class="text-xl font-semibold text-slate-900 mb-4">Détails de la commande</h3>
        <div class="grid gap-4 lg:grid-cols-2">
          <div class="space-y-3 text-sm text-slate-700">
            <div><span class="font-semibold">Client :</span> ${escapeHtml(selectedOrder.customer_name)}</div>
            <div><span class="font-semibold">Téléphone :</span> ${escapeHtml(selectedOrder.customer_phone || 'Non renseigné')}</div>
            <div><span class="font-semibold">Adresse :</span><br>${escapeHtml(selectedOrder.customer_address || 'Non renseignée').replace(/\n/g, '<br>')}</div>
            <div><span class="font-semibold">Latitude :</span> ${escapeHtml(selectedOrder.latitude || 'N/A')}</div>
            <div><span class="font-semibold">Longitude :</span> ${escapeHtml(selectedOrder.longitude || 'N/A')}</div>
            <div><span class="font-semibold">Statut :</span> ${escapeHtml(selectedStatus.label)}</div>
            <div><span class="font-semibold">Total :</span> ${new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(selectedOrder.total_price)}</div>
          </div>
          <div class="space-y-2">
            <div class="rounded-3xl border border-slate-200 overflow-hidden bg-slate-100 h-72" id="orderMapContainer"></div>
            <p class="text-xs text-slate-400" id="orderMapNote"></p>
            <div class="grid gap-3 md:grid-cols-2 pt-2">
              <button type="button" id="assignDeliveryBtn" class="inline-flex items-center justify-center rounded-3xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition">Assigner un livreur</button>
              <button type="button" id="openMapsBtn" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 transition">Ouvrir Google Maps</button>
            </div>
            <div class="rounded-3xl bg-amber-50 p-4 text-amber-900 text-sm">
              <p><strong>Commande sélectionnée :</strong> #${escapeHtml(selectedOrder.id)}</p>
              <p><strong>Livreur :</strong> ${selectedOrder.delivery_person_name ? escapeHtml(selectedOrder.delivery_person_name) + ' (' + escapeHtml(selectedOrder.delivery_person_phone || 'Téléphone non renseigné') + ')' : 'Non assigné'}</p>
            </div>
          </div>
        </div>
      `;
      wrapper.appendChild(details);
      requestAnimationFrame(() => {
        renderMap(selectedOrder);
      });

      details.querySelector('#assignDeliveryBtn')?.addEventListener('click', () => assignDelivery(selectedOrder));
      details.querySelector('#openMapsBtn')?.addEventListener('click', () => openMaps(selectedOrder));
    }

  };

  const normalizeStatus = (status) => {
    const cleaned = (status || '').toLowerCase().replace(/[^a-z_]/g, '');
    if (cleaned.includes('pending') || cleaned.includes('nouvelle') || cleaned.includes('enattente')) return 'pending';
    if (cleaned.includes('inprogress') || cleaned.includes('preparation') || cleaned.includes('enpreparation')) return 'in_progress';
    if (cleaned.includes('accepted') || cleaned.includes('prete') || cleaned.includes('ready')) return 'accepted';
    if (cleaned.includes('delivered') || cleaned.includes('livree') || cleaned.includes('livrée')) return 'delivered';
    return cleaned || 'pending';
  };

  const escapeHtml = (unsafe) => {
    return String(unsafe || '').replace(/[&<>"'`=\/]/g, function (s) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
      })[s];
    });
  };

  /**
   * Convertit une adresse texte en coordonnées via Nominatim (OpenStreetMap),
   * utilisé en secours quand la commande n'a pas de latitude/longitude enregistrée.
   */
  const geocodeAddress = async (address) => {
    try {
      const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&accept-language=fr`;
      const res = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
      if (!res.ok) return null;
      const results = await res.json();
      if (!results.length) return null;
      return { lat: parseFloat(results[0].lat), lng: parseFloat(results[0].lon) };
    } catch (e) {
      return null;
    }
  };

  /**
   * Affiche la mini-carte du panneau de détail avec Leaflet/OpenStreetMap.
   * Si la commande n'a pas de GPS enregistré, géocode son adresse texte
   * pour afficher une position approximative plutôt que de laisser la carte vide.
   */
  const renderMap = async (order) => {
    const containerMap = document.getElementById('orderMapContainer');
    const note = document.getElementById('orderMapNote');
    if (!containerMap || typeof L === 'undefined') return;

    let lat = null;
    let lng = null;
    let approximate = false;

    if (hasValidCoords(order)) {
      lat = Number(order.latitude);
      lng = Number(order.longitude);
    } else if (order.customer_address) {
      containerMap.innerHTML = `<div class="w-full h-full flex items-center justify-center text-center text-slate-400 text-sm px-4">Localisation de l'adresse…</div>`;
      const geo = await geocodeAddress(order.customer_address);

      // Si l'utilisateur a cliqué sur une autre commande pendant la recherche,
      // ce conteneur n'est plus affiché : on abandonne pour éviter d'écrire au mauvais endroit.
      if (!containerMap.isConnected) return;

      if (!geo) {
        containerMap.innerHTML = `<div class="w-full h-full flex items-center justify-center text-center text-slate-400 text-sm px-4">Adresse introuvable sur la carte : « ${escapeHtml(order.customer_address)} ».</div>`;
        return;
      }
      lat = geo.lat;
      lng = geo.lng;
      approximate = true;
    } else {
      containerMap.innerHTML = `<div class="w-full h-full flex items-center justify-center text-center text-slate-400 text-sm px-4">Aucune adresse ni coordonnées GPS pour cette commande.</div>`;
      return;
    }

    containerMap.innerHTML = '';
    const map = L.map(containerMap, { zoomControl: true }).setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const makeIcon = (color) => L.divIcon({
      className: '',
      html: `<div style="width:16px;height:16px;border-radius:50%;background:${color};border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>`,
      iconSize: [16, 16],
      iconAnchor: [8, 8],
      popupAnchor: [0, -10]
    });

    const clientPopup = approximate
      ? `Client : ${escapeHtml(order.customer_name)}<br><span style="color:#b45309">Position approximative (basée sur l'adresse)</span>`
      : `Client : ${escapeHtml(order.customer_name)}`;

    const clientMarker = L.marker([lat, lng], { icon: makeIcon(approximate ? '#f59e0b' : '#10b981') })
      .addTo(map)
      .bindPopup(clientPopup);

    const shopMarker = L.marker([SHOP_LAT, SHOP_LNG], { icon: makeIcon('#3b82f6') })
      .addTo(map)
      .bindPopup('Boutique FarmMarket');

    // Centrer la carte sur l'adresse client plutôt que de zoomer sur le magasin.
    map.setView([lat, lng], 15, { animate: true, duration: 0.5 });

    if (approximate) {
      clientMarker.openPopup();
      if (note) note.textContent = 'Position estimée à partir de l\'adresse (pas de GPS enregistré pour cette commande).';
    } else if (note) {
      note.textContent = '';
    }

    // Leaflet a besoin d'un recalcul de taille quand le conteneur vient d'être inséré dans le DOM.
    setTimeout(() => map.invalidateSize(), 150);
  };

  /**
   * Redirige vers la page de gestion des livreurs, avec la commande
   * pré-sélectionnée pour lui assigner un livreur depuis cette page.
   */
  const assignDelivery = (order) => {
    window.location.href = `index.php?action=farmer/deliveries&order_id=${encodeURIComponent(order.id)}`;
  };

  const buildGoogleMapsUrl = (order) => {
    if (hasValidCoords(order)) {
      const destination = `${Number(order.latitude)},${Number(order.longitude)}`;
      return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}`;
    }
    if (order.customer_address) {
      return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(order.customer_address)}`;
    }
    return null;
  };

  const buildWazeUrl = (order) => {
    if (hasValidCoords(order)) {
      const destination = `${Number(order.latitude)},${Number(order.longitude)}`;
      return `https://waze.com/ul?ll=${encodeURIComponent(destination)}&navigate=yes`;
    }
    if (order.customer_address) {
      return `https://waze.com/ul?q=${encodeURIComponent(order.customer_address)}&navigate=yes`;
    }
    return null;
  };

  const openMaps = (order) => {
    const url = buildGoogleMapsUrl(order);
    if (!url) {
      alert('Aucune adresse ni coordonnées GPS disponibles pour cette commande.');
      return;
    }
    window.open(url, '_blank', 'noopener,noreferrer');
  };

  const openWaze = (order) => {
    const url = buildWazeUrl(order);
    if (!url) {
      alert('Aucune adresse ni coordonnées GPS disponibles pour cette commande.');
      return;
    }
    window.open(url, '_blank', 'noopener,noreferrer');
  };

  render();

  // Auto-refresh: poll server for changes and reload page when orders changed
  let localHash = null;
  setInterval(async () => {
    try {
      const res = await fetch('index.php?action=farmer/orders-poll', { cache: 'no-store' });
      if (!res.ok) return;
      const json = await res.json();
      if (!json.hash) return;
      if (localHash === null) {
        localHash = json.hash; // initialize from server
        return;
      }
      if (json.hash !== localHash) {
        setTimeout(() => window.location.reload(), 200);
      }
    } catch (e) {}
  }, 8000);
});