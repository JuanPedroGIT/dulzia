// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('@/services/adminService', () => ({
  apiGetContactRecipient: vi.fn(),
  apiUpdateContactRecipient: vi.fn(),
  apiGetContactDetails: vi.fn(),
  apiUpdateContactDetails: vi.fn(),
}))

import AdminContactSettingsPage from '@/pages/admin/AdminContactSettingsPage.vue'
import {
  apiGetContactRecipient,
  apiUpdateContactRecipient,
  apiGetContactDetails,
  apiUpdateContactDetails,
} from '@/services/adminService'

const RECIPIENT = {
  email: 'avisos@dulzia.es',
  name: 'Dulzia Salamanca Eventos',
  email_source: 'db',
  name_source: 'env',
}

const DETAILS = {
  email: 'hola@dulzia.es',
  phone: '+34 629 991 659',
  email_source: 'db',
  phone_source: 'default',
}

async function mountPage({ recipient = RECIPIENT, details = DETAILS } = {}) {
  apiGetContactRecipient.mockResolvedValue({ ...recipient })
  apiGetContactDetails.mockResolvedValue({ ...details })
  apiUpdateContactRecipient.mockResolvedValue({ ok: true })
  apiUpdateContactDetails.mockResolvedValue({ ok: true })

  const wrapper = mount(AdminContactSettingsPage, {
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
  await flushPromises()

  return wrapper
}

/** Los dos bloques en el orden en que se pintan. */
function cards(wrapper) {
  const [avisos, web] = wrapper.findAll('.settings-card')
  return { avisos, web }
}

describe('AdminContactSettingsPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('separa los avisos internos de los datos que se publican', async () => {
    const wrapper = await mountPage()
    const { avisos, web } = cards(wrapper)

    expect(avisos.text()).toContain('Avisos del formulario')
    expect(avisos.find('input[type=email]').element.value).toBe('avisos@dulzia.es')
    expect(avisos.text()).toContain('Configurado en el panel')

    expect(web.text()).toContain('Datos publicados en la web')
    expect(web.find('input[type=email]').element.value).toBe('hola@dulzia.es')
    expect(web.find('input[type=tel]').element.value).toBe('+34 629 991 659')
  })

  it('dice qué enseña la web cuando un dato público no está configurado', async () => {
    const wrapper = await mountPage({
      details: { email: null, phone: null, email_source: 'default', phone_source: 'default' },
    })
    const { web } = cards(wrapper)

    // El campo va vacío (no se confunde con algo configurado) y el aviso dice
    // qué se está viendo en la web.
    expect(web.find('input[type=email]').element.value).toBe('')
    expect(web.text()).toContain('Sin configurar: la web muestra info@dulziasalamancaeventos.com')
    expect(web.text()).toContain('Sin configurar: la web muestra +34 629 991 659')
  })

  it('guarda los avisos del formulario', async () => {
    const wrapper = await mountPage()
    const { avisos } = cards(wrapper)

    await avisos.find('input[type=email]').setValue('otro@dulzia.es')
    await avisos.trigger('submit')
    await flushPromises()

    expect(apiUpdateContactRecipient).toHaveBeenCalledWith({
      email: 'otro@dulzia.es',
      name: 'Dulzia Salamanca Eventos',
    })
  })

  it('guarda los datos publicados', async () => {
    const wrapper = await mountPage()
    const { web } = cards(wrapper)

    await web.find('input[type=email]').setValue('nuevo@dulzia.es')
    await web.find('input[type=tel]').setValue('600 111 222')
    await web.trigger('submit')
    await flushPromises()

    expect(apiUpdateContactDetails).toHaveBeenCalledWith({
      email: 'nuevo@dulzia.es',
      phone: '600 111 222',
    })
  })

  it('enseña el error del backend si el teléfono no vale', async () => {
    apiUpdateContactDetails.mockRejectedValueOnce(new Error('El teléfono no es válido'))
    const wrapper = await mountPage()
    const { web } = cards(wrapper)

    await web.trigger('submit')
    await flushPromises()

    expect(web.text()).toContain('El teléfono no es válido')
  })
})
