// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({ useRouter: () => ({ push: vi.fn() }) }))
vi.mock('@/composables/useAuth', () => ({ useAuth: () => ({ logout: vi.fn() }) }))
vi.mock('@/services/adminService', () => ({
  apiGetServices: vi.fn(),
  apiGetService: vi.fn(),
  apiGetMessages: vi.fn(),
  apiCreateService: vi.fn(),
  apiUpdateService: vi.fn(),
  apiDeactivateService: vi.fn(),
  apiActivateService: vi.fn(),
}))

import AdminDashboardPage from '@/pages/admin/AdminDashboardPage.vue'
import BaseFileUpload from '@/components/ui/BaseFileUpload.vue'
import { apiGetServices, apiGetService, apiGetMessages, apiCreateService, apiUpdateService } from '@/services/adminService'

// El listado del panel no incluye description ni features (ver ListServicesHandler);
// el detalle sí. El modal debe leer del detalle.
const LIST_ITEM = { id: 'candy-bar', name: 'Candy Bar', emoji: '🍬', category: 'food', is_active: true, photoCount: 12 }
const DETAIL = {
  id: 'candy-bar',
  name: 'Candy Bar',
  emoji: '🍬',
  category: 'food',
  description: 'Mesa dulce para tu evento',
  features: ['Algodón de azúcar', 'Personal uniformado'],
  imageUrl: 'https://fake-storage.test/services/propia.jpg',
  image: 'https://fake-storage.test/services/propia.jpg',
  photos: [],
}

async function mountPanel(detail = DETAIL) {
  apiGetServices.mockResolvedValue([LIST_ITEM])
  apiGetMessages.mockResolvedValue({ unreadCount: 0 })
  apiGetService.mockResolvedValue(detail)
  const wrapper = mount(AdminDashboardPage, { global: { stubs: { RouterLink: true } } })
  await flushPromises()
  return wrapper
}

async function openEditModal(wrapper) {
  await wrapper.find('.btn-action--edit').trigger('click')
  await flushPromises()
}

describe('AdminDashboardPage — modal de edición de sección', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.stubGlobal('alert', vi.fn()) // jsdom no implementa alert()
  })

  it('abre el modal con la descripción y las características guardadas', async () => {
    const wrapper = await mountPanel()
    await openEditModal(wrapper)

    expect(apiGetService).toHaveBeenCalledWith('candy-bar')
    const areas = wrapper.findAll('textarea')
    expect(areas[0].element.value).toBe('Mesa dulce para tu evento')
    expect(areas[1].element.value).toBe('Algodón de azúcar\nPersonal uniformado')
  })

  it('muestra la foto actual de la sección en el campo de subida', async () => {
    const wrapper = await mountPanel()
    await openEditModal(wrapper)

    const upload = wrapper.findComponent(BaseFileUpload)
    expect(upload.exists()).toBe(true)
    expect(upload.props('currentImage')).toBe('https://fake-storage.test/services/propia.jpg')
  })

  it('al guardar envía el contenido existente, sin vaciarlo', async () => {
    apiUpdateService.mockResolvedValue({ ok: true })
    const wrapper = await mountPanel()
    await openEditModal(wrapper)

    await wrapper.find('form').trigger('submit')
    await flushPromises()

    const [id, payload] = apiUpdateService.mock.calls[0]
    expect(id).toBe('candy-bar')
    expect(payload).toBeInstanceOf(FormData)
    expect(payload.get('name')).toBe('Candy Bar')
    expect(payload.get('description')).toBe('Mesa dulce para tu evento')
    expect(payload.getAll('features[]')).toEqual(['Algodón de azúcar', 'Personal uniformado'])
    expect(payload.get('category')).toBe('food')
    expect(payload.get('image')).toBeNull()
    expect(payload.get('removeImage')).toBeNull()
  })

  it('la X del campo de foto marca la sección para quedarse sin foto propia', async () => {
    apiUpdateService.mockResolvedValue({ ok: true })
    const wrapper = await mountPanel()
    await openEditModal(wrapper)

    // BaseFileUpload emite null al pulsar la X
    wrapper.findComponent(BaseFileUpload).vm.$emit('change', null)
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    const [, payload] = apiUpdateService.mock.calls[0]
    expect(payload.get('removeImage')).toBe('1')
    expect(payload.get('image')).toBeNull()
  })

  it('envía la foto elegida al crear una sección', async () => {
    apiCreateService.mockResolvedValue({ id: 'nueva', name: 'Nueva' })
    const wrapper = await mountPanel()
    await wrapper.find('.btn-primary').trigger('click')
    await flushPromises()

    const file = new File(['x'], 'foto.jpg', { type: 'image/jpeg' })
    wrapper.findComponent(BaseFileUpload).vm.$emit('change', file)
    await wrapper.find('input[type="text"]').setValue('Nueva sección')
    await wrapper.findAll('textarea')[0].setValue('Descripción')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    const [payload] = apiCreateService.mock.calls[0]
    expect(payload).toBeInstanceOf(FormData)
    expect(payload.get('image')).toBe(file)
  })

  it('no abre el formulario si el detalle no se puede cargar', async () => {
    const wrapper = await mountPanel()
    apiGetService.mockRejectedValueOnce(new Error('Error 500'))

    await openEditModal(wrapper)

    expect(wrapper.find('form').exists()).toBe(false)
  })
})
