// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({ useRouter: () => ({ push: vi.fn() }) }))
vi.mock('@/composables/useAuth', () => ({ useAuth: () => ({ logout: vi.fn() }) }))
vi.mock('@/services/adminService', () => ({ apiGetMessages: vi.fn() }))

import AdminDashboardPage from '@/pages/admin/AdminDashboardPage.vue'
import { apiGetMessages } from '@/services/adminService'

const COUNTS = { all: 10, unread: 3, read: 7 }

// El stub deja el destino en el href: sin esto no hay forma de ver a dónde lleva
// cada tarjeta.
const RouterLinkStub = { props: ['to'], template: '<a :href="to"><slot /></a>' }

async function mountPanel(counts = COUNTS) {
  apiGetMessages.mockResolvedValue({ counts: { ...counts } })
  const wrapper = mount(AdminDashboardPage, { global: { stubs: { RouterLink: RouterLinkStub } } })
  await flushPromises()
  return wrapper
}

describe('AdminDashboardPage — índice del panel', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra una tarjeta por zona de edición', async () => {
    const wrapper = await mountPanel()

    expect(wrapper.findAll('.cards .card').map(c => c.find('.card__title').text())).toEqual([
      'Secciones',
      'Categorías',
      'Avisos por email',
      'Datos de contacto',
    ])
  })

  it('cada tarjeta lleva a su página de edición', async () => {
    const wrapper = await mountPanel()

    expect(wrapper.findAll('.cards .card').map(c => c.attributes('href'))).toEqual([
      '/dulzia-panel/servicios',
      '/dulzia-panel/categorias',
      '/dulzia-panel/ajustes-email',
      '/dulzia-panel/ajustes-contacto',
    ])
  })

  it('la tarjeta del buzón va aparte del resto y lleva al listado de mensajes', async () => {
    const wrapper = await mountPanel()

    const inbox = wrapper.find('.card--inbox')
    expect(inbox.attributes('href')).toBe('/dulzia-panel/mensajes')
    // No está dentro de la rejilla de zonas de edición.
    expect(wrapper.findAll('.cards .card--inbox')).toHaveLength(0)
  })

  it('la tarjeta del buzón enseña cuántos mensajes hay sin leer', async () => {
    const wrapper = await mountPanel()

    expect(wrapper.find('.card--inbox .msg-badge').text()).toBe('3 sin leer')
  })

  it('sin mensajes sin leer no lleva contador', async () => {
    const wrapper = await mountPanel({ all: 10, unread: 0, read: 10 })

    expect(wrapper.find('.card--inbox .msg-badge').exists()).toBe(false)
  })
})
