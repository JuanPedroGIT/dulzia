import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useMessages } from '../src/composables/useMessages.js'

vi.mock('../src/services/adminService.js', () => ({
  apiGetMessages: vi.fn(),
  apiGetMessage: vi.fn(),
  apiMarkMessageRead: vi.fn(),
  apiDeleteMessage: vi.fn(),
}))

import { apiGetMessages, apiMarkMessageRead, apiDeleteMessage } from '../src/services/adminService.js'

const COUNTS = { all: 25, unread: 3, read: 22 }

function page(overrides = {}) {
  return {
    items: [{ id: 'a', name: 'María', is_read: false }],
    page: 1,
    totalPages: 2,
    total: 21,
    filter: 'all',
    counts: { ...COUNTS },
    ...overrides,
  }
}

describe('useMessages', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('fetchPage loads items, pagination, filter and counts', async () => {
    apiGetMessages.mockResolvedValueOnce(page())

    const { messages, page: currentPage, totalPages, total, filter, counts, fetchPage } = useMessages()
    await fetchPage()

    expect(messages.value).toHaveLength(1)
    expect(messages.value[0].name).toBe('María')
    expect(currentPage.value).toBe(1)
    expect(totalPages.value).toBe(2)
    expect(total.value).toBe(21)
    expect(filter.value).toBe('all')
    expect(counts.value).toEqual(COUNTS)
    // Sin filtro se pide el listado completo.
    expect(apiGetMessages).toHaveBeenCalledWith(1, 'all')
  })

  it('setFilter pide el listado filtrado desde la primera página', async () => {
    apiGetMessages.mockResolvedValue(page({ items: [], total: 3, filter: 'unread' }))
    const { filter, page: currentPage, total, setFilter, fetchPage } = useMessages()

    await fetchPage(3) // el usuario estaba en la página 3
    await setFilter('unread')

    expect(apiGetMessages).toHaveBeenLastCalledWith(1, 'unread')
    expect(filter.value).toBe('unread')
    expect(currentPage.value).toBe(1)
    // El total pasa a ser el del filtro (lo que pagina la tabla).
    expect(total.value).toBe(3)
  })

  it('setFilter no repite la petición si ya está en ese filtro', async () => {
    apiGetMessages.mockResolvedValue(page())
    const { setFilter, fetchPage } = useMessages()

    await fetchPage()
    await setFilter('all')

    expect(apiGetMessages).toHaveBeenCalledTimes(1)
  })

  it('sets error when fetchPage fails', async () => {
    apiGetMessages.mockRejectedValueOnce(new Error('Error 500'))

    const { error, fetchPage } = useMessages()
    await fetchPage()

    expect(error.value).toBe('Error 500')
  })

  it('markRead updates the row and the counters', async () => {
    apiGetMessages.mockResolvedValueOnce(page({ total: 1, totalPages: 1, counts: { all: 1, unread: 1, read: 0 } }))
    apiMarkMessageRead.mockResolvedValueOnce({ ok: true })

    const { messages, counts, fetchPage, markRead } = useMessages()
    await fetchPage()
    await markRead('a', true)

    expect(apiMarkMessageRead).toHaveBeenCalledWith('a', true)
    expect(messages.value[0].is_read).toBe(true)
    expect(counts.value).toEqual({ all: 1, unread: 0, read: 1 })
  })

  it('markRead does not change the counters when the row is not visible', async () => {
    apiMarkMessageRead.mockResolvedValueOnce({ ok: true })

    const { counts, markRead } = useMessages()
    counts.value = { ...COUNTS }
    await markRead('no-en-pagina', true)

    expect(counts.value).toEqual(COUNTS)
  })

  it('con el filtro "sin leer" puesto, marcar leído relee para que la fila desaparezca', async () => {
    apiGetMessages.mockResolvedValue(page({ items: [{ id: 'a', is_read: false }], filter: 'unread' }))
    apiMarkMessageRead.mockResolvedValueOnce({ ok: true })

    const { setFilter, markRead } = useMessages()
    await setFilter('unread')
    expect(apiGetMessages).toHaveBeenCalledTimes(1)

    await markRead('a', true)

    // La fila ya no cumple el filtro: se vuelve a pedir la página.
    expect(apiGetMessages).toHaveBeenCalledTimes(2)
    expect(apiGetMessages).toHaveBeenLastCalledWith(1, 'unread')
  })

  it('remove deletes and refetches the page', async () => {
    apiGetMessages
      .mockResolvedValueOnce(page({ items: [{ id: 'a' }], total: 1, totalPages: 1 }))
      .mockResolvedValueOnce(page({ items: [], total: 0, totalPages: 1 }))
    apiDeleteMessage.mockResolvedValueOnce({ ok: true })

    const { fetchPage, remove } = useMessages()
    await fetchPage()
    await remove('a')

    expect(apiDeleteMessage).toHaveBeenCalledWith('a')
    expect(apiGetMessages).toHaveBeenCalledTimes(2)
  })
})
