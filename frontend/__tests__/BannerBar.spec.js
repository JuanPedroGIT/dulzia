// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const ACTIVE = { id: 'b1', title: 'Nuevo servicio', description: 'Ya estamos en toda la península.', starts_at: '2026-10-01', ends_at: '2026-10-31' }

let fetchMock

beforeEach(() => {
  sessionStorage.clear()
  fetchMock = vi.fn(async () => ({ ok: true, status: 200, json: async () => ({ banner: ACTIVE }) }))
  vi.stubGlobal('fetch', fetchMock)
  // jsdom no trae ResizeObserver: con un stub que no hace nada basta (la barra
  // solo lo usa para publicar su altura).
  vi.stubGlobal('ResizeObserver', class {
    observe() {}
    unobserve() {}
    disconnect() {}
  })
})

afterEach(() => {
  vi.unstubAllGlobals()
  sessionStorage.clear()
})

// El banner vive a nivel de módulo: cada test monta con módulos recién cargados.
async function mountBar() {
  vi.resetModules()
  const { default: BannerBar } = await import('@/components/layout/BannerBar.vue')
  const wrapper = mount(BannerBar)
  await flushPromises()
  return wrapper
}

describe('BannerBar — aviso arriba del todo', () => {
  it('no pinta nada sin banner en ventana', async () => {
    fetchMock.mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ banner: null }) })
    const wrapper = await mountBar()

    expect(wrapper.find('.banner-bar').exists()).toBe(false)
  })

  it('pinta el título y la descripción', async () => {
    const wrapper = await mountBar()

    expect(wrapper.find('.banner-bar__title').text()).toBe('Nuevo servicio')
    expect(wrapper.find('.banner-bar__description').text()).toBe('Ya estamos en toda la península.')
  })

  it('al cerrar guarda el id en sessionStorage y desaparece', async () => {
    const wrapper = await mountBar()

    await wrapper.find('.banner-bar__close').trigger('click')

    expect(sessionStorage.getItem('banner-cerrado')).toBe('b1')
    expect(wrapper.find('.banner-bar').exists()).toBe(false)
  })

  it('no reaparece si el id ya está guardado como cerrado', async () => {
    sessionStorage.setItem('banner-cerrado', 'b1')
    const wrapper = await mountBar()

    expect(wrapper.find('.banner-bar').exists()).toBe(false)
  })

  it('vuelve a salir con un banner distinto al cerrado', async () => {
    sessionStorage.setItem('banner-cerrado', 'b-otro')
    const wrapper = await mountBar()

    expect(wrapper.find('.banner-bar').exists()).toBe(true)
  })

  it('devuelve --banner-h a 0 si la barra nace oculta (regresión del hueco en blanco)', async () => {
    // El HTML horneado trae la variable a la altura de la barra; si el banner
    // ya está cerrado en la sesión, al montar hay que devolverla a 0 o el nav
    // queda desplazado con un hueco de 64 px encima.
    document.documentElement.style.setProperty('--banner-h', '64px')
    sessionStorage.setItem('banner-cerrado', 'b1')
    const wrapper = await mountBar()

    expect(wrapper.find('.banner-bar').exists()).toBe(false)
    expect(document.documentElement.style.getPropertyValue('--banner-h')).toBe('0px')
  })
})
