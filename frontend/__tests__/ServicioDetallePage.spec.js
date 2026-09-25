// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { reactive } from 'vue'
import { mount, flushPromises } from '@vue/test-utils'

// `useRoute` devuelve siempre el mismo objeto reactivo, así que cambiar su id
// simula navegar entre servicios sin desmontar la página (que es lo que hace
// Vue Router al reutilizar el componente).
const mocks = vi.hoisted(() => ({ route: null }))

vi.mock('vue-router', () => ({ useRoute: () => mocks.route }))
vi.mock('@/composables/useSeo.js', () => ({
  useSeo: vi.fn(),
  breadcrumbJsonLd: vi.fn(() => ({})),
  SITE_URL: 'https://dulzia.test',
}))

const CATALOG = [
  {
    id: 'candy-bar', name: 'Candy Bar', emoji: '🍬', description: 'Mesa dulce',
    features: [], category: 'food', image: null, thumbnail: null, examples: [],
  },
  {
    id: 'glitter-bar', name: 'Glitter Bar', emoji: '✨', description: 'Brillo',
    features: [], category: 'food', image: null, thumbnail: null, examples: [],
  },
]

let fetchMock

beforeEach(() => {
  vi.resetModules()
  fetchMock = vi.fn(async () => ({ ok: true, status: 200, json: async () => CATALOG }))
  vi.stubGlobal('fetch', fetchMock)
})

afterEach(() => {
  vi.unstubAllGlobals()
})

async function mountDetail(id) {
  mocks.route = reactive({ params: { id } })

  const { default: ServicioDetallePage } = await import('@/pages/ServicioDetallePage.vue')

  const wrapper = mount(ServicioDetallePage, {
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
  await flushPromises()

  return wrapper
}

describe('ServicioDetallePage — la ficha se sirve del catálogo', () => {
  it('pide solo el catálogo, nunca el detalle del servicio', async () => {
    const wrapper = await mountDetail('candy-bar')

    expect(wrapper.text()).toContain('Candy Bar')
    // Catálogo + categorías (las necesitan las etiquetas de las tarjetas) +
    // datos de contacto (los lleva el banner de CTA del final), y ninguna
    // llamada al detalle del servicio.
    const urls = fetchMock.mock.calls.map(([url]) => url)
    expect(urls).toContain('/api/services')
    expect(urls.every(url => ['/api/services', '/api/categories', '/api/contact'].includes(url))).toBe(true)
  })

  it('navegar a otro servicio no vuelve a pedir nada', async () => {
    const wrapper = await mountDetail('candy-bar')
    const before = fetchMock.mock.calls.length

    mocks.route.params.id = 'glitter-bar'
    await flushPromises()

    expect(wrapper.text()).toContain('Glitter Bar')
    expect(fetchMock.mock.calls.length).toBe(before)
  })

  it('muestra el 404 cuando el id no está en el catálogo', async () => {
    const wrapper = await mountDetail('no-existe')

    expect(wrapper.text()).toContain('Servicio no encontrado')
    expect(fetchMock.mock.calls.some(([url]) => url.startsWith('/api/services/'))).toBe(false)
  })

  it('enseña el 404 solo cuando el catálogo ya ha llegado', async () => {
    let resolveFetch
    fetchMock.mockReturnValue(new Promise(resolve => { resolveFetch = resolve }))

    const wrapper = await mountDetail('candy-bar')

    // Todavía no se sabe si el servicio existe: no se puede afirmar que no exista.
    expect(wrapper.text()).toContain('Cargando…')
    expect(wrapper.text()).not.toContain('Servicio no encontrado')

    resolveFetch({ ok: true, status: 200, json: async () => CATALOG })
    await flushPromises()

    expect(wrapper.text()).toContain('Candy Bar')
  })
})
