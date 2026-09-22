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
  const unreadCount = ref(0)

  async function fetchPage(targetPage = page.value) {
    loading.value = true
    error.value = null
    try {
      const data = await apiGetMessages(targetPage)
      messages.value    = data.items
      page.value        = data.page
      totalPages.value  = data.totalPages
      total.value       = data.total
      unreadCount.value = data.unreadCount
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
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
    if (changed) unreadCount.value = Math.max(0, unreadCount.value + (read ? -1 : 1))
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
    page, totalPages, total, unreadCount,
    fetchPage, fetchOne, markRead, remove,
  }
}
