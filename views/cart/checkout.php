<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['product']['price'] * $item['quantity'];
    }
    $deliveryFeeHome = HOME_DELIVERY_FEE;
    $total = $subtotal + $deliveryFeeHome;
?>

<link rel="stylesheet" href="public/lib/leaflet/leaflet.css">

<div class="page-content pb-16">

    <!-- ── En-tête ────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Confirmation de commande</h1>
            <p class="page-subtitle">Vérifiez vos articles et choisissez votre mode de livraison.</p>
        </div>
        <a href="index.php?action=cart" class="btn-ghost">← Modifier le panier</a>
    </div>

    <!-- ── Barre de progression ───────────────────────── -->
    <div class="fm-steps" aria-label="Étapes de commande">
        <div class="fm-step fm-step-done">
            <div class="fm-step-dot">✓</div>
            <span class="fm-step-lbl">Panier</span>
        </div>
        <div class="fm-step-line fm-step-line-done"></div>
        <div class="fm-step fm-step-active">
            <div class="fm-step-dot">📋</div>
            <span class="fm-step-lbl">Confirmation</span>
        </div>
        <div class="fm-step-line"></div>
        <div class="fm-step fm-step-todo">
            <div class="fm-step-dot">💳</div>
            <span class="fm-step-lbl">Paiement</span>
        </div>
        <div class="fm-step-line"></div>
        <div class="fm-step fm-step-todo">
            <div class="fm-step-dot">🎉</div>
            <span class="fm-step-lbl">Terminé</span>
        </div>
    </div>

    <div class="fm-co-grid">

        <!-- ══════════════════════════
             COLONNE GAUCHE
        ══════════════════════════ -->
        <div class="fm-co-left">

            <!-- Articles -->
            <div class="fm-section-card">
                <h2 class="fm-section-title">🛒 Vos articles</h2>
                <div class="fm-items-list">
                    <?php foreach ($cart as $item) :
                        $lineTotal = $item['product']['price'] * $item['quantity'];
                    ?>
                    <div class="fm-item-row fm-reveal">
                        <div class="fm-item-info">
                            <p class="fm-item-name"><?= htmlspecialchars($item['product']['name']) ?></p>
                            <p class="fm-item-meta">
                                Qté : <strong><?= intval($item['quantity']) ?></strong>
                                &nbsp;·&nbsp;
                                P.U. : <strong><?= number_format($item['product']['price'], 0, '', ' ') ?> FCFA</strong>
                            </p>
                        </div>
                        <span class="fm-item-price"><?= number_format($lineTotal, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Mode de livraison -->
            <form id="checkoutCompleteForm" action="index.php?action=checkout/complete" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

                <div class="section-card">
                    <h2 class="section-title">🚚 Mode de livraison</h2>
                    <div class="fm-delivery-grid">

                        <!-- Livraison domicile -->
                        <label class="fm-delivery-card" id="labelHome">
                            <input type="radio" name="delivery_type" value="home" class="fm-radio-hidden" checked>
                            <div class="fm-delivery-icon">🚚</div>
                            <div class="fm-delivery-info">
                                <p class="fm-delivery-name">Livraison à domicile</p>
                                <p class="fm-delivery-desc">Recevez votre commande directement chez vous.</p>
                                <span class="fm-delivery-fee fm-fee-paid">+<?= number_format($deliveryFeeHome, 0, '', ' ') ?> FCFA</span>
                            </div>
                            <span class="fm-delivery-check">✓</span>
                        </label>

                        <!-- Retrait boutique -->
                        <label class="fm-delivery-card" id="labelShop">
                            <input type="radio" name="delivery_type" value="shop" class="fm-radio-hidden">
                            <div class="fm-delivery-icon">🏪</div>
                            <div class="fm-delivery-info">
                                <p class="fm-delivery-name">Retrait en boutique</p>
                                <p class="fm-delivery-desc">Venez chercher votre commande à Hévié Akossavié.</p>
                                <span class="fm-delivery-fee fm-fee-free">Gratuit</span>
                            </div>
                            <span class="fm-delivery-check">✓</span>
                        </label>

                    </div>
                </div>

                <div class="section-card" id="deliveryAddressSection">
                    <h2 class="section-title">📍 Adresse de livraison</h2>
                    <p class="fm-field-hint">Indiquez l'adresse où vous souhaitez recevoir votre commande, ou ajustez le repère sur la carte pour préciser l'emplacement exact.</p>
                    <input id="deliveryAddress" name="delivery_address" type="text" class="fm-input" placeholder="Ex : Cotonou, Akossavié" autocomplete="street-address" value="<?= htmlspecialchars($_SESSION['user']['address'] ?? '') ?>">

                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.75rem;margin-top:0.85rem;flex-wrap:wrap;">
                        <button type="button" id="useMyLocationBtn" class="btn-ghost" style="white-space:nowrap;">📡 Utiliser ma position actuelle</button>
                    </div>

                    <div id="checkoutAddressMap" class="fm-checkout-map" style="margin-top:0.75rem;height:260px;width:100%;border-radius:16px;border:1px solid #e2e8f0;"></div>
                    <div id="gpsStatusBar" class="fm-gps-status" style="display:none;margin-top:0.85rem;"></div>
                    <p id="mapConfirmHint" class="fm-field-hint" style="margin-top:0.6rem;color:#94a3b8;">
                        📌 Le repère rouge sur la carte est la position qui sera transmise au livreur. Déplacez-le si besoin.
                    </p>
                </div>

                <!-- Opérateur Mobile Money -->
                <div class="section-card">
                    <h2 class="section-title">💳 Opérateur Mobile Money</h2>
                    <div class="fm-op-pills" id="operatorPills">
                        <?php
                        $ops = [
                            'Moov Money'   => ['color'=>'#0066CC','bg'=>'#E6F0FB','logo'=>'M'],
                            'MTN mobile'   => ['color'=>'#F5A623','bg'=>'#FEF3DC','logo'=>'M'],
                            'Celtiis Cash' => ['color'=>'#E01B24','bg'=>'#FDEAEA','logo'=>'C'],
                        ];
                        foreach ($ops as $name => $op) : ?>
                        <button type="button"
                                class="fm-op-pill <?= $name === 'Moov Money' ? 'fm-op-active' : '' ?>"
                                data-op="<?= htmlspecialchars($name) ?>"
                                style="--op-color:<?= $op['color'] ?>;--op-bg:<?= $op['bg'] ?>">
                            <span class="fm-op-logo" style="background:<?= $op['color'] ?>"><?= $op['logo'] ?></span>
                            <?= htmlspecialchars($name) ?>
                            <span class="fm-op-check">✓</span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedOperator" value="Moov Money">
                </div>

                <input type="hidden" name="delivery_fee"      id="deliveryFeeInput"      value="<?= $deliveryFeeHome ?>">
                <input type="hidden" name="total_amount"      id="finalAmountInput"      value="<?= $total ?>">
                <input type="hidden" name="delivery_address"  id="deliveryAddressInput"  value="<?= htmlspecialchars($_SESSION['user']['address'] ?? '') ?>">
                <input type="hidden" name="delivery_latitude" id="deliveryLatitudeInput" value="">
                <input type="hidden" name="delivery_longitude" id="deliveryLongitudeInput" value="">

                <!-- Bouton continuer -->
                <button id="continueToMobile" type="button" class="btn-primary w-full">
                    Continuer vers le paiement →
                </button>
                <p class="fm-continue-hint">Vous serez redirigé vers la page de paiement sécurisé Mobile Money.</p>

            </form>
        </div>

        <!-- ══════════════════════════
             COLONNE DROITE — Récap
        ══════════════════════════ -->
        <aside class="fm-co-right">
            <div class="fm-recap-card section-card">
                <h2 class="section-title fm-recap-title">Récapitulatif</h2>

                <div class="fm-recap-rows">
                    <div class="fm-recap-row">
                        <span>Sous-total</span>
                        <span><?= number_format($subtotal, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <div class="fm-recap-row">
                        <span>Livraison</span>
                        <span id="recapFee">+<?= number_format($deliveryFeeHome, 0, '', ' ') ?> FCFA</span>
                    </div>
                    <div class="fm-recap-total">
                        <span>Total</span>
                        <span id="recapTotal"><?= number_format($total, 0, '', ' ') ?> FCFA</span>
                    </div>
                </div>

                <!-- Opérateur -->
                <div class="fm-recap-op" id="recapOpBlock">
                    <span class="fm-recap-op-icon" id="recapOpIcon" style="background:#0066CC">M</span>
                    <div>
                        <p class="fm-recap-op-name" id="recapOpName">Moov Money</p>
                        <p class="fm-recap-op-sub">Paiement sécurisé 🔒</p>
                    </div>
                </div>

                <!-- Garanties -->
                <ul class="fm-guarantees">
                    <li>✅ Confirmation par SMS</li>
                    <li>✅ Facture PDF disponible</li>
                    <li>✅ Livraison tracée par GPS</li>
                    <li>✅ Service client réactif</li>
                </ul>

                <!-- Résumé articles (sidebar) -->
                <div class="fm-recap-items">
                    <p class="fm-recap-items-title"><?= count($cart) ?> article<?= count($cart) > 1 ? 's' : '' ?> dans le panier</p>
                    <?php foreach ($cart as $item) : ?>
                    <div class="fm-recap-item-row">
                        <span class="fm-recap-item-name"><?= htmlspecialchars($item['product']['name']) ?> ×<?= intval($item['quantity']) ?></span>
                        <span><?= number_format($item['product']['price'] * $item['quantity'], 0, '', ' ') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

    </div>
</div>

<!-- Inline styles moved to public/css/style.css -->
<!-- views/cart/checkout.php: original <style> block consolidated into public/css/style.css -->

<script src="public/lib/leaflet/leaflet.js"></script>

<script>
(function () {
    var subtotal     = <?= intval($subtotal) ?>;
    var feeHome      = <?= intval($deliveryFeeHome) ?>;
    var opData       = {
        'Moov Money':   { color: '#0066CC', logo: 'M' },
        'MTN mobile':   { color: '#F5A623', logo: 'M' },
        'Celtiis Cash': { color: '#E01B24', logo: 'C' },
    };

    /* ── Scroll reveal ──────────────────────── */
    var reveals = document.querySelectorAll('.fm-reveal');
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.1 });
    reveals.forEach(function (el) { obs.observe(el); });

    /* ── Mode livraison ─────────────────────── */
    function fmt(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '); }

    function selectDelivery(card) {
        document.querySelectorAll('.fm-delivery-card').forEach(function (c) { c.classList.remove('fm-delivery-selected'); });
        card.classList.add('fm-delivery-selected');
        var radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        var isHome  = radio && radio.value === 'home';
        var fee     = isHome ? feeHome : 0;
        var total   = subtotal + fee;

        document.getElementById('deliveryFeeInput').value  = fee;
        document.getElementById('finalAmountInput').value  = total;
        document.getElementById('recapFee').textContent    = fee > 0 ? '+' + fmt(fee) + ' FCFA' : 'Gratuit';
        document.getElementById('recapTotal').textContent  = fmt(total) + ' FCFA';
    }

    /* ── Opérateur ──────────────────────────── */
    document.querySelectorAll('.fm-op-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.fm-op-pill').forEach(function (p) { p.classList.remove('fm-op-active'); });
            pill.classList.add('fm-op-active');

            var op = pill.dataset.op;
            document.getElementById('selectedOperator').value = op;

            var d = opData[op] || {};
            var icon = document.getElementById('recapOpIcon');
            var name = document.getElementById('recapOpName');
            if (icon) { icon.style.background = d.color || '#16a34a'; icon.textContent = d.logo || 'M'; }
            if (name) { name.textContent = op; }
        });
    });

    /* ── Section adresse ────────────────────── */
    function updateDeliveryAddressSection(isHome) {
        var section = document.getElementById('deliveryAddressSection');
        var addressInput = document.getElementById('deliveryAddress');
        if (!section || !addressInput) {
            return;
        }
        section.style.display = isHome ? 'block' : 'none';
        if (!isHome) {
            addressInput.value = '';
            document.getElementById('deliveryAddressInput').value = '';
            document.getElementById('deliveryLatitudeInput').value = '';
            document.getElementById('deliveryLongitudeInput').value = '';
            window._checkoutLocationConfirmed = false;
        } else if (window._checkoutMap) {
            // La section redevient visible : Leaflet a besoin d'un recalcul de taille.
            setTimeout(function () { window._checkoutMap.invalidateSize(); }, 50);
        }
    }

    function bindDeliveryInputs() {
        var addressInput = document.getElementById('deliveryAddress');
        if (addressInput) {
            addressInput.addEventListener('input', function () {
                document.getElementById('deliveryAddressInput').value = addressInput.value;
            });
        }
    }

    document.querySelectorAll('.fm-delivery-card').forEach(function (card) {
        card.addEventListener('click', function () {
            selectDelivery(card);
            var radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === 'home') {
                updateDeliveryAddressSection(true);
            } else {
                updateDeliveryAddressSection(false);
            }
        });
    });
    // initialiser la première carte sélectionnée
    var firstCard = document.getElementById('labelHome');
    if (firstCard) {
        selectDelivery(firstCard);
        updateDeliveryAddressSection(true);
    }

    bindDeliveryInputs();

    /* ── Bouton continuer ───────────────────── */
    document.getElementById('continueToMobile').addEventListener('click', function () {
        var deliveryType = (document.querySelector('input[name="delivery_type"]:checked') || {}).value || 'home';
        var fee          = document.getElementById('deliveryFeeInput').value;
        var operator     = document.getElementById('selectedOperator').value;
        var addressInput = document.getElementById('deliveryAddress');
        var address      = addressInput ? addressInput.value.trim() : document.getElementById('deliveryAddressInput').value.trim();
        var latitude     = document.getElementById('deliveryLatitudeInput').value;
        var longitude    = document.getElementById('deliveryLongitudeInput').value;

        // Pour la livraison à domicile, on exige une position confirmée sur la carte :
        // on promet un suivi GPS au client, donc on ne peut pas laisser passer une commande sans coordonnées.
        if (deliveryType === 'home' && (!latitude || !longitude)) {
            var statusBar = document.getElementById('gpsStatusBar');
            if (statusBar) {
                statusBar.style.display = 'block';
                statusBar.style.color = '#dc2626';
                statusBar.textContent = 'Veuillez confirmer votre position sur la carte (tapez une adresse, cliquez sur la carte, ou utilisez « Utiliser ma position actuelle ») avant de continuer.';
            }
            var mapEl = document.getElementById('checkoutAddressMap');
            if (mapEl) mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        document.getElementById('deliveryAddressInput').value = address;

        var url = 'index.php?action=checkout/mobile'
            + '&delivery_type='  + encodeURIComponent(deliveryType)
            + '&delivery_fee='   + encodeURIComponent(fee)
            + '&operator='       + encodeURIComponent(operator);

        if (deliveryType === 'home') {
            if (address) {
                url += '&address=' + encodeURIComponent(address);
            }
            if (latitude && longitude) {
                url += '&latitude=' + encodeURIComponent(latitude) + '&longitude=' + encodeURIComponent(longitude);
            }
        }

        window.location = url;
    });
})();
</script>

<script>
/**
 * Carte de sélection d'adresse pour le checkout (Leaflet / OpenStreetMap).
 * - Un marqueur déplaçable représente l'adresse de livraison.
 * - Cliquer sur la carte déplace le marqueur.
 * - Taper une adresse la géocode automatiquement pour repositionner le marqueur.
 * - Un bouton permet d'utiliser la géolocalisation du navigateur.
 * Les coordonnées choisies sont écrites dans #deliveryLatitudeInput / #deliveryLongitudeInput.
 *
 * Améliorations apportées :
 *  - Icône de marqueur personnalisée (divIcon en SVG) : n'appelle plus les fichiers PNG
 *    par défaut de Leaflet dont le chemin casse fréquemment en self-hosting.
 *  - Toutes les requêtes de géocodage/reverse-géocodage sont annulables (AbortController)
 *    pour éviter qu'une réponse tardive n'écrase une saisie plus récente (race condition).
 *  - Les erreurs réseau sont désormais interceptées (try/catch) et affichées à l'utilisateur.
 *  - Les recherches sont restreintes au Bénin (countrycodes=bj) pour des résultats plus pertinents.
 *  - Un flag global window._checkoutLocationConfirmed indique si une position valide a été choisie.
 */
window.mountCheckoutAddressMap = function (options) {
    var containerId     = options.containerId;
    var addressInputId  = options.addressInputId;
    var initialAddress  = options.initialAddress || '';

    var mapEl = document.getElementById(containerId);
    var addressInput = document.getElementById(addressInputId);
    var latInput  = document.getElementById('deliveryLatitudeInput');
    var lngInput  = document.getElementById('deliveryLongitudeInput');
    var statusBar = document.getElementById('gpsStatusBar');

    if (!mapEl || typeof L === 'undefined') {
        console.error('Impossible d\'afficher la carte : Leaflet n\'est pas chargé.');
        if (statusBar) {
            statusBar.style.display = 'block';
            statusBar.style.color = '#dc2626';
            statusBar.textContent = 'La carte n\'a pas pu être chargée. Vous pouvez tout de même saisir votre adresse en texte.';
        }
        return;
    }

    window._checkoutLocationConfirmed = false;

    var DEFAULT_LAT = 6.3703, DEFAULT_LNG = 2.3912, DEFAULT_ZOOM = 12; // Cotonou
    var BENIN_COUNTRY_CODE = 'bj';

    var map = L.map(mapEl, { attributionControl: true }).setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
    window._checkoutMap = map;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    /* Icône personnalisée en SVG : évite toute dépendance aux images PNG par défaut de Leaflet
       (marker-icon.png / marker-shadow.png), une source fréquente de marqueurs invisibles. */
    var pinIcon = L.divIcon({
        className: 'fm-map-pin',
        html:
            '<svg width="34" height="44" viewBox="0 0 34 44" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M17 0C7.6 0 0 7.6 0 17c0 11.3 14.2 25.7 15.9 27.4.6.6 1.6.6 2.2 0C19.8 42.7 34 28.3 34 17 34 7.6 26.4 0 17 0z" fill="#16a34a"/>' +
            '<circle cx="17" cy="17" r="7" fill="#ffffff"/>' +
            '</svg>',
        iconSize: [34, 44],
        iconAnchor: [17, 44],
        popupAnchor: [0, -40]
    });

    var marker = L.marker([DEFAULT_LAT, DEFAULT_LNG], { draggable: true, icon: pinIcon }).addTo(map);

    /* ── Annulation des requêtes en vol ─────────────────────────
       Chaque nouvelle recherche (saisie texte, GPS, adresse initiale) annule la précédente
       pour qu'une réponse arrivée en retard ne vienne pas écraser une position plus récente. */
    var activeGeocodeController = null;
    var activeReverseController = null;

    function setStatus(msg, type) {
        if (!statusBar) return;
        statusBar.style.display = msg ? 'block' : 'none';
        statusBar.textContent = msg || '';
        statusBar.style.color = type === 'error' ? '#dc2626' : (type === 'success' ? '#15803d' : '#64748b');
    }

    function updateCoords(lat, lng) {
        window._checkoutLocationConfirmed = true;
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
    }

    function placeMarker(lat, lng, recenter) {
        marker.setLatLng([lat, lng]);
        if (recenter) map.setView([lat, lng], 15);
        updateCoords(lat, lng);
    }

    async function geocodeAddress(query) {
        if (activeGeocodeController) activeGeocodeController.abort();
        activeGeocodeController = new AbortController();

        var url = 'https://nominatim.openstreetmap.org/search'
            + '?format=json&q=' + encodeURIComponent(query)
            + '&limit=1&accept-language=fr'
            + '&countrycodes=' + BENIN_COUNTRY_CODE;

        try {
            var res = await fetch(url, {
                headers: { 'Accept-Language': 'fr' },
                signal: activeGeocodeController.signal
            });
            if (!res.ok) return { ok: false, aborted: false };
            var results = await res.json();
            if (!results.length) return { ok: false, aborted: false };
            return { ok: true, lat: parseFloat(results[0].lat), lng: parseFloat(results[0].lon) };
        } catch (e) {
            if (e.name === 'AbortError') return { ok: false, aborted: true };
            return { ok: false, aborted: false, networkError: true };
        }
    }

    async function reverseGeocode(lat, lng) {
        if (activeReverseController) activeReverseController.abort();
        activeReverseController = new AbortController();

        try {
            var url = 'https://nominatim.openstreetmap.org/reverse'
                + '?format=json&lat=' + lat + '&lon=' + lng + '&accept-language=fr';
            var res = await fetch(url, {
                headers: { 'Accept-Language': 'fr' },
                signal: activeReverseController.signal
            });
            if (!res.ok) return null;
            var data = await res.json();
            return data && data.display_name ? data.display_name : null;
        } catch (e) {
            return null; // silencieux : le reverse-geocode n'est qu'un confort d'affichage
        }
    }

    function applyReverseName(lat, lng) {
        reverseGeocode(lat, lng).then(function (name) {
            if (name && addressInput) {
                addressInput.value = name;
                var hidden = document.getElementById('deliveryAddressInput');
                if (hidden) hidden.value = name;
            }
        });
    }

    /* Déplacement du marqueur à la main */
    marker.on('dragend', function () {
        var pos = marker.getLatLng();
        updateCoords(pos.lat, pos.lng);
        setStatus('Position mise à jour.', 'success');
        applyReverseName(pos.lat, pos.lng);
    });

    /* Clic sur la carte pour repositionner le marqueur */
    map.on('click', function (e) {
        placeMarker(e.latlng.lat, e.latlng.lng, false);
        setStatus('Position mise à jour.', 'success');
        applyReverseName(e.latlng.lat, e.latlng.lng);
    });

    /* Saisie d'adresse texte → géocodage automatique (avec debounce) */
    var geocodeTimeout = null;
    if (addressInput) {
        addressInput.addEventListener('input', function () {
            clearTimeout(geocodeTimeout);
            var value = addressInput.value.trim();
            if (value.length < 4) return;
            geocodeTimeout = setTimeout(function () {
                setStatus('Recherche de l\'adresse…', 'loading');
                geocodeAddress(value).then(function (result) {
                    if (result.aborted) return; // une saisie plus récente a pris le relais
                    if (result.ok) {
                        placeMarker(result.lat, result.lng, true);
                        setStatus('Adresse localisée sur la carte.', 'success');
                    } else if (result.networkError) {
                        setStatus('Connexion impossible. Vérifiez votre réseau et placez le repère manuellement.', 'error');
                    } else {
                        setStatus('Adresse introuvable. Vous pouvez placer le repère manuellement sur la carte.', 'error');
                    }
                });
            }, 600);
        });
    }

    /* Bouton géolocalisation */
    var geoBtn = document.getElementById('useMyLocationBtn');
    if (geoBtn) {
        geoBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                setStatus('La géolocalisation n\'est pas disponible sur cet appareil.', 'error');
                return;
            }
            geoBtn.disabled = true;
            setStatus('Récupération de votre position…', 'loading');
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                placeMarker(lat, lng, true);
                setStatus('Position actuelle utilisée.', 'success');
                applyReverseName(lat, lng);
                geoBtn.disabled = false;
            }, function (err) {
                var msg = 'Impossible d\'obtenir votre position. Vérifiez les autorisations de localisation.';
                if (err && err.code === 1) msg = 'Localisation refusée. Autorisez l\'accès à votre position dans les réglages du navigateur, ou placez le repère manuellement.';
                if (err && err.code === 3) msg = 'La localisation a pris trop de temps. Réessayez ou placez le repère manuellement.';
                setStatus(msg, 'error');
                geoBtn.disabled = false;
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }

    /* Position initiale : géocoder l'adresse déjà connue du client, sinon vue par défaut */
    if (initialAddress && initialAddress.trim().length >= 4) {
        setStatus('Localisation de votre adresse enregistrée…', 'loading');
        geocodeAddress(initialAddress.trim()).then(function (result) {
            if (result.aborted) return;
            if (result.ok) {
                placeMarker(result.lat, result.lng, true);
                setStatus('', null);
            } else if (result.networkError) {
                setStatus('Connexion impossible pour localiser votre adresse enregistrée : placez le repère manuellement.', 'error');
            } else {
                setStatus('Adresse enregistrée introuvable sur la carte : placez le repère manuellement.', 'error');
            }
        });
    }

    setTimeout(function () { map.invalidateSize(); }, 100);
};

window.addEventListener('load', function () {
    if (typeof window.mountCheckoutAddressMap === 'function') {
        window.mountCheckoutAddressMap({
            containerId: 'checkoutAddressMap',
            addressInputId: 'deliveryAddress',
            initialAddress: document.getElementById('deliveryAddress')?.value || ''
        });
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>