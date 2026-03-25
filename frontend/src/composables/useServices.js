import { ref } from 'vue'

const BASE = '/api/services'

export function useServices() {
  const services = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchAll() {
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
