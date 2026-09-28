// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('@/services/adminService', () => ({
  apiGetBanners: vi.fn(),
  apiCreateBanner: vi.fn(),
  apiUpdateBanner: vi.fn(),
  apiDeleteBanner: vi.fn(),
}))

import AdminBannersPage from '@/pages/admin/AdminBannersPage.vue'
import { apiGetBanners, apiCreateBanner, apiUpdateBanner, apiDeleteBanner } from '@/services/adminService'

// Fechas relativas a hoy para que el estado (Activo/Programado/Caducado) no
// dependa del día en que corra el test.
function daysFromToday(n) {
  const d = new Date()
  d.setDate(d.getDate() + n)
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${d.getFullYear()}-${m}-${day}`
}

const BANNERS = [
  { id: 'act', title: 'En ventana', description: 'Se está viendo.', starts_at: daysFromToday(-1), ends_at: daysFromToday(1) },
  { id: 'prog', title: 'Programado', description: 'Todavía no.', starts_at: daysFromToday(3), ends_at: daysFromToday(5) },
  { id: 'cad', title: 'Caducado', description: 'Ya pasó.', starts_at: daysFromToday(-5), ends_at: daysFromToday(-3) },
]

async function mountPage() {
  apiGetBanners.mockResolvedValue(BANNERS.map(b => ({ ...b })))
  const wrapper = mount(AdminBannersPage, { global: { stubs: { RouterLink: true } } })
  await flushPromises()
  return wrapper
}

describe('AdminBannersPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.stubGlobal('confirm', vi.fn(() => true))
    vi.stubGlobal('alert', vi.fn())
  })

  it('lista los banners con su estado calculado contra hoy', async () => {
    const wrapper = await mountPage()

    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(3)
    expect(rows[0].text()).toContain('En ventana')
    expect(rows[0].find('.pill').text()).toBe('Activo')
    expect(rows[1].find('.pill').text()).toBe('Programado')
    expect(rows[2].find('.pill').text()).toBe('Caducado')
  })

  it('crea un banner con título, descripción y fechas', async () => {
    apiCreateBanner.mockResolvedValue({ ok: true })
    const wrapper = await mountPage()

    await wrapper.find('.btn-add').trigger('click')
    await wrapper.find('input[type="text"]').setValue('Nuevo servicio')
    await wrapper.find('textarea').setValue('Ya estamos en toda la península.')
    await wrapper.findAll('input[type="date"]')[0].setValue('2026-10-01')
    await wrapper.findAll('input[type="date"]')[1].setValue('2026-10-31')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(apiCreateBanner).toHaveBeenCalledWith({
      title: 'Nuevo servicio',
      description: 'Ya estamos en toda la península.',
      starts_at: '2026-10-01',
      ends_at: '2026-10-31',
    })
  })

  it('edita un banner rellenando el formulario con lo guardado', async () => {
    apiUpdateBanner.mockResolvedValue({ ok: true })
    const wrapper = await mountPage()

    await wrapper.findAll('.btn-action--edit')[1].trigger('click')
    await wrapper.find('input[type="text"]').setValue('Programado (editado)')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(apiUpdateBanner).toHaveBeenCalledWith('prog', {
      title: 'Programado (editado)',
      description: 'Todavía no.',
      starts_at: daysFromToday(3),
      ends_at: daysFromToday(5),
    })
  })

  it('borra un banner tras confirmar', async () => {
    apiDeleteBanner.mockResolvedValue({ ok: true })
    const wrapper = await mountPage()

    await wrapper.findAll('.btn-action--delete')[2].trigger('click')
    await flushPromises()

    expect(window.confirm).toHaveBeenCalled()
    expect(apiDeleteBanner).toHaveBeenCalledWith('cad')
  })

  it('no borra si se cancela la confirmación', async () => {
    vi.stubGlobal('confirm', vi.fn(() => false))
    const wrapper = await mountPage()

    await wrapper.findAll('.btn-action--delete')[0].trigger('click')
    await flushPromises()

    expect(apiDeleteBanner).not.toHaveBeenCalled()
  })

  it('enseña el error del backend en el formulario', async () => {
    apiCreateBanner.mockRejectedValue(new Error('La fecha de fin no puede ser anterior a la de inicio'))
    const wrapper = await mountPage()

    await wrapper.find('.btn-add').trigger('click')
    await wrapper.find('input[type="text"]').setValue('Título')
    await wrapper.find('textarea').setValue('Descripción')
    await wrapper.findAll('input[type="date"]')[0].setValue('2026-10-31')
    await wrapper.findAll('input[type="date"]')[1].setValue('2026-10-01')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.find('.modal-error').text()).toBe('La fecha de fin no puede ser anterior a la de inicio')
  })
})
