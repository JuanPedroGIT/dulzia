// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({ useRouter: () => ({ push: vi.fn() }) }))
vi.mock('@/services/adminService', () => ({
  apiGetServices: vi.fn(),
  apiGetService: vi.fn(),
  apiGetCategories: vi.fn(),
  apiCreateService: vi.fn(),
  apiUpdateService: vi.fn(),
  apiDeactivateService: vi.fn(),
  apiActivateService: vi.fn(),
  apiSetServiceFeatured: vi.fn(),
}))

import AdminServicesPage from '@/pages/admin/AdminServicesPage.vue'
import BaseFileUpload from '@/components/ui/BaseFileUpload.vue'
import { apiGetServices, apiGetService, apiGetCategories, apiCreateService, apiUpdateService, apiSetServiceFeatured } from '@/services/adminService'

// El listado del panel no incluye description ni features (ver ListServicesHandler);
// el detalle sí. El modal debe leer del detalle.
const LIST_ITEM = { id: 'candy-bar', name: 'Candy Bar', emoji: '🍬', category: 'food', is_active: true, featured: false, photoCount: 12 }

// Las categorías ya no están en el código: salen de la tabla (se gestionan en
// /dulzia-panel/categorias).
const CATEGORIES = [
  { id: 'food', name: 'Gastronomía', emoji: '🍴', sort_order: 0, serviceCount: 1 },
  { id: 'animacion', name: 'Animación', emoji: '🎪', sort_order: 1, serviceCount: 0 },
]
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

// El stub deja el destino en el href y pinta el contenido: así se puede leer el
// enlace de vuelta al panel.
const RouterLinkStub = { props: ['to'], template: '<a :href="to"><slot /></a>' }

async function mountPanel(detail = DETAIL) {
  // Copia nueva en cada montaje: el panel muta la fila al cambiar el check de
  // portada, y compartir el literal entre tests los contaminaría.
  apiGetServices.mockResolvedValue([{ ...LIST_ITEM }])
  apiGetService.mockResolvedValue(detail)
  apiGetCategories.mockResolvedValue(CATEGORIES)
  const wrapper = mount(AdminServicesPage, { global: { stubs: { RouterLink: RouterLinkStub } } })
  await flushPromises()
  return wrapper
}

async function openEditModal(wrapper) {
  await wrapper.find('.btn-action--edit').trigger('click')
  await flushPromises()
}

describe('AdminServicesPage — modal de edición de sección', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.stubGlobal('alert', vi.fn()) // jsdom no implementa alert()
  })

  it('vuelve al panel desde la cabecera', async () => {
    const wrapper = await mountPanel()

    expect(wrapper.find('.btn-back').text()).toBe('← Panel')
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

  it('manda la miniatura en la misma petición que la foto', async () => {
    apiCreateService.mockResolvedValue({ id: 'nueva', name: 'Nueva' })
    const wrapper = await mountPanel()
    await wrapper.find('.btn-primary').trigger('click')
    await flushPromises()

    const file = new File(['x'], 'foto.jpg', { type: 'image/jpeg' })
    const thumbnail = new File(['y'], 'foto.jpg', { type: 'image/jpeg' })
    const upload = wrapper.findComponent(BaseFileUpload)
    upload.vm.$emit('change', file)
    upload.vm.$emit('thumbnail', thumbnail)
    await wrapper.find('input[type="text"]').setValue('Nueva sección')
    await wrapper.findAll('textarea')[0].setValue('Descripción')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    const [payload] = apiCreateService.mock.calls[0]
    expect(payload.get('image')).toBe(file)
    expect(payload.get('thumbnail')).toBe(thumbnail)
  })

  it('la X descarta también la miniatura', async () => {
    apiUpdateService.mockResolvedValue({ ok: true })
    const wrapper = await mountPanel()
    await openEditModal(wrapper)

    const upload = wrapper.findComponent(BaseFileUpload)
    upload.vm.$emit('thumbnail', new File(['y'], 'foto.jpg', { type: 'image/jpeg' }))
    upload.vm.$emit('change', null)
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    const [, payload] = apiUpdateService.mock.calls[0]
    expect(payload.get('thumbnail')).toBeNull()
    expect(payload.get('removeImage')).toBe('1')
  })

  it('no abre el formulario si el detalle no se puede cargar', async () => {
    const wrapper = await mountPanel()
    apiGetService.mockRejectedValueOnce(new Error('Error 500'))

    await openEditModal(wrapper)

    expect(wrapper.find('form').exists()).toBe(false)
  })

  it('el desplegable de categorías sale de la tabla, no del código', async () => {
    const wrapper = await mountPanel()
    await openEditModal(wrapper)

    const options = wrapper.findAll('select option').map(o => o.text())
    expect(options).toEqual(['Gastronomía', 'Animación'])
  })
})

describe('AdminServicesPage — destacados en la portada', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.stubGlobal('alert', vi.fn())
  })

  it('el check de la fila marca la sección para la portada', async () => {
    apiSetServiceFeatured.mockResolvedValue({ ok: true })
    const wrapper = await mountPanel()

    await wrapper.find('.featured-check').setValue(true)

    expect(apiSetServiceFeatured).toHaveBeenCalledWith('candy-bar', true)
  })

  it('desmarcar la quita de la portada', async () => {
    apiGetServices.mockResolvedValue([{ ...LIST_ITEM, featured: true }])
    apiGetCategories.mockResolvedValue(CATEGORIES)
    apiSetServiceFeatured.mockResolvedValue({ ok: true })

    const wrapper = mount(AdminServicesPage, { global: { stubs: { RouterLink: RouterLinkStub } } })
    await flushPromises()

    await wrapper.find('.featured-check').setValue(false)

    expect(apiSetServiceFeatured).toHaveBeenCalledWith('candy-bar', false)
  })

  it('si la petición falla, el check vuelve a su sitio', async () => {
    apiSetServiceFeatured.mockRejectedValue(new Error('Error 500'))
    const wrapper = await mountPanel()
    const check = wrapper.find('.featured-check')

    await check.setValue(true)
    await flushPromises()

    expect(check.element.checked).toBe(false)
    expect(window.alert).toHaveBeenCalled()
  })
})
