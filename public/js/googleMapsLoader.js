document.addEventListener('DOMContentLoaded', function () {
  if (window.loadGoogleMapsApi) {
    return;
  }

  window.loadGoogleMapsApi = function () {
    if (window.google && window.google.maps) {
      return Promise.resolve(window.google.maps);
    }

    if (window._googleMapsApiPromise) {
      return window._googleMapsApiPromise;
    }

    const meta = document.querySelector('meta[name="google-maps-api-key"]');
    const apiKey = meta ? meta.content.trim() : '';
    if (!apiKey) {
      return Promise.reject(new Error('Google Maps API key introuvable. Définissez VITE_GOOGLE_MAPS_API_KEY dans l’environnement PHP.'));
    }

    window._googleMapsApiPromise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places`;
      script.async = true;
      script.defer = true;
      script.onload = function () {
        if (window.google && window.google.maps) {
          resolve(window.google.maps);
        } else {
          reject(new Error('Google Maps n’a pas pu être initialisé.'));
        }
      };
      script.onerror = function () {
        reject(new Error('Impossible de charger l’API Google Maps.'));
      };
      document.head.appendChild(script);
    });

    return window._googleMapsApiPromise;
  };
});
