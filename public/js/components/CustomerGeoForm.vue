<template>
  <form ref="geoForm" id="checkoutGeoForm" @submit.prevent="handleSubmit" class="space-y-6 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
    <div>
      <label class="block text-sm font-semibold text-slate-900 mb-2">Adresse de livraison</label>
      <textarea
        v-model="address"
        name="delivery_address"
        rows="4"
        class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"
        placeholder="Entrez votre adresse complète"
        required
      ></textarea>
    </div>

    <input type="hidden" name="latitude" :value="latitude" />
    <input type="hidden" name="longitude" :value="longitude" />

    <div class="grid gap-3 sm:grid-cols-2">
      <button
        type="submit"
        class="inline-flex justify-center rounded-3xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition"
        :disabled="loading"
      >
        {{ loading ? 'Localisation en cours...' : 'Valider l’adresse' }}
      </button>

      <button
        type="button"
        @click="openDirectionsToStore"
        class="inline-flex justify-center rounded-3xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100 transition"
      >
        Venir en boutique
      </button>
    </div>

    <div v-if="message" class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900 text-sm">
      {{ message }}
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'

// Composant de formulaire client pour géolocaliser l'adresse de livraison.
// À intégrer dans le fichier de formulaire client, par exemple views/cart/checkout_mobile.php.

const address = ref('')
const latitude = ref('')
const longitude = ref('')
const message = ref('')
const loading = ref(false)
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

const geocodeAddress = async (query) => {
  if (!apiKey) {
    message.value = 'Clé API Google Maps manquante. Vérifiez votre fichier .env.'
    return null
  }

  const encoded = encodeURIComponent(query)
  const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encoded}&key=${apiKey}`

  try {
    const response = await fetch(url)
    const data = await response.json()

    if (data.status !== 'OK' || !data.results || !data.results.length) {
      message.value = 'Adresse introuvable. Vérifiez la saisie et réessayez.'
      return null
    }

    const location = data.results[0].geometry.location
    return {
      lat: location.lat,
      lng: location.lng,
    }
  } catch (error) {
    message.value = 'Erreur lors de l’appel à Google Maps Geocoding.'
    return null
  }
}

const handleSubmit = async () => {
  if (!address.value.trim()) {
    message.value = 'Veuillez saisir une adresse de livraison.'
    return
  }

  loading.value = true
  message.value = 'Géocodage de l’adresse en cours...'

  const coords = await geocodeAddress(address.value)
  if (!coords) {
    loading.value = false
    return
  }

  latitude.value = coords.lat.toFixed(8)
  longitude.value = coords.lng.toFixed(8)
  message.value = `Adresse localisée : latitude ${latitude.value}, longitude ${longitude.value}`

  const nativeForm = geoForm.value
  if (nativeForm) {
    nativeForm.submit()
  }
}

const openDirectionsToStore = () => {
  if (!navigator.geolocation) {
    message.value = 'Géolocalisation non supportée par votre navigateur.'
    return
  }

  message.value = 'Détection de votre position en cours...'
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const origin = `${pos.coords.latitude},${pos.coords.longitude}`
      const destination = '6.3570077,2.3790333'
      const mapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}&travelmode=driving`
      window.open(mapsUrl, '_blank')
      message.value = 'Itinéraire ouvert dans Google Maps.'
    },
    (err) => {
      message.value = 'Impossible de récupérer votre position : ' + err.message
    },
    { enableHighAccuracy: true, timeout: 10000 }
  )
}

const geoForm = ref(null)
</script>

<style scoped>
textarea {
  min-height: 138px;
}
</style>
