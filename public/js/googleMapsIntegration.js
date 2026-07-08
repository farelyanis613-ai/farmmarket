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
    let autocomplete;
    // Try to wire Places Autocomplete when the API is available
    window.loadGoogleMapsApi().then(function (maps) {
      try {
        if (maps.places && addressInput) {
          autocomplete = new maps.places.Autocomplete(addressInput, { types: ['geocode'] });
          // limit returned fields for performance
          if (typeof autocomplete.setFields === 'function') {
            autocomplete.setFields(['geometry', 'formatted_address']);
          }
          autocomplete.addListener && autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (place && place.geometry && place.geometry.location) {
              // update input value with formatted address when available
              if (place.formatted_address) {
                addressInput.value = place.formatted_address;
              }
              var loc = place.geometry.location;
              // ensure container is cleared and map/marker are created or updated
              container.innerHTML = '';
              if (!map) {
                map = new maps.Map(container, { center: loc, zoom: 14 });
              } else {
                map.setCenter(loc);
              }
              if (!marker) {
                marker = new maps.Marker({ map: map, position: loc, title: 'Adresse de livraison' });
              } else {
                marker.setPosition(loc);
              }
            }
          });
        }
      } catch (e) {
        // ignore autocomplete errors, keep geocoding fallback
        console.warn('Autocomplete non disponible', e);
      }
    }).catch(function () {
      // loader failed — geocode fallback will still be used on input change/blur
    });
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
          const message = error && error.message ? error.message : 'Impossible de charger la carte.';
          container.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-red-500">' + message + '</div>';
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
        const message = error && error.message ? error.message : 'Impossible de charger la carte.';
        showError(message);
      });
  };
});
