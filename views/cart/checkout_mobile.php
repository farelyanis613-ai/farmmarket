<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
    $operator        = trim($_GET['operator'] ?? 'Moov Money');
    $deliveryAddress = trim($_GET['address'] ?? $_SESSION['user']['address'] ?? '');

    $operators = [
        'Moov Money'   => ['color'=>'#0066CC','bg'=>'#E6F0FB','logo'=>'M'],
        'MTN mobile'   => ['color'=>'#F5A623','bg'=>'#FEF3DC','logo'=>'M'],
        'Celtiis Cash' => ['color'=>'#E01B24','bg'=>'#FDEAEA','logo'=>'C'],
    ];
?>

<!-- ═══════════════════════════════════════════════
     LEAFLET CSS — OpenStreetMap (gratuit, aucune clé)
════════════════════════════════════════════════ -->
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">

<div class="fm-pay-wrap pb-16">

    <!-- ── En-tête ──────────────────────────────── -->
    <div class="fm-pay-header">
        <a href="index.php?action=checkout" class="fm-back-link">← Retour</a>
        <div>
            <h1 class="fm-page-title">Paiement Mobile Money</h1>
            <p class="fm-page-sub">Complétez les informations ci-dessous pour valider votre commande.</p>
        </div>
    </div>

    <!-- ── Barre de progression ──────────────────── -->
    <div class="fm-checkout-steps" aria-label="Étapes du paiement">
        <div class="fm-cstep fm-cstep-done">
            <div class="fm-cstep-dot">✓</div>
            <span class="fm-cstep-label">Panier</span>
        </div>
        <div class="fm-cstep-line fm-cstep-line-done"></div>
        <div class="fm-cstep fm-cstep-done">
            <div class="fm-cstep-dot">✓</div>
            </div>
        <aside class="fm-pay-right">
            <div class="fm-recap-card">
                <h2 class="fm-recap-title">Récapitulatif</h2>

                <div class="fm-recap-rows">
                    <div class="fm-recap-row">
                        <span class="fm-recap-label">Sous-total</span>
                        <span class="fm-recap-val"><?= number_format(($total ?? 0) - ($deliveryFee ?? 0), 0, '', ' ') ?> FCFA</span>
                    </div>
                    <div class="fm-recap-row">
                        <span class="fm-recap-label">Frais de livraison</span>
                        <span class="fm-recap-val">
                            <?php if (($deliveryFee ?? 0) > 0) : ?>
                                <?= number_format($deliveryFee, 0, '', ' ') ?> FCFA
                            <?php else : ?>
                                <span class="fm-free">Gratuit</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="fm-recap-row fm-recap-total">
                        <span>Total à payer</span>
                        <span><?= number_format($total ?? 0, 0, '', ' ') ?> FCFA</span>
                    </div>
                </div>

                <!-- Opérateur -->
                <div class="fm-recap-op" id="recapOp">
                    <span class="fm-recap-op-icon" id="recapOpIcon"
                          style="background:<?= $operators[$operator]['color'] ?? '#16a34a' ?>">
                        <?= $operators[$operator]['logo'] ?? 'M' ?>
                    </span>
                    <div>
                        <p class="fm-recap-op-name" id="recapOpName"><?= htmlspecialchars($operator) ?></p>
                        <p class="fm-recap-op-sub">Paiement sécurisé</p>
                    </div>
                    <span class="fm-secure-icon">🔒</span>
                </div>

                <!-- Bloc GPS sidebar (caché jusqu'à localisation) -->
                <div id="gpsRecapBlock" class="fm-gps-recap" style="display:none;">
                    <div class="fm-gps-recap-head">
                        <span class="fm-gps-dot"></span>
                        Position GPS enregistrée
                    </div>
                    <div id="gpsRecapCoords" class="fm-gps-recap-coords">—</div>
                </div>

                <ul class="fm-guarantees">
                    <li>✅ Paiement 100% sécurisé</li>
                    <li>✅ Confirmation par SMS</li>
                    <li>✅ Facture téléchargeable</li>
                    <li>✅ Livraison GPS tracée</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<!-- Inline styles moved to public/css/style.css -->
<!-- views/cart/checkout_mobile.php: original <style> block consolidated into public/css/style.css -->

<!-- ═══════════════════════════════════════
     LEAFLET JS
════════════════════════════════════════ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLs="
        crossorigin=""></script>

<!-- ═══════════════════════════════════════
     GPS + CARTE + FORMULAIRE
════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ════════════════════════════════════════
       1. SÉLECTION OPÉRATEUR
    ════════════════════════════════════════ */
    var opData = {
        'Moov Money':   { color:'#0066CC', bg:'#E6F0FB', logo:'M' },
        'MTN mobile':   { color:'#F5A623', bg:'#FEF3DC', logo:'M' },
        'Celtiis Cash': { color:'#E01B24', bg:'#FDEAEA', logo:'C' },
    };
    document.querySelectorAll('.fm-op-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.fm-op-pill').forEach(function (p) { p.classList.remove('fm-op-active'); });
            pill.classList.add('fm-op-active');
            var op = pill.dataset.op;
            document.getElementById('operatorInput').value = op;
            document.getElementById('opNameHint').textContent = op;
            var d = opData[op] || {};
            var icon  = document.getElementById('recapOpIcon');
            var oname = document.getElementById('recapOpName');
            if (icon)  { icon.style.background = d.color || '#16a34a'; icon.textContent = d.logo || 'M'; }
            if (oname) { oname.textContent = op; }
        });
    });

    /* ════════════════════════════════════════
       2. TOGGLE EMAIL
    ════════════════════════════════════════ */
    var cb = document.getElementById('receiptEmailCheckbox');
    var emailContainer = document.getElementById('receiptEmailContainer');
    var emailInput = document.getElementById('receiptEmail');
    if (cb && emailContainer) {
        cb.addEventListener('change', function () {
            emailContainer.classList.toggle('open', cb.checked);
            if (emailInput) cb.checked ? emailInput.setAttribute('required','') : emailInput.removeAttribute('required');
        });
    }

    /* ════════════════════════════════════════
       3. CARTE GPS LEAFLET
    ════════════════════════════════════════ */
    var mapContainer = document.getElementById('checkoutAddressMap');
    if (!mapContainer) return; // Mode retrait en boutique : pas de carte

    // Références DOM
    var addrInput   = document.getElementById('deliveryAddress');
    var latInput    = document.getElementById('gpsLat');
    var lngInput    = document.getElementById('gpsLng');
    var statusBar   = document.getElementById('gpsStatusBar');
    var gpsBtn      = document.getElementById('gpsLocateBtn');
    var gpsBtnLabel = document.getElementById('gpsBtnLabel');
    var recapBlock  = document.getElementById('gpsRecapBlock');
    var recapCoords = document.getElementById('gpsRecapCoords');

    // Centre : Cotonou, Bénin
    var DEFAULT_LAT  = 6.3654;
    var DEFAULT_LNG  = 2.4183;
    var DEFAULT_ZOOM = 14;

    // ── Init carte ────────────────────────────
    var map = L.map('checkoutAddressMap').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // ── Icône marqueur personnalisé ───────────
    var pinIcon = L.divIcon({
        className: '',
        html: '<div style="width:32px;height:32px;border-radius:50%;background:#16a34a;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;font-size:14px;">📍</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -34],
    });

    // ── Marqueur (draggable) ──────────────────
    var marker = L.marker([DEFAULT_LAT, DEFAULT_LNG], { icon: pinIcon, draggable: true }).addTo(map);
    marker.bindPopup('<b>Point de livraison</b><br><small>Glissez pour ajuster</small>').openPopup();

    // ── Helpers ──────────────────────────────
    function setCoords(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        // Mettre à jour sidebar
        if (recapBlock) recapBlock.style.display = 'block';
        if (recapCoords) recapCoords.textContent = 'Lat : ' + lat.toFixed(5) + '   Lng : ' + lng.toFixed(5);
    }

    function showStatus(msg, type) {
        // type : 'searching' | 'success' | 'warning' | 'error'
        statusBar.className = 'fm-gps-status ' + type;
        statusBar.textContent = msg;
        statusBar.style.display = 'block';
        if (type === 'success') {
            setTimeout(function () { statusBar.style.display = 'none'; }, 3500);
        }
    }

    // ── Géocodage inverse (coords → adresse) ─
    function reverseGeocode(lat, lng) {
        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
            headers: { 'Accept-Language': 'fr' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d && d.address && addrInput) {
                var parts = [
                    d.address.road,
                    d.address.neighbourhood || d.address.suburb,
                    d.address.city || d.address.town || d.address.village,
                ].filter(Boolean);
                addrInput.value = parts.length ? parts.join(', ') : (d.display_name || '').split(',').slice(0, 3).join(', ');
            }
        })
        .catch(function () { /* silencieux si pas de réseau */ });
    }

    // ── Drag marqueur ─────────────────────────
    marker.on('dragend', function () {
        var ll = marker.getLatLng();
        setCoords(ll.lat, ll.lng);
        reverseGeocode(ll.lat, ll.lng);
        showStatus('📍 Position mise à jour', 'success');
    });

    // ── Clic sur la carte ─────────────────────
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        map.panTo(e.latlng);
        setCoords(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
        marker.openPopup();
        showStatus('📍 Position sélectionnée', 'success');
    });

    // ── Géocodage adresse saisie → carte ──────
    var geocodeTimer = null;
    function geocodeAddress(address) {
        if (!address || address.length < 4) return;
        showStatus('🔍 Recherche de l\'adresse…', 'searching');
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(address + ', Bénin') + '&format=json&limit=1', {
            headers: { 'Accept-Language': 'fr' }
        })
        .then(function (r) { return r.json(); })
        .then(function (results) {
            if (results.length > 0) {
                var lat = parseFloat(results[0].lat);
                var lng = parseFloat(results[0].lon);
                marker.setLatLng([lat, lng]);
                map.flyTo([lat, lng], 16, { animate: true, duration: 1 });
                setCoords(lat, lng);
                showStatus('✅ Adresse trouvée sur la carte', 'success');
            } else {
                showStatus('⚠️ Adresse introuvable — cliquez sur la carte pour placer le marqueur', 'warning');
            }
        })
        .catch(function () {
            showStatus('❌ Erreur réseau — vérifiez votre connexion', 'error');
        });
    }

    if (addrInput) {
        addrInput.addEventListener('input', function () {
            clearTimeout(geocodeTimer);
            geocodeTimer = setTimeout(function () { geocodeAddress(addrInput.value); }, 900);
        });
    }

    // ── Bouton GPS : géolocalisation navigateur ─
    if (gpsBtn) {
        gpsBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                showStatus('❌ GPS non supporté par votre navigateur', 'error');
                return;
            }
            gpsBtn.disabled = true;
            gpsBtnLabel.innerHTML = '<span class="spinning">⏳</span> Localisation en cours…';
            showStatus('📡 Récupération de votre position GPS…', 'searching');

            navigator.geolocation.getCurrentPosition(
                /* Succès */
                function (pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    marker.setLatLng([lat, lng]);
                    map.flyTo([lat, lng], 17, { animate: true, duration: 1.2 });
                    setCoords(lat, lng);
                    reverseGeocode(lat, lng);
                    showStatus('✅ Position GPS détectée avec succès !', 'success');
                    gpsBtn.disabled = false;
                    gpsBtnLabel.textContent = '✓ Position GPS trouvée';
                    setTimeout(function () {
                        gpsBtnLabel.textContent = 'Me localiser automatiquement';
                    }, 4000);
                },
                /* Erreur */
                function (err) {
                    var messages = {
                        1: '⛔ Permission GPS refusée. Autorisez l\'accès dans les paramètres de votre navigateur.',
                        2: '⚠️ Position GPS indisponible. Vérifiez que votre GPS est activé.',
                        3: '⏱ Délai GPS dépassé. Réessayez ou cliquez sur la carte.',
                    };
                    showStatus(messages[err.code] || '❌ Erreur GPS inconnue', 'error');
                    gpsBtn.disabled = false;
                    gpsBtnLabel.textContent = 'Me localiser automatiquement';
                },
                /* Options */
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
            );
        });
    }

    // ── Géocode l'adresse initiale si présente ─
    var initialAddr = addrInput ? addrInput.value.trim() : '';
    if (initialAddr.length > 4) {
        setTimeout(function () { geocodeAddress(initialAddr); }, 700);
    } else {
        // Juste enregistrer le centre par défaut
        setCoords(DEFAULT_LAT, DEFAULT_LNG);
    }

    /* ════════════════════════════════════════
       4. VALIDATION ET SOUMISSION FORMULAIRE
    ════════════════════════════════════════ */
    var form   = document.getElementById('mobilePayForm');
    var payBtn = document.getElementById('payBtn');
    var payTxt = document.getElementById('payBtnText');

    if (form && payBtn) {
        form.addEventListener('submit', function (e) {
            var phone = document.getElementById('phoneNumber');
            if (phone && phone.value.replace(/\s/g, '').length < 7) {
                e.preventDefault();
                phone.style.borderColor = '#dc2626';
                phone.style.boxShadow   = '0 0 0 3px #fee2e2';
                phone.focus();
                return;
            }
            payBtn.disabled = true;
            payBtn.style.background = '#15803d';
            if (payTxt) payTxt.textContent = '⏳ Traitement en cours…';
        });
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>









cat > /mnt/user-data/outputs/fix_minimal.js << 'EOF'
/**
 * CORRECTIF CARTE BLANCHE — Leaflet dans layout flex/grid
 *
 * Colle ce bloc dans checkout_mobile_pay.php
 * en remplacement de ton initMap() actuel.
 *
 * Les 3 causes corrigées ici :
 *  1. map.invalidateSize() appelé après 300ms (flex/grid cache la taille réelle)
 *  2. Observer ResizeObserver pour recalculer si le panneau change de taille
 *  3. Le conteneur n'utilise plus la classe CSS mais un style inline height
 */

/* ── Dans ton HTML, remplace le div carte par : ───────────────
<div id="checkoutAddressMap"
     style="height:220px;width:100%;border-radius:12px;
            overflow:hidden;border:1.5px solid #e2e8f0;
            position:relative;z-index:0;">
</div>
──────────────────────────────────────────────────────────── */

/* ── Remplace TOUT ton ancien <script> par celui-ci ───────── */

document.addEventListener('DOMContentLoaded', function () {

    /* ════════════════════════════════════
       CARTE GPS
    ════════════════════════════════════ */
    var mapEl = document.getElementById('checkoutAddressMap');
    if (mapEl && typeof L !== 'undefined') {

        var addrInput   = document.getElementById('deliveryAddress');
        var latInput    = document.getElementById('gpsLat');
        var lngInput    = document.getElementById('gpsLng');
        var statusBar   = document.getElementById('gpsStatusBar');
        var gpsBtn      = document.getElementById('gpsLocateBtn');
        var gpsBtnLabel = document.getElementById('gpsBtnLabel');
        var recapBlock  = document.getElementById('gpsRecapBlock');
        var recapCoords = document.getElementById('gpsRecapCoords');

        var LAT = 6.3654, LNG = 2.4183, ZOOM = 14;

        /* Init */
        var map = L.map('checkoutAddressMap', {
            center: [LAT, LNG],
            zoom: ZOOM,
            zoomControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        /* ✅ CORRECTIF 1 : invalider la taille après rendu */
        setTimeout(function () { map.invalidateSize(); }, 300);

        /* ✅ CORRECTIF 2 : recalculer si layout change (accordion, tabs, etc.) */
        if (window.ResizeObserver) {
            var ro = new ResizeObserver(function () { map.invalidateSize(); });
            ro.observe(mapEl);
        }

        /* Icône */
        var pinIcon = L.divIcon({
            className: '',
            html: '<div style="width:32px;height:32px;border-radius:50%;background:#16a34a;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;font-size:16px;">📍</div>',
            iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -34],
        });

        var marker = L.marker([LAT, LNG], { icon: pinIcon, draggable: true }).addTo(map);
        marker.bindPopup('<b>Point de livraison</b><br><small>Glissez pour ajuster</small>').openPopup();

        /* Helpers */
        function setCoords(lat, lng) {
            if (latInput)    latInput.value    = lat.toFixed(6);
            if (lngInput)    lngInput.value    = lng.toFixed(6);
            if (recapBlock)  recapBlock.style.display  = 'block';
            if (recapCoords) recapCoords.textContent   = 'Lat : ' + lat.toFixed(5) + '   Lng : ' + lng.toFixed(5);
        }

        function showStatus(msg, type) {
            if (!statusBar) return;
            statusBar.className    = 'fm-gps-status ' + type;
            statusBar.textContent  = msg;
            statusBar.style.display = 'block';
            if (type === 'success') setTimeout(function () { statusBar.style.display = 'none'; }, 3500);
        }

        /* Géocodage inverse coords → adresse */
        function reverseGeocode(lat, lng) {
            fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
                headers: { 'Accept-Language': 'fr' }
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d || !d.address || !addrInput) return;
                var parts = [
                    d.address.road,
                    d.address.neighbourhood || d.address.suburb,
                    d.address.city || d.address.town || d.address.village,
                ].filter(Boolean);
                addrInput.value = parts.join(', ') || (d.display_name || '').split(',').slice(0, 3).join(', ');
            })
            .catch(function () {});
        }

        /* Drag */
        marker.on('dragend', function () {
            var ll = marker.getLatLng();
            setCoords(ll.lat, ll.lng);
            reverseGeocode(ll.lat, ll.lng);
            showStatus('📍 Position mise à jour', 'success');
        });

        /* Clic sur la carte */
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            map.panTo(e.latlng);
            setCoords(e.latlng.lat, e.latlng.lng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
            marker.openPopup();
            showStatus('📍 Position sélectionnée', 'success');
        });

        /* Géocodage adresse → carte */
        var geocodeTimer = null;
        function geocodeAddress(address) {
            if (!address || address.length < 4) return;
            showStatus('🔍 Recherche de l\'adresse…', 'searching');
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(address + ', Bénin') + '&format=json&limit=1', {
                headers: { 'Accept-Language': 'fr' }
            })
            .then(function (r) { return r.json(); })
            .then(function (results) {
                if (results.length > 0) {
                    var lat = parseFloat(results[0].lat);
                    var lng = parseFloat(results[0].lon);
                    marker.setLatLng([lat, lng]);
                    map.flyTo([lat, lng], 16, { animate: true, duration: 1 });
                    setCoords(lat, lng);
                    showStatus('✅ Adresse localisée sur la carte', 'success');
                } else {
                    showStatus('⚠️ Adresse introuvable — cliquez sur la carte', 'warning');
                }
            })
            .catch(function () { showStatus('❌ Erreur réseau', 'error'); });
        }

        if (addrInput) {
            addrInput.addEventListener('input', function () {
                clearTimeout(geocodeTimer);
                geocodeTimer = setTimeout(function () { geocodeAddress(addrInput.value); }, 900);
            });
        }

        /* Bouton GPS */
        if (gpsBtn) {
            gpsBtn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    showStatus('❌ GPS non supporté par ce navigateur', 'error');
                    return;
                }
                gpsBtn.disabled = true;
                if (gpsBtnLabel) gpsBtnLabel.textContent = '⏳ Localisation…';
                showStatus('📡 Récupération de votre position GPS…', 'searching');

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        var lat = pos.coords.latitude;
                        var lng = pos.coords.longitude;
                        marker.setLatLng([lat, lng]);
                        map.flyTo([lat, lng], 17, { animate: true, duration: 1.2 });
                        setCoords(lat, lng);
                        reverseGeocode(lat, lng);
                        showStatus('✅ Position GPS détectée !', 'success');
                        gpsBtn.disabled = false;
                        if (gpsBtnLabel) {
                            gpsBtnLabel.textContent = '✓ Position trouvée';
                            setTimeout(function () {
                                gpsBtnLabel.textContent = 'Me localiser automatiquement';
                            }, 4000);
                        }
                    },
                    function (err) {
                        var msgs = {
                            1: '⛔ Permission refusée — autorisez dans les paramètres',
                            2: '⚠️ GPS indisponible — vérifiez votre appareil',
                            3: '⏱ Délai dépassé — réessayez',
                        };
                        showStatus(msgs[err.code] || '❌ Erreur GPS', 'error');
                        gpsBtn.disabled = false;
                        if (gpsBtnLabel) gpsBtnLabel.textContent = 'Me localiser automatiquement';
                    },
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                );
            });
        }

        /* Adresse initiale */
        var initAddr = addrInput ? addrInput.value.trim() : '';
        if (initAddr.length > 4) {
            setTimeout(function () { geocodeAddress(initAddr); }, 800);
        } else {
            setCoords(LAT, LNG);
        }
    }

    /* ════════════════════════════════════
       OPÉRATEUR
    ════════════════════════════════════ */
    var opData = {
        'Moov Money':   { color:'#0066CC', logo:'M' },
        'MTN mobile':   { color:'#F5A623', logo:'M' },
        'Celtiis Cash': { color:'#E01B24', logo:'C' },
    };
    document.querySelectorAll('.fm-op-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.fm-op-pill').forEach(function (p) { p.classList.remove('fm-op-active'); });
            pill.classList.add('fm-op-active');
            var op = pill.dataset.op;
            var inp = document.getElementById('operatorInput');
            var hint = document.getElementById('opNameHint');
            var icon = document.getElementById('recapOpIcon');
            var name = document.getElementById('recapOpName');
            if (inp)  inp.value = op;
            if (hint) hint.textContent = op;
            if (icon) { icon.style.background = opData[op]?.color || '#16a34a'; icon.textContent = opData[op]?.logo || 'M'; }
            if (name) name.textContent = op;
        });
    });

    /* ════════════════════════════════════
       EMAIL TOGGLE
    ════════════════════════════════════ */
    var cb = document.getElementById('receiptEmailCheckbox');
    var emailBox = document.getElementById('receiptEmailContainer');
    var emailFld = document.getElementById('receiptEmail');
    if (cb && emailBox) {
        cb.addEventListener('change', function () {
            emailBox.classList.toggle('open', cb.checked);
            if (emailFld) cb.checked ? emailFld.setAttribute('required','') : emailFld.removeAttribute('required');
        });
    }

    /* ════════════════════════════════════
       VALIDATION FORMULAIRE
    ════════════════════════════════════ */
    var form   = document.getElementById('mobilePayForm');
    var payBtn = document.getElementById('payBtn');
    var payTxt = document.getElementById('payBtnText');
    if (form && payBtn) {
        form.addEventListener('submit', function (e) {
            var phone = document.getElementById('phoneNumber');
            if (phone && phone.value.replace(/\s/g,'').length < 7) {
                e.preventDefault();
                phone.style.borderColor = '#dc2626';
                phone.style.boxShadow   = '0 0 0 3px #fee2e2';
                phone.focus();
                return;
            }
            payBtn.disabled = true;
            payBtn.style.background = '#15803d';
            if (payTxt) payTxt.textContent = '⏳ Traitement en cours…';
        });
    }
});
EOF
echo "Done"
Sortie

