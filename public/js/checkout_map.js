/**
 * checkout_map.js
 * Système GPS complet — Leaflet + OpenStreetMap + Nominatim
 * Plateforme avicole & cunicole
 *
 * Fonctions :
 *  - Carte centrée sur Cotonou au chargement
 *  - Bouton "Me localiser" (GPS navigateur)
 *  - Géocodage de l'adresse saisie (Nominatim)
 *  - Marqueur déplaçable (drag) → met à jour le champ adresse
 *  - Coordonnées lat/lng enregistrées dans des inputs cachés
 *
 * Usage : inclure après Leaflet CSS + JS, puis appeler :
 *   window.mountCheckoutAddressMap({ containerId, addressInputId, initialAddress })
 */

window.mountCheckoutAddressMap = function ({ containerId, addressInputId, initialAddress }) {

  /* ── Centre par défaut : Cotonou ─────────────────────────── */
  const DEFAULT_LAT = 6.3654;
  const DEFAULT_LNG = 2.4183;
  const DEFAULT_ZOOM = 14;

  const container = document.getElementById(containerId);
  const addrInput = document.getElementById(addressInputId);
  if (!container) return;

  /* ── Inputs cachés lat / lng (ajoutés dynamiquement) ──────── */
  let latInput = document.getElementById('gps_lat');
  let lngInput = document.getElementById('gps_lng');
  if (!latInput) {
    latInput = document.createElement('input');
    latInput.type = 'hidden'; latInput.name = 'gps_lat'; latInput.id = 'gps_lat';
    addrInput?.parentElement?.appendChild(latInput);
  }
  if (!lngInput) {
    lngInput = document.createElement('input');
    lngInput.type = 'hidden'; lngInput.name = 'gps_lng'; lngInput.id = 'gps_lng';
    addrInput?.parentElement?.appendChild(lngInput);
  }

  /* ── Init carte ───────────────────────────────────────────── */
  const map = L.map(containerId, { zoomControl: true }).setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map);

  /* ── Icône marqueur personnalisée ─────────────────────────── */
  const pinIcon = L.divIcon({
    className: '',
    html: `<div style="
      width:36px;height:42px;
      display:flex;flex-direction:column;
      align-items:center;
    ">
      <div style="
        width:36px;height:36px;border-radius:50% 50% 50% 0;
        transform:rotate(-45deg);
        background:#2D7A4F;
        border:3px solid #fff;
        box-shadow:0 2px 8px rgba(0,0,0,.35);
        display:flex;align-items:center;justify-content:center;
      ">
        <span style="transform:rotate(45deg);font-size:14px;">📍</span>
      </div>
      <div style="width:6px;height:6px;border-radius:50%;background:#2D7A4F;margin-top:0;"></div>
    </div>`,
    iconSize: [36, 42],
    iconAnchor: [18, 42],
    popupAnchor: [0, -44],
  });

  /* ── Marqueur central (draggable) ─────────────────────────── */
  const marker = L.marker([DEFAULT_LAT, DEFAULT_LNG], {
    icon: pinIcon,
    draggable: true,
  }).addTo(map);

  marker.bindPopup('<b>Votre position de livraison</b><br>Glissez pour ajuster.').openPopup();

  /* ── Mise à jour coords ───────────────────────────────────── */
  function updateCoords(lat, lng) {
    latInput.value = lat.toFixed(6);
    lngInput.value = lng.toFixed(6);
  }

  /* ── Géocodage inverse (coords → adresse) ─────────────────── */
  async function reverseGeocode(lat, lng) {
    try {
      const r = await fetch(
        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=fr`,
        { headers: { 'Accept-Language': 'fr' } }
      );
      const d = await r.json();
      if (d && d.display_name && addrInput) {
        const parts = [
          d.address?.road,
          d.address?.neighbourhood || d.address?.suburb,
          d.address?.city || d.address?.town || d.address?.village,
        ].filter(Boolean);
        addrInput.value = parts.length ? parts.join(', ') : d.display_name.split(',').slice(0,3).join(',');
      }
    } catch (e) { /* réseau indisponible */ }
  }

  /* ── Drag du marqueur ─────────────────────────────────────── */
  marker.on('dragend', function () {
    const { lat, lng } = marker.getLatLng();
    updateCoords(lat, lng);
    reverseGeocode(lat, lng);
  });

  /* ── Clic sur la carte ────────────────────────────────────── */
  map.on('click', function (e) {
    const { lat, lng } = e.latlng;
    marker.setLatLng([lat, lng]);
    updateCoords(lat, lng);
    reverseGeocode(lat, lng);
    marker.openPopup();
  });

  /* ── Géocodage direct (adresse → coords) ─────────────────── */
  let geocodeTimer = null;
  async function geocodeAddress(address) {
    if (!address || address.length < 5) return;
    try {
      showStatus('Recherche…', 'searching');
      const r = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address + ', Bénin')}&format=json&limit=1&accept-language=fr`,
        { headers: { 'Accept-Language': 'fr' } }
      );
      const results = await r.json();
      if (results.length > 0) {
        const { lat, lon } = results[0];
        const latlng = [parseFloat(lat), parseFloat(lon)];
        marker.setLatLng(latlng);
        map.flyTo(latlng, 16, { animate: true, duration: 1 });
        updateCoords(latlng[0], latlng[1]);
        showStatus('Position trouvée ✓', 'success');
      } else {
        showStatus('Adresse introuvable — cliquez sur la carte', 'warn');
      }
    } catch (e) {
      showStatus('Erreur réseau', 'error');
    }
  }

  if (addrInput) {
    addrInput.addEventListener('input', function () {
      clearTimeout(geocodeTimer);
      geocodeTimer = setTimeout(() => geocodeAddress(addrInput.value), 800);
    });
  }

  /* ── Bouton GPS : localisation navigateur ─────────────────── */
  const gpsBtn = document.createElement('button');
  gpsBtn.type = 'button';
  gpsBtn.id   = 'gpsLocateBtn';
  gpsBtn.innerHTML = `
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
      <circle cx="12" cy="12" r="8" opacity=".3"/>
    </svg>
    Me localiser
  `;
  gpsBtn.style.cssText = `
    display:inline-flex;align-items:center;gap:6px;
    padding:.55rem 1rem;border-radius:8px;
    border:1.5px solid #2D7A4F;background:#fff;
    color:#2D7A4F;font-size:.82rem;font-weight:600;
    cursor:pointer;transition:all .15s;margin-top:6px;
    box-shadow:0 1px 4px rgba(45,122,79,.15);
  `;
  gpsBtn.onmouseenter = () => { gpsBtn.style.background = '#E8F5EE'; };
  gpsBtn.onmouseleave = () => { gpsBtn.style.background = '#fff'; };

  /* Barre de statut */
  const statusBar = document.createElement('div');
  statusBar.id = 'gpsStatus';
  statusBar.style.cssText = `
    font-size:.75rem;padding:.35rem .75rem;border-radius:6px;
    margin-top:6px;display:none;font-weight:500;
  `;

  function showStatus(msg, type) {
    const styles = {
      searching: { bg:'#EFF6FF', color:'#1D4ED8', border:'#BFDBFE' },
      success:   { bg:'#F0FDF4', color:'#166534', border:'#BBF7D0' },
      warn:      { bg:'#FFFBEB', color:'#92400E', border:'#FDE68A' },
      error:     { bg:'#FEF2F2', color:'#991B1B', border:'#FECACA' },
    };
    const s = styles[type] || styles.warn;
    statusBar.style.background   = s.bg;
    statusBar.style.color        = s.color;
    statusBar.style.border       = `1px solid ${s.border}`;
    statusBar.style.display      = 'block';
    statusBar.textContent        = msg;
    if (type === 'success') setTimeout(() => { statusBar.style.display = 'none'; }, 3000);
  }

  gpsBtn.addEventListener('click', function () {
    if (!navigator.geolocation) {
      showStatus('GPS non supporté par votre navigateur', 'error'); return;
    }
    gpsBtn.disabled = true;
    gpsBtn.innerHTML = `<span style="animation:spin .8s linear infinite;display:inline-block">⏳</span> Localisation…`;
    showStatus('Récupération de votre position…', 'searching');

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        marker.setLatLng([lat, lng]);
        map.flyTo([lat, lng], 17, { animate: true, duration: 1.2 });
        updateCoords(lat, lng);
        reverseGeocode(lat, lng);
        showStatus('📍 Position GPS détectée avec succès !', 'success');
        gpsBtn.disabled = false;
        gpsBtn.innerHTML = `✓ Position trouvée`;
        setTimeout(() => {
          gpsBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="8" opacity=".3"/></svg> Me localiser`;
        }, 3000);
      },
      function (err) {
        const msgs = {
          1: 'Permission GPS refusée. Autorisez l\'accès dans votre navigateur.',
          2: 'Position indisponible. Vérifiez votre GPS.',
          3: 'Délai dépassé. Réessayez.',
        };
        showStatus(msgs[err.code] || 'Erreur GPS', 'error');
        gpsBtn.disabled = false;
        gpsBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="8" opacity=".3"/></svg> Me localiser`;
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  });

  /* Injection dans le DOM, sous la carte */
  container.insertAdjacentElement('afterend', gpsBtn);
  gpsBtn.insertAdjacentElement('afterend', statusBar);

  /* Animation spinner */
  if (!document.getElementById('gpsSpinStyle')) {
    const style = document.createElement('style');
    style.id = 'gpsSpinStyle';
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
  }

  /* ── Géocodage de l'adresse initiale ──────────────────────── */
  if (initialAddress && initialAddress.length > 4) {
    setTimeout(() => geocodeAddress(initialAddress), 600);
  } else {
    updateCoords(DEFAULT_LAT, DEFAULT_LNG);
  }

  /* ── Expose l'instance map pour usage externe ─────────────── */
  window._checkoutMap = map;
};