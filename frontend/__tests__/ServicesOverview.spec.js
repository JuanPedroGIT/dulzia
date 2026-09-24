// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ServicesOverview from '@/components/features/ServicesOverview.vue'

const SERVICES = [
  { id: 'carrito-hot-dog', name: 'Carrito de Perritos', featured: true, category: 'food', features: [] },
  { id: 'palomitero', name: 'Palomitero', featured: false, category: 'food', features: [] },
  { id: 'candy-bar', name: 'Candy Bar', featured: true, category: 'food', features: [] },
]

function mountOverview(services) {
  return mount(ServicesOverview, {
    props: { services },
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
}

describe('ServicesOverview — parrilla de la portada', () => {
  it('muestra solo las secciones destacadas', () => {
    const wrapper = mountOverview(SERVICES)
    const names = wrapper.findAll('.card__name').map(n => n.text())

    expect(names).toEqual(['Carrito de Perritos', 'Candy Bar'])
  })

  it('el CTA cuenta el catálogo entero, no solo las destacadas', () => {
    const wrapper = mountOverview(SERVICES)

    expect(wrapper.text()).toContain('Ver todos los servicios (3)')
  })

  it('no se pinta si no hay ninguna sección destacada', () => {
    const wrapper = mountOverview([{ id: 'palomitero', name: 'Palomitero', featured: false }])

    expect(wrapper.find('.section').exists()).toBe(false)
    expect(wrapper.text()).toBe('')
  })
})
