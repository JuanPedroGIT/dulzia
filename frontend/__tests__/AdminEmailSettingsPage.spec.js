// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('@/services/adminService', () => ({
  apiGetContactRecipient: vi.fn(),
  apiUpdateContactRecipient: vi.fn(),
}))

import AdminEmailSettingsPage from '@/pages/admin/AdminEmailSettingsPage.vue'
import { apiGetContactRecipient, apiUpdateContactRecipient } from '@/services/adminService'

const RECIPIENT = {
  email: 'avisos@dulzia.es',
  name: 'Dulzia Salamanca Eventos',
  email_source: 'db',
  name_source: 'env',
}

async function mountPage(recipient = RECIPIENT) {
  apiGetContactRecipient.mockResolvedValue({ ...recipient })
  apiUpdateContactRecipient.mockResolvedValue({ ok: true })

  const wrapper = mount(AdminEmailSettingsPage, {
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
  await flushPromises()

  return wrapper
}

describe('AdminEmailSettingsPage — avisos del formulario', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('muestra a qué dirección llegan los mensajes y de dónde sale cada campo', async () => {
    const wrapper = await mountPage()

    expect(wrapper.find('input[type=email]').element.value).toBe('avisos@dulzia.es')
    expect(wrapper.find('input[type=text]').element.value).toBe('Dulzia Salamanca Eventos')
    expect(wrapper.text()).toContain('Configurado en el panel')
    expect(wrapper.text()).toContain('Valor por defecto del servidor')
  })

  it('avisa de que esta dirección es interna, no la que se publica', async () => {
    const wrapper = await mountPage()

    expect(wrapper.text()).toContain('no se publica en la web')
  })

  it('vuelve al panel desde la cabecera', async () => {
    const wrapper = await mountPage()

    expect(wrapper.find('.btn-back').text()).toBe('← Panel')
  })

  it('guarda lo que haya en los campos', async () => {
    const wrapper = await mountPage()

    await wrapper.find('input[type=email]').setValue('otro@dulzia.es')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(apiUpdateContactRecipient).toHaveBeenCalledWith({
      email: 'otro@dulzia.es',
      name: 'Dulzia Salamanca Eventos',
    })
  })

  it('enseña el error del backend si el email no vale', async () => {
    apiUpdateContactRecipient.mockRejectedValueOnce(new Error('El email no es válido'))
    const wrapper = await mountPage()

    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('El email no es válido')
  })
})
