// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('@/services/adminService', () => ({
  apiCheckToken: vi.fn(),
  apiLogin: vi.fn(),
}))

import AdminLoginPage from '@/pages/admin/AdminLoginPage.vue'
import { apiCheckToken, apiLogin } from '@/services/adminService'

describe('AdminLoginPage — sesión guardada', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
  })

  it('entra directo al panel si el token guardado sigue válido', async () => {
    localStorage.setItem('admin_token', 'token-valido')
    apiCheckToken.mockResolvedValue({ ok: true })

    mount(AdminLoginPage)
    await flushPromises()

    expect(apiCheckToken).toHaveBeenCalled()
    expect(push).toHaveBeenCalledWith('/dulzia-panel')
  })

  it('sin token muestra el formulario sin llamar al servidor', async () => {
    const wrapper = mount(AdminLoginPage)
    await flushPromises()

    expect(apiCheckToken).not.toHaveBeenCalled()
    expect(wrapper.find('#username').exists()).toBe(true)
  })

  it('con el token caducado lo borra y pide credenciales', async () => {
    localStorage.setItem('admin_token', 'token-caducado')
    apiCheckToken.mockRejectedValue(new Error('401'))

    const wrapper = mount(AdminLoginPage)
    await flushPromises()

    expect(localStorage.getItem('admin_token')).toBeNull()
    expect(wrapper.find('#username').exists()).toBe(true)
    expect(push).not.toHaveBeenCalled()
  })

  it('si el check falla por otro motivo muestra el formulario y conserva el token', async () => {
    localStorage.setItem('admin_token', 'token-valido')
    apiCheckToken.mockRejectedValue(new Error('Error 500'))

    const wrapper = mount(AdminLoginPage)
    await flushPromises()

    expect(wrapper.find('#username').exists()).toBe(true)
    expect(localStorage.getItem('admin_token')).toBe('token-valido')
    expect(push).not.toHaveBeenCalled()
  })

  it('muestra el aviso de comprobación mientras el check está en vuelo', async () => {
    localStorage.setItem('admin_token', 'token-valido')
    let resolveCheck
    apiCheckToken.mockReturnValueOnce(new Promise(resolve => { resolveCheck = resolve }))

    const wrapper = mount(AdminLoginPage)
    await flushPromises()
    expect(wrapper.text()).toContain('Comprobando sesión…')
    expect(wrapper.find('form').exists()).toBe(false)

    resolveCheck({ ok: true })
    await flushPromises()
  })

  it('hace login con las credenciales del formulario', async () => {
    apiLogin.mockResolvedValue({ token: 'nuevo-token' })

    const wrapper = mount(AdminLoginPage)
    await wrapper.find('#username').setValue('admin')
    await wrapper.find('#password').setValue('secreto')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(apiLogin).toHaveBeenCalledWith('admin', 'secreto')
    expect(localStorage.getItem('admin_token')).toBe('nuevo-token')
    expect(push).toHaveBeenCalledWith('/dulzia-panel')
  })
})
