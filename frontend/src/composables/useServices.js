import { ref } from 'vue'

const BASE = '/api/services'

// Durante el prerender del build se inyecta un snapshot del catálogo
// (window.__DULZIA_PRERENDER__): las páginas se generan con datos reales sin
// backend. En el navegador normal esa variable no existe y se usa la API.
const snapshot = typeof window !== 'undefined' ? window.__DULZIA_PRERENDER__ : null

// El catálogo es el mismo durante toda la sesión y lo comparten la portada, el
// listado y las fichas de servicio. Por eso vive a nivel de módulo: se pide una
// vez y no una por página (antes cada visita a una ficha volvía a pedirlo entero).
const services = ref([])
const loading = ref(false)
const loaded = ref(false)
const error = ref(null)

// Petición en curso: si dos páginas montan a la vez, la segunda se cuelga de la
// primera en lugar de lanzar otra.
let pending = null

export function useServices() {
  async function fetchAll({ force = false } = {}) {
    if (snapshot?.list) {
      services.value = snapshot.list
      loaded.value = true
      return
    }

    if (pending) return pending
    if (loaded.value && !force) return

    loading.value = true
    error.value = null

    pending = (async () => {
      try {
        const res = await fetch(BASE)
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        services.value = await res.json()
        loaded.value = true
      } catch (e) {
        // Sin marcar `loaded`: un fallo se reintenta al volver a navegar.
        error.value = e.message
      } finally {
        loading.value = false
        pending = null
      }
    })()

    return pending
  }

  return { services, loading, loaded, error, fetchAll }
}
