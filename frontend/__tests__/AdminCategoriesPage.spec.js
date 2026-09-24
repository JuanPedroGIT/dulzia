// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('@/services/adminService', () => ({
  apiGetCategories: vi.fn(),
  apiCreateCategory: vi.fn(),
  apiUpdateCategory: vi.fn(),
  apiDeleteCategory: vi.fn(),
}))

import AdminCategoriesPage from '@/pages/admin/AdminCategoriesPage.vue'
import { apiGetCategories, apiCreateCategory, apiDeleteCategory } from '@/services/adminService'

const CATEGORIES = [
  { id: 'food', name: 'Gastronomía', emoji: '🍴', sort_order: 0, serviceCount: 4 },
  { id: 'animacion', name: 'Animación', emoji: '🎪', sort_order: 1, serviceCount: 0 },
]

async function mountPage() {
  apiGetCategories.mockResolvedValue(CATEGORIES.map(c => ({ ...c })))
  const wrapper = mount(AdminCategoriesPage)
  await flushPromises()
  return wrapper
}

describe('AdminCategoriesPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.stubGlobal('confirm', vi.fn(() => true))
    vi.stubGlobal('alert', vi.fn())
  })

  it('lista las categorías con su uso', async () => {
    const wrapper = await mountPage()

    expect(wrapper.findAll('tbody tr')).toHaveLength(2)
    expect(wrapper.text()).toContain('Gastronomía')
    expect(wrapper.text()).toContain('food')
    expect(wrapper.text()).toContain('4')
  })

  it('crea una categoría con el nombre, el emoji y el orden', async () => {
    apiCreateCategory.mockResolvedValue({ id: 'nueva', name: 'Nueva' })
    const wrapper = await mountPage()

    await wrapper.find('.btn-add').trigger('click')
    await wrapper.find('input[type="text"]').setValue('Mesa dulce')
    await wrapper.findAll('input[type="text"]')[1].setValue('🍰')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(apiCreateCategory).toHaveBeenCalledWith({
      name: 'Mesa dulce',
      emoji: '🍰',
      // Sin tocar el orden, la nueva va al final de la lista.
      sort_order: 2,
    })
  })

  it('muestra el identificador que se generará del nombre', async () => {
    const wrapper = await mountPage()

    await wrapper.find('.btn-add').trigger('click')
    await wrapper.find('input[type="text"]').setValue('Animación infantil')

    expect(wrapper.find('.form-hint').text()).toContain('animacion-infantil')
  })

  it('borra una categoría tras confirmar', async () => {
    apiDeleteCategory.mockResolvedValue({ ok: true })
    const wrapper = await mountPage()

    await wrapper.findAll('.btn-action--delete')[1].trigger('click')
    await flushPromises()

    expect(window.confirm).toHaveBeenCalled()
    expect(apiDeleteCategory).toHaveBeenCalledWith('animacion')
  })

  it('enseña el aviso del backend al intentar borrar una categoría en uso', async () => {
    apiDeleteCategory.mockRejectedValue(new Error('No se puede borrar: la usan 4 secciones.'))
    const wrapper = await mountPage()

    await wrapper.findAll('.btn-action--delete')[0].trigger('click')
    await flushPromises()

    expect(window.alert).toHaveBeenCalledWith('No se puede borrar: la usan 4 secciones.')
  })

  it('no borra si se cancela la confirmación', async () => {
    vi.stubGlobal('confirm', vi.fn(() => false))
    const wrapper = await mountPage()

    await wrapper.findAll('.btn-action--delete')[0].trigger('click')
    await flushPromises()

    expect(apiDeleteCategory).not.toHaveBeenCalled()
  })
})
