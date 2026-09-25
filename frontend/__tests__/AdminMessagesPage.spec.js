// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({ useRouter: () => ({ push: vi.fn() }) }))
vi.mock('@/services/adminService', () => ({
  apiGetMessages: vi.fn(),
  apiGetMessage: vi.fn(),
  apiMarkMessageRead: vi.fn(),
  apiDeleteMessage: vi.fn(),
}))

import AdminMessagesPage from '@/pages/admin/AdminMessagesPage.vue'
import { apiGetMessages } from '@/services/adminService'

const RouterLinkStub = { props: ['to'], template: '<a :href="to"><slot /></a>' }

const MESSAGE = {
  id: 'a',
  name: 'María',
  email: 'maria@example.com',
  event_type: 'boda',
  submitted_at: '2026-09-01T10:00:00+00:00',
  is_read: false,
  email_sent: true,
}

const COUNTS = { all: 25, unread: 3, read: 22 }

// El listado responde como el backend: `total` es el del filtro activo y
// `counts` son los tres contadores globales.
async function mountPage() {
  apiGetMessages.mockImplementation(async (page, filter) => ({
    items: filter === 'read' ? [] : [MESSAGE],
    page,
    totalPages: 1,
    total: filter === 'unread' ? 3 : filter === 'read' ? 0 : 25,
    filter,
    counts: { ...COUNTS },
  }))

  const wrapper = mount(AdminMessagesPage, { global: { stubs: { RouterLink: RouterLinkStub } } })
  await flushPromises()

  return wrapper
}

function tabs(wrapper) {
  return wrapper.findAll('.filter-btn')
}

describe('AdminMessagesPage — filtro de leídos y sin leer', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.stubGlobal('alert', vi.fn())
  })

  it('muestra las tres pestañas con sus contadores', async () => {
    const wrapper = await mountPage()

    expect(tabs(wrapper).map(t => t.text().replace(/\s+/g, ' ').trim())).toEqual([
      'Todos 25',
      'Sin leer 3',
      'Leídos 22',
    ])
  })

  it('al entrar enseña todos los mensajes', async () => {
    const wrapper = await mountPage()

    expect(apiGetMessages).toHaveBeenCalledWith(1, 'all')
    expect(tabs(wrapper)[0].classes()).toContain('active')
    expect(wrapper.find('.summary').text()).toBe('25 mensajes · 3 sin leer')
  })

  it('pulsar "Sin leer" pide el listado filtrado', async () => {
    const wrapper = await mountPage()

    await tabs(wrapper)[1].trigger('click')
    await flushPromises()

    expect(apiGetMessages).toHaveBeenLastCalledWith(1, 'unread')
    expect(tabs(wrapper)[1].classes()).toContain('active')
    expect(wrapper.find('.summary').text()).toBe('3 sin leer')
  })

  it('pulsar "Leídos" pide los leídos', async () => {
    const wrapper = await mountPage()

    await tabs(wrapper)[2].trigger('click')
    await flushPromises()

    expect(apiGetMessages).toHaveBeenLastCalledWith(1, 'read')
    expect(wrapper.find('.summary').text()).toBe('0 leídos')
  })

  it('cuando el filtro no tiene mensajes lo dice con el filtro puesto', async () => {
    const wrapper = await mountPage()

    await tabs(wrapper)[2].trigger('click')
    await flushPromises()

    expect(wrapper.find('.messages-table').text()).toContain('No hay mensajes leídos.')
  })

  it('enseña los mensajes sin leer en la cabecera', async () => {
    const wrapper = await mountPage()

    expect(wrapper.find('.header-badge').text()).toBe('3 sin leer')
  })
})
