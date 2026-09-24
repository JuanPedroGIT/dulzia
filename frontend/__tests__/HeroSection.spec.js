// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import HeroSection from '@/components/features/HeroSection.vue'

// El hero pinta las secciones marcadas como destacadas en el panel.
const SERVICES = [
  { id: 'carrito-hot-dog', name: 'Carrito de Perritos', featured: true, image: 'https://fake-storage.test/services/hotdog.jpg' },
  { id: 'glitter-bar', name: 'Glitter Bar', featured: false, image: 'https://fake-storage.test/services/glitter.jpg' },
  { id: 'candy-bar', name: 'Candy Bar', featured: true, image: null },
]

function mountHero(services) {
  return mount(HeroSection, {
    props: { services },
    // El stub pinta el destino para poder comprobar a dónde lleva cada tarjeta.
    global: { stubs: { RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' } } },
  })
}

describe('HeroSection — accesos rápidos', () => {
  it('pinta una tarjeta por servicio destacado, con su nombre', () => {
    const wrapper = mountHero(SERVICES)
    const cards = wrapper.findAll('.hero__card')

    expect(cards).toHaveLength(2)
    expect(cards[0].find('img.hero__card-img').attributes('src'))
      .toBe('https://fake-storage.test/services/hotdog.jpg')
    expect(cards[0].find('.hero__card-label').text()).toBe('Carrito de Perritos')
    expect(cards[1].find('.hero__card-label').text()).toBe('Candy Bar')
  })

  it('no pinta las secciones que no están marcadas', () => {
    const wrapper = mountHero(SERVICES)

    expect(wrapper.text()).not.toContain('Glitter Bar')
  })

  it('corta en 6 tarjetas aunque haya más destacadas', () => {
    const many = Array.from({ length: 8 }, (_, i) => ({
      id: `servicio-${i}`, name: `Servicio ${i}`, featured: true, image: `https://fake-storage.test/services/${i}.jpg`,
    }))

    const wrapper = mountHero(many)

    expect(wrapper.findAll('.hero__card')).toHaveLength(6)
    expect(wrapper.text()).toContain('Servicio 5')
    expect(wrapper.text()).not.toContain('Servicio 6')
  })

  it('la tarjeta solo se marca lista cuando su foto se ha cargado', async () => {
    const wrapper = mountHero(SERVICES)
    const card = wrapper.findAll('.hero__card')[0]

    expect(card.classes()).not.toContain('hero__card--ready')

    await card.find('img').trigger('load')

    expect(card.classes()).toContain('hero__card--ready')
  })

  it('las secciones destacadas sin foto no llegan a mostrarse', () => {
    const wrapper = mountHero(SERVICES)
    const card = wrapper.findAll('.hero__card')[1]

    expect(card.find('img').exists()).toBe(false)
    expect(card.classes()).not.toContain('hero__card--ready')
  })

  it('sin catálogo cargado no hay tarjetas', () => {
    const wrapper = mountHero([])

    expect(wrapper.findAll('.hero__card')).toHaveLength(0)
    expect(wrapper.findAll('img')).toHaveLength(0)
  })

  it('las tarjetas piden la miniatura', () => {
    const wrapper = mountHero([
      {
        id: 'carrito-hot-dog', name: 'Carrito', featured: true,
        image: 'https://fake-storage.test/services/grande.jpg',
        thumbnail: 'https://fake-storage.test/services/mini.jpg',
      },
    ])

    expect(wrapper.find('img.hero__card-img').attributes('src'))
      .toBe('https://fake-storage.test/services/mini.jpg')
  })

  it('cada tarjeta lleva a la ficha de su servicio', () => {
    const wrapper = mountHero(SERVICES)
    const cards = wrapper.findAll('.hero__card')

    expect(cards[0].attributes('href')).toBe('/servicios/carrito-hot-dog')
    expect(cards[1].attributes('href')).toBe('/servicios/candy-bar')
    expect(cards[0].element.tagName).toBe('A')
  })
})
