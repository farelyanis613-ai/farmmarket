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

  const statusMap = {
    pending: { label: 'Nouvelle', class: 'bg-blue-100 text-blue-800' },
    in_progress: { label: 'En préparation', class: 'bg-yellow-100 text-yellow-800' },
    accepted: { label: 'Prête', class: 'bg-orange-100 text-orange-800' },
    delivered: { label: 'Livrée', class: 'bg-green-100 text-green-800' },
    failed: { label: 'Échouée', class: 'bg-red-100 text-red-800' },
    rejected: { label: 'Rejetée', class: 'bg-red-100 text-red-800' },
  };

  const render = () => {
    container.innerHTML = '';

    const wrapper = document.createElement('div');
    wrapper.className = 'space-y-6';

    const orderList = document.createElement('div');
    orderList.className = 'space-y-3';

    orders.forEach((order) => {
      const status = statusMap[normalizeStatus(order.status)] || { label: order.status, class: 'bg-slate-100 text-slate-800' };
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `w-full text-left rounded-3xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300 transition ${selectedOrderId === order.id ? 'ring-2 ring-emerald-500' : ''}`;
      button.innerHTML = `
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="text-sm text-slate-500">Commande #${order.id} • ${new Date(order.created_at).toLocaleDateString('fr-FR')}</div>
            <div class="text-lg font-semibold text-slate-900">${escapeHtml(order.customer_name)}</div>
            <div class="text-sm text-slate-600">${escapeHtml(order.customer_phone || 'Téléphone non renseigné')}</div>
          </div>
          <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${status.class}">${escapeHtml(status.label)}</span>
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

    const selectedOrder = orders.find((item) => item.id === selectedOrderId);
    if (selectedOrder) {
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
            <div><span class="font-semibold">Statut :</span> ${escapeHtml(status.label)}</div>
            <div><span class="font-semibold">Total :</span> ${new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(selectedOrder.total_price)}</div>
          </div>
          <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 overflow-hidden bg-slate-100 h-72" id="orderMapContainer"></div>
            <div class="grid gap-3 md:grid-cols-2">
              <button type="button" id="assignDeliveryBtn" class="inline-flex items-center justify-center rounded-3xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition">Assigner un livreur</button>
              <button type="button" id="openMapsBtn" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 transition">Ouvrir Google Maps</button>
              <button type="button" id="openWazeBtn" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 transition">Ouvrir Waze</button>
            </div>
            <div class="rounded-3xl bg-amber-50 p-4 text-amber-900 text-sm">
              <p><strong>Commande sélectionnée :</strong> #{selectedOrder.id}</p>
              <p><strong>Livreur :</strong> ${selectedOrder.delivery_person_name ? escapeHtml(selectedOrder.delivery_person_name) + ' (' + escapeHtml(selectedOrder.delivery_person_phone || 'Téléphone non renseigné') + ')' : 'Non assigné'}</p>
            </div>
          </div>
        </div>
      `;
      wrapper.appendChild(details);
      renderMap(selectedOrder);

      document.body.querySelector('#assignDeliveryBtn')?.addEventListener('click', () => assignDelivery(selectedOrder));
      document.body.querySelector('#openMapsBtn')?.addEventListener('click', () => openMaps(selectedOrder));
      document.body.querySelector('#openWazeBtn')?.addEventListener('click', () => openWaze(selectedOrder));
    }

    container.appendChild(wrapper);
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
    return String(unsafe || '').replace(/[&<"'`=\/]/g, function (s) {
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

  const getGoogleMapsApiKey = () => {
    const meta = document.querySelector('meta[name="google-maps-api-key"]');
    return meta ? meta.content.trim() : '';
  };

  const loadGoogleMaps = () => {
    const apiKey = getGoogleMapsApiKey();
    if (!apiKey) {
      return Promise.reject(new Error('Google Maps API key introuvable.'));
    }

    if (window.google && window.google.maps) {
      return Promise.resolve(window.google.maps);
    }

    if (window._farmerOrdersDashboardGoogleMapsPromise) {
      return window._farmerOrdersDashboardGoogleMapsPromise;
    }

    window._farmerOrdersDashboardGoogleMapsPromise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places`;
      script.async = true;
      script.defer = true;
      script.onload = function () {
        if (window.google && window.google.maps) {
          resolve(window.google.maps);
        } else {
          reject(new Error('Google Maps n\'a pas pu être initialisé.'));
        }
      };
      script.onerror = function () {
        reject(new Error('Impossible de charger l\'API Google Maps.'));
      };
      document.head.appendChild(script);
    });

    return window._farmerOrdersDashboardGoogleMapsPromise;
  };

  const renderMap = async (order) => {
    const containerMap = document.getElementById('orderMapContainer');
    if (!containerMap || order.latitude == null || order.longitude == null || order.latitude === '' || order.longitude === '') return;

    try {
      const maps = await loadGoogleMaps();
      const position = { lat: Number(order.latitude), lng: Number(order.longitude) };
      const map = new maps.Map(containerMap, {
        center: position,
        zoom: 13,
      });

      new maps.Marker({
        position,
        map,
        title: `Client: ${order.customer_name}`,
      });

      new maps.Marker({
        position: { lat: 6.3570077, lng: 2.3790333 },
        map,
        label: 'B',
        title: 'Boutique FarmMarket',
      });
    } catch (error) {
      console.error(error);
    }
  };

  const assignDelivery = async (order) => {
    const deliveryId = prompt('Entrez l’ID du livreur à assigner (par exemple 3):');
    if (!deliveryId) return;

    const payload = new FormData();
    payload.append('order_id', order.id);
    payload.append('delivery_id', deliveryId);

    const response = await fetch('index.php?action=farmer/assign-delivery-api', {
      method: 'POST',
      body: payload,
    });

    const result = await response.json();
    if (result.success) {
      alert('Livreur assigné avec succès. Statut mis à jour.');
      window.location.reload();
    } else {
      alert('Erreur : ' + (result.message || 'Impossible d’assigner.'));
    }
  };

  const openMaps = (order) => {
    if (order.latitude == null || order.longitude == null || order.latitude === '' || order.longitude === '') {
      alert('Coordonnées GPS manquantes pour cette commande.');
      return;
    }

    const lat = Number(order.latitude);
    const lng = Number(order.longitude);
    if (Number.isNaN(lat) || Number.isNaN(lng)) {
      alert('Coordonnées GPS invalides pour cette commande.');
      return;
    }

    const destination = encodeURIComponent(`${lat},${lng}`);
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${destination}`, '_blank');
  };

  const openWaze = (order) => {
    if (order.latitude == null || order.longitude == null || order.latitude === '' || order.longitude === '') {
      alert('Coordonnées GPS manquantes pour cette commande.');
      return;
    }

    const lat = Number(order.latitude);
    const lng = Number(order.longitude);
    if (Number.isNaN(lat) || Number.isNaN(lng)) {
      alert('Coordonnées GPS invalides pour cette commande.');
      return;
    }

    const destination = encodeURIComponent(`${lat},${lng}`);
    window.open(`https://waze.com/ul?ll=${destination}&navigate=yes`, '_blank');
  };

  render();
});
