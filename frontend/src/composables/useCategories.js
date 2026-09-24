import { ref } from 'vue'

const BASE = '/api/categories'

// Durante el prerender del build se inyecta un snapshot (window.__DULZIA_PRERENDER__)
// con el catálogo y las categorías: sin él la portada estática saldría sin
// pestañas y con los identificadores en vez de los nombres.
const snapshot = typeof window !== 'undefined' ? window.__DULZIA_PRERENDER__ : null

// Las categorías las comparten la parrilla, las tarjetas, la ficha y las pestañas
// del catálogo, así que su estado vive a nivel de módulo: se piden una vez por
// sesión (mismo patrón que useServices).
const categories = ref([])
const loading = ref(false)
const loaded = ref(false)
const error = ref(null)

let pending = null

export function useCategories() {
  async function fetchAll({ force = false } = {}) {
    if (snapshot?.categories) {
      categories.value = snapshot.categories
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
        categories.value = await res.json()
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

  /** Nombre para mostrar. Si la categoría ya no existe, se enseña el identificador
   *  en vez de dejarlo en blanco. */
  function categoryName(id) {
    return categories.value.find(c => c.id === id)?.name ?? id ?? ''
  }

  // Se pide sola: cualquier sitio que pinte una etiqueta de categoría la necesita
  // (incluidas las tarjetas, que no tienen ciclo de vida propio). El estado de
  // módulo hace que solo salga una petición por sesión.
  fetchAll()

  return { categories, loading, loaded, error, fetchAll, categoryName }
}
