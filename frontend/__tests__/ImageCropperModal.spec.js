// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import ImageCropperModal from '@/components/ui/ImageCropperModal.vue'

// jsdom no implementa canvas (getContext devuelve null y toBlob no existe), así
// que se doblan: cada exportación queda registrada con su tamaño y su calidad.
const exports = []
let originalGetContext
let originalToBlob

beforeEach(() => {
  exports.length = 0

  originalGetContext = HTMLCanvasElement.prototype.getContext
  originalToBlob = HTMLCanvasElement.prototype.toBlob

  HTMLCanvasElement.prototype.getContext = vi.fn(() => ({
    fillStyle: '',
    fillRect: vi.fn(),
    drawImage: vi.fn(),
  }))
  HTMLCanvasElement.prototype.toBlob = function (callback, type, quality) {
    exports.push({ width: this.width, height: this.height, type, quality })
    callback(new Blob(['x'], { type }))
  }
})

afterEach(() => {
  HTMLCanvasElement.prototype.getContext = originalGetContext
  HTMLCanvasElement.prototype.toBlob = originalToBlob
})

/**
 * Monta el recortador simulando un contenedor de 560×420 (el máximo real en
 * escritorio) y una foto de `naturalW`×`naturalH` píxeles ya cargada.
 */
async function mountCropper({ naturalW = 4000, naturalH = 3000 } = {}) {
  const wrapper = mount(ImageCropperModal, {
    props: { show: true, imageSrc: 'blob:foto' },
    attachTo: document.body,
  })

  const container = wrapper.find('.cropper-modal__body')
  Object.defineProperty(container.element, 'clientWidth', { value: 560, configurable: true })
  Object.defineProperty(container.element, 'clientHeight', { value: 420, configurable: true })

  const img = wrapper.find('img.cropper-img')
  Object.defineProperty(img.element, 'naturalWidth', { value: naturalW, configurable: true })
  Object.defineProperty(img.element, 'naturalHeight', { value: naturalH, configurable: true })
  await img.trigger('load')

  return wrapper
}

async function confirmCrop(wrapper) {
  await wrapper.find('.btn-confirm').trigger('click')
  await flushPromises()
  return wrapper.emitted('confirm')[0][0]
}

describe('ImageCropperModal — exportación', () => {
  it('sube una foto grande de 1600 px y una miniatura de 640 px', async () => {
    const wrapper = await mountCropper()
    const { full, thumbnail } = await confirmCrop(wrapper)

    expect(full).toBeInstanceOf(Blob)
    expect(thumbnail).toBeInstanceOf(Blob)
    expect(exports).toHaveLength(2)
    expect(exports[0]).toMatchObject({ width: 1600, height: 1200, type: 'image/jpeg' })
    expect(exports[1]).toMatchObject({ width: 640, height: 480, type: 'image/jpeg' })
  })

  it('la miniatura sale con más compresión que la grande', async () => {
    const wrapper = await mountCropper()
    await confirmCrop(wrapper)

    expect(exports[0].quality).toBeGreaterThan(exports[1].quality)
  })

  it('una foto pequeña no se amplía por encima de su resolución real', async () => {
    const wrapper = await mountCropper({ naturalW: 800, naturalH: 600 })
    await confirmCrop(wrapper)

    // El recorte a escala 1 cubre los 800 px reales: ni 1600 ni 640 los superan.
    expect(exports[0]).toMatchObject({ width: 800, height: 600 })
    expect(exports[1]).toMatchObject({ width: 640, height: 480 })
  })

  it('nunca exporta más píxeles de los que tiene el recorte real', async () => {
    const wrapper = await mountCropper()

    // A zoom 1:1 el recorte cubre exactamente los 560 px del contenedor: ni la
    // foto grande (1600) ni la miniatura (640) se pueden inventar.
    await wrapper.find('input[type="range"]').setValue(1)
    await confirmCrop(wrapper)

    expect(exports[0]).toMatchObject({ width: 560, height: 420 })
    expect(exports[1]).toMatchObject({ width: 560, height: 420 })
  })

  it('no vuelve a exportar si se pulsa dos veces seguidas', async () => {
    const wrapper = await mountCropper()

    // Doble clic real: los dos pulsados caen en el mismo tick.
    const button = wrapper.find('.btn-confirm')
    button.element.click()
    button.element.click()
    await flushPromises()

    expect(exports).toHaveLength(2)
    expect(wrapper.emitted('confirm')).toHaveLength(1)
  })
})
