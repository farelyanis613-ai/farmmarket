document.addEventListener('DOMContentLoaded', function () {
  if (window.googleMapsIntegrationLoaded) {
    return;
  }
  window.googleMapsIntegrationLoaded = true;

  function geocodeAddress(maps, address, callback) {
    if (!address || !address.trim()) {
      callback(new Error('Adresse vide'), null);
      return;
    }
    const geocoder = new maps.Geocoder();
    geocoder.geocode({ address: address.trim() }, function (results, status) {
      if (status !== 'OK' || !results || !results[0]) {
        callback(new Error('Impossible de géocoder l’adresse : ' + status), null);
        return;
      }
      callback(null, results[0].geometry.location);
    });
  }

  window.mountCheckoutAddressMap = function (options) {
    const container = document.getElementById(options.containerId);
    const addressInput = document.getElementById(options.addressInputId);
    if (!container || !addressInput) {
      return;
    }

    let map;
    let marker;
    const updateMap = function (address) {
      if (!address || !address.trim()) {
        container.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-slate-500">Saisissez votre adresse pour afficher la carte.</div>';
        return;
      }

      window.loadGoogleMapsApi()
        .then(function (maps) {
          geocodeAddress(maps, address, function (error, location) {
            if (error) {
              console.warn(error.message);
              container.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-red-500">Adresse introuvable.</div>';
              return;
            }

            container.innerHTML = '';
            if (!map) {
              map = new maps.Map(container, {
                center: location,
                zoom: 14,
              });
            } else {
              map.setCenter(location);
            }

            if (!marker) {
              marker = new maps.Marker({
                map: map,
                position: location,
                title: 'Adresse de livraison',
              });
            } else {
              marker.setPosition(location);
            }
          });
        })
        .catch(function (error) {
          console.error(error);
          container.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-red-500">Impossible de charger la carte.</div>';
        });
    };

    addressInput.addEventListener('change', function () {
      updateMap(addressInput.value);
    });
    addressInput.addEventListener('blur', function () {
      updateMap(addressInput.value);
    });

    if (options.initialAddress) {
      updateMap(options.initialAddress);
    } else {
      container.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-slate-500">Saisissez votre adresse pour afficher la carte.</div>';
    }
  };

  window.mountOrderDetailsMap = function (options) {
    const container = document.getElementById(options.containerId);
    if (!container) {
      return;
    }

    const showError = function (message) {
      container.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-red-500">' + message + '</div>';
    };

    const onLocation = function (maps, location) {
      container.innerHTML = '';
      const map = new maps.Map(container, {
        center: location,
        zoom: 13,
      });
      new maps.Marker({
        position: location,
        map: map,
        title: options.title || 'Lieu de livraison',
      });
    };

    window.loadGoogleMapsApi()
      .then(function (maps) {
        if (options.lat && options.lng) {
          onLocation(maps, { lat: Number(options.lat), lng: Number(options.lng) });
        } else if (options.address) {
          geocodeAddress(maps, options.address, function (error, location) {
            if (error) {
              showError('Adresse introuvable.');
              return;
            }
            onLocation(maps, location);
          });
        } else {
          showError('Coordonnées manquantes pour afficher la carte.');
        }
      })
      .catch(function (error) {
        console.error(error);
        showError('Impossible de charger la carte.');
      });
  };
});
