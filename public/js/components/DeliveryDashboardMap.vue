<template>
  <div class="delivery-map-component rounded-3xl overflow-hidden border border-slate-200 shadow-sm">
    <div ref="mapContainer" class="h-80 w-full"></div>
    <div class="p-4 bg-white">
      <h3 class="text-lg font-bold text-slate-900 mb-2">Position de la commande</h3>
      <p class="text-sm text-slate-600 mb-2">Client : {{ order.customer_name }}</p>
      <p class="text-sm text-slate-600">Adresse : {{ order.address }}</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

const mapContainer = ref(null)
const mapInstance = ref(null)

const loadGoogleMaps = () => {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.maps) {
      return resolve(window.google.maps)
    }

    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${import.meta.env.VITE_GOOGLE_MAPS_API_KEY}`
    script.async = true
    script.defer = true
    script.onload = () => resolve(window.google.maps)
    script.onerror = reject
    document.head.appendChild(script)
  })
}

const initMap = async () => {
  const maps = await loadGoogleMaps()
  const position = {
    lat: Number(props.order.latitude) || 6.3570077,
    lng: Number(props.order.longitude) || 2.3790333,
  }

  mapInstance.value = new maps.Map(mapContainer.value, {
    center: position,
    zoom: 13,
  })

  new maps.Marker({
    position,
    map: mapInstance.value,
    title: 'Adresse client',
  })

  new maps.Marker({
    position: { lat: 6.3570077, lng: 2.3790333 },
    map: mapInstance.value,
    label: 'B',
    title: 'Boutique FarmMarket',
  })
}

onMounted(() => {
  if (!props.order.latitude || !props.order.longitude) {
    return
  }
  initMap().catch(() => {
    console.warn('Échec du chargement de Google Maps.')
  })
})
</script>

<style scoped>
.delivery-map-component {
  min-height: 22rem;
}
</style>
