import { ref } from 'vue'

const BASE = '/api/services'

// Durante el prerender del build se inyecta un snapshot del catálogo
// (window.__DULZIA_PRERENDER__): las páginas se generan con datos reales sin
// backend. En el navegador normal esa variable no existe y se usa la API.
const snapshot = typeof window !== 'undefined' ? window.__DULZIA_PRERENDER__ : null

export function useServices() {
  const services = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchAll() {
    if (snapshot?.list) {
      services.value = snapshot.list
      return
    }
    loading.value = true
    error.value = null
    try {
      const res = await fetch(BASE)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      services.value = await res.json()
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return { services, loading, error, fetchAll }
}

export function useService(id) {
  const service = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchOne(overrideId) {
    const resolvedId = overrideId ?? (typeof id === 'object' ? id.value : id)
    if (snapshot?.details) {
      service.value = snapshot.details[resolvedId] ?? null
      return
    }
    loading.value = true
    error.value = null
    try {
      const res = await fetch(`${BASE}/${resolvedId}`)
      if (res.status === 404) { service.value = null; return }
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      service.value = await res.json()
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return { service, loading, error, fetchOne }
}
