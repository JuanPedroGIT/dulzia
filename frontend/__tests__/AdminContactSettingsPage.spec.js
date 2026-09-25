// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('@/services/adminService', () => ({
  apiGetContactDetails: vi.fn(),
  apiUpdateContactDetails: vi.fn(),
}))

import AdminContactSettingsPage from '@/pages/admin/AdminContactSettingsPage.vue'
import { apiGetContactDetails, apiUpdateContactDetails } from '@/services/adminService'

const DETAILS = {
  email: 'hola@dulzia.es',
  phone: '+34 629 991 659',
  email_source: 'db',
  phone_source: 'default',
}

async function mountPage(details = DETAILS) {
  apiGetContactDetails.mockResolvedValue({ ...details })
  apiUpdateContactDetails.mockResolvedValue({ ok: true })

  const wrapper = mount(AdminContactSettingsPage, {
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
  await flushPromises()

  return wrapper
}

describe('AdminContactSettingsPage — datos publicados en la web', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra el email y el teléfono publicados', async () => {
    const wrapper = await mountPage()

    expect(wrapper.find('input[type=email]').element.value).toBe('hola@dulzia.es')
    expect(wrapper.find('input[type=tel]').element.value).toBe('+34 629 991 659')
    expect(wrapper.text()).toContain('Configurado en el panel')
  })

  it('dice qué enseña la web cuando un dato no está configurado', async () => {
    const wrapper = await mountPage({
      email: null,
      phone: null,
      email_source: 'default',
      phone_source: 'default',
    })

    // El campo va vacío (no se confunde con algo configurado) y el aviso dice
    // qué se está viendo en la web.
    expect(wrapper.find('input[type=email]').element.value).toBe('')
    expect(wrapper.text()).toContain('Sin configurar: la web muestra info@dulziasalamancaeventos.com')
    expect(wrapper.text()).toContain('Sin configurar: la web muestra +34 629 991 659')
  })

  it('vuelve al panel desde la cabecera', async () => {
    const wrapper = await mountPage()

    expect(wrapper.find('.btn-back').text()).toBe('← Panel')
  })

  it('guarda lo que haya en los campos', async () => {
    const wrapper = await mountPage()

    await wrapper.find('input[type=email]').setValue('nuevo@dulzia.es')
    await wrapper.find('input[type=tel]').setValue('600 111 222')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(apiUpdateContactDetails).toHaveBeenCalledWith({
      email: 'nuevo@dulzia.es',
      phone: '600 111 222',
    })
  })

  it('enseña el error del backend si el teléfono no vale', async () => {
    apiUpdateContactDetails.mockRejectedValueOnce(new Error('El teléfono no es válido'))
    const wrapper = await mountPage()

    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('El teléfono no es válido')
  })
})
