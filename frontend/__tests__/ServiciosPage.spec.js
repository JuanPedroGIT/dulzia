// @vitest-environment jsdom
import { describe, it, expect, vi, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({ useRoute: () => ({ params: { id: 'x' } }) }))

import ServiciosPage from '@/pages/ServiciosPage.vue'
import { useCategories } from '@/composables/useCategories.js'
import { useServices } from '@/composables/useServices.js'

const CATEGORIES = [
  { id: 'food', name: 'Gastronomía', emoji: '🍴', sort_order: 0 },
  { id: 'animacion', name: 'Animación', emoji: null, sort_order: 1 },
]

afterEach(() => {
  vi.unstubAllGlobals()
})

function stubApi() {
  vi.stubGlobal('fetch', vi.fn(async (url) => ({
    ok: true,
    status: 200,
    json: async () => (url === '/api/categories' ? CATEGORIES : []),
  })))
}

describe('ServiciosPage — pestañas', () => {
  it('las pestañas salen de las categorías de la API, con su emoji', async () => {
    stubApi()
    const wrapper = mount(ServiciosPage, {
      global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
    })
    // El catálogo y las categorías viven a nivel de módulo: se piden al montar.
    await useServices().fetchAll()
    await useCategories().fetchAll()
    await flushPromises()

    const tabs = wrapper.findAll('.filter__tab').map(t => t.text())

    expect(tabs).toEqual(['Todos', '🍴 Gastronomía', 'Animación'])
  })
})

describe('ServiciosPage — entradilla', () => {
  it('cuenta los servicios del catálogo, no un número escrito a mano', async () => {
    vi.stubGlobal('fetch', vi.fn(async (url) => ({
      ok: true,
      status: 200,
      json: async () => (url === '/api/categories' ? CATEGORIES : [{ id: 'a' }, { id: 'b' }, { id: 'c' }]),
    })))
    // El catálogo vive a nivel de módulo y el test anterior lo dejó vacío: se
    // fuerza la carga para que el resultado no dependa del orden.
    await useServices().fetchAll({ force: true })

    const wrapper = mount(ServiciosPage, {
      global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
    })

    expect(wrapper.find('.hero-small__sub').text()).toContain('3 servicios únicos')
  })
})
