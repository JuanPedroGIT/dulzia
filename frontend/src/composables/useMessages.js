import { ref } from 'vue'
import { apiGetMessages, apiGetMessage, apiMarkMessageRead, apiDeleteMessage } from '@/services/adminService.js'

export function formatDateTime(iso) {
  return new Date(iso).toLocaleString('es-ES', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

export function useMessages() {
  const messages    = ref([])
  const message     = ref(null)
  const loading     = ref(false)
  const error       = ref(null)
  const page        = ref(1)
  const totalPages  = ref(1)
  const total       = ref(0)
  const filter      = ref('all') // 'all' | 'unread' | 'read'
  // Contadores globales: no dependen del filtro, son las pestañas. `total` en
  // cambio es el del filtro activo, que es lo que pagina la tabla.
  const counts      = ref({ all: 0, unread: 0, read: 0 })

  async function fetchPage(targetPage = page.value) {
    loading.value = true
    error.value = null
    try {
      const data = await apiGetMessages(targetPage, filter.value)
      messages.value   = data.items
      page.value       = data.page
      totalPages.value = data.totalPages
      total.value      = data.total
      counts.value     = data.counts ?? { all: 0, unread: 0, read: 0 }
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  /**
   * Cambia de pestaña y vuelve a la primera página: la 3 del filtro anterior no
   * tiene por qué existir en el nuevo.
   */
  async function setFilter(next) {
    if (filter.value === next) return
    filter.value = next
    await fetchPage(1)
  }

  async function fetchOne(id) {
    loading.value = true
    error.value = null
    try {
      message.value = await apiGetMessage(id)
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function markRead(id, read) {
    // El contador global solo se ajusta si la fila visible cambia de estado;
    // si no está en la página actual, se actualiza con el siguiente fetchPage.
    const row = messages.value.find(m => m.id === id)
    const changed = row ? row.is_read !== read : false
    await apiMarkMessageRead(id, read)
    if (row) row.is_read = read
    if (message.value?.id === id) message.value.is_read = read
    if (changed) {
      const unread = Math.max(0, counts.value.unread + (read ? -1 : 1))
      counts.value = { all: counts.value.all, unread, read: counts.value.all - unread }
    }
    // Con un filtro activo la fila puede dejar de cumplirlo ("sin leer" y acabo
    // de marcarlo leído): se relee para que desaparezca y cuadren los totales.
    if (filter.value !== 'all') await fetchPage(page.value)
  }

  async function remove(id) {
    await apiDeleteMessage(id)
    if (message.value?.id === id) message.value = null
    await fetchPage(page.value)
    // Si la página quedó vacía y no es la primera, retrocede una página
    if (messages.value.length === 0 && page.value > 1) {
      await fetchPage(page.value - 1)
    }
  }

  return {
    messages, message, loading, error,
    page, totalPages, total, filter, counts,
    fetchPage, setFilter, fetchOne, markRead, remove,
  }
}
