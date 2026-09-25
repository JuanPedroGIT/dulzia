// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

let fetchMock

beforeEach(() => {
  // Los datos de contacto viven a nivel de módulo: módulo nuevo en cada test.
  vi.resetModules()
  fetchMock = vi.fn(async () => ({
    ok: true,
    status: 200,
    json: async () => ({ email: 'hola@dulzia.es', phone: '629 000 111' }),
  }))
  vi.stubGlobal('fetch', fetchMock)
})

afterEach(() => {
  vi.unstubAllGlobals()
})

async function mountFooter() {
  const { default: AppFooter } = await import('@/components/layout/AppFooter.vue')

  const wrapper = mount(AppFooter, {
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
  await flushPromises()

  return wrapper
}

describe('AppFooter — datos de contacto', () => {
  it('muestra el teléfono y el email configurados en el panel', async () => {
    const wrapper = await mountFooter()

    expect(wrapper.text()).toContain('629 000 111')
    expect(wrapper.text()).toContain('hola@dulzia.es')
  })

  it('los enlaces de llamada y WhatsApp salen del mismo teléfono', async () => {
    const wrapper = await mountFooter()

    const hrefs = wrapper.findAll('a').map(a => a.attributes('href'))
    expect(hrefs).toContain('tel:+629000111')
    expect(hrefs).toContain('https://wa.me/629000111')
    expect(hrefs).toContain('mailto:hola@dulzia.es')
  })

  it('si la API falla sigue enseñando los datos de siempre', async () => {
    fetchMock.mockResolvedValue({ ok: false, status: 500 })

    const wrapper = await mountFooter()

    expect(wrapper.text()).toContain('+34 629 991 659')
    expect(wrapper.text()).toContain('info@dulziasalamancaeventos.com')
  })
})
