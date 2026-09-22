import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useMessages } from '../src/composables/useMessages.js'

vi.mock('../src/services/adminService.js', () => ({
  apiGetMessages: vi.fn(),
  apiGetMessage: vi.fn(),
  apiMarkMessageRead: vi.fn(),
  apiDeleteMessage: vi.fn(),
}))

import { apiGetMessages, apiMarkMessageRead, apiDeleteMessage } from '../src/services/adminService.js'

describe('useMessages', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('fetchPage loads items, pagination and counts', async () => {
    apiGetMessages.mockResolvedValueOnce({
      items: [{ id: 'a', name: 'María', is_read: false }],
      page: 1,
      totalPages: 2,
      total: 21,
      unreadCount: 3,
    })

    const { messages, page, totalPages, total, unreadCount, fetchPage } = useMessages()
    await fetchPage()

    expect(messages.value).toHaveLength(1)
    expect(messages.value[0].name).toBe('María')
    expect(page.value).toBe(1)
    expect(totalPages.value).toBe(2)
    expect(total.value).toBe(21)
    expect(unreadCount.value).toBe(3)
  })

  it('sets error when fetchPage fails', async () => {
    apiGetMessages.mockRejectedValueOnce(new Error('Error 500'))

    const { error, fetchPage } = useMessages()
    await fetchPage()

    expect(error.value).toBe('Error 500')
  })

  it('markRead updates the row and decrements the unread counter', async () => {
    apiGetMessages.mockResolvedValueOnce({
      items: [{ id: 'a', name: 'María', is_read: false }],
      page: 1, totalPages: 1, total: 1, unreadCount: 1,
    })
    apiMarkMessageRead.mockResolvedValueOnce({ ok: true })

    const { messages, unreadCount, fetchPage, markRead } = useMessages()
    await fetchPage()
    await markRead('a', true)

    expect(apiMarkMessageRead).toHaveBeenCalledWith('a', true)
    expect(messages.value[0].is_read).toBe(true)
    expect(unreadCount.value).toBe(0)
  })

  it('markRead does not change the counter when the row is not visible', async () => {
    apiMarkMessageRead.mockResolvedValueOnce({ ok: true })

    const { unreadCount, markRead } = useMessages()
    unreadCount.value = 5
    await markRead('no-en-pagina', true)

    expect(unreadCount.value).toBe(5)
  })

  it('remove deletes and refetches the page', async () => {
    apiGetMessages
      .mockResolvedValueOnce({ items: [{ id: 'a' }], page: 1, totalPages: 1, total: 1, unreadCount: 1 })
      .mockResolvedValueOnce({ items: [], page: 1, totalPages: 1, total: 0, unreadCount: 0 })
    apiDeleteMessage.mockResolvedValueOnce({ ok: true })

    const { fetchPage, remove } = useMessages()
    await fetchPage()
    await remove('a')

    expect(apiDeleteMessage).toHaveBeenCalledWith('a')
    expect(apiGetMessages).toHaveBeenCalledTimes(2)
  })
})
