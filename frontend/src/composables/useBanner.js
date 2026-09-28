import { ref } from 'vue'

const BASE = '/api/banner'

// Durante el prerender del build se inyecta un snapshot (window.__DULZIA_PRERENDER__)
// con el banner activo: sin él el HTML prerenderizado saldría sin el aviso.
const snapshot = typeof window !== 'undefined' ? window.__DULZIA_PRERENDER__ : null

// El banner lo pinta una sola barra (App.vue), pero los datos viven a nivel de
// módulo como el resto de composables: se piden una vez por sesión.
// `banner` vale null cuando no hay ninguno en ventana, y entonces no se pinta nada.
const banner = ref(null) // { id, title, description, starts_at, ends_at } | null
const loading = ref(false)
const loaded = ref(false)
const error = ref(null)

let pending = null

export function useBanner() {
  async function fetchBanner({ force = false } = {}) {
    if (snapshot?.banner) {
      banner.value = snapshot.banner
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
        banner.value = (await res.json()).banner ?? null
        loaded.value = true
      } catch (e) {
        // Sin marcar `loaded`: un fallo se reintenta al volver a navegar. Sin
        // banner la barra no pinta nada, así que la web nunca se rompe por esto.
        error.value = e.message
      } finally {
        loading.value = false
        pending = null
      }
    })()

    return pending
  }

  // Se pide solo: la barra está en todas las páginas.
  fetchBanner()

  return { banner, loading, loaded, error, fetchBanner }
}
