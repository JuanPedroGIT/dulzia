// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import HeroSection from '@/components/features/HeroSection.vue'

const SERVICES = [
  { id: 'carrito-hot-dog', image: 'https://fake-storage.test/services/hotdog.jpg' },
  { id: 'glitter-bar', image: null },
]

function mountHero(services) {
  return mount(HeroSection, {
    props: { services },
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
}

describe('HeroSection — accesos rápidos', () => {
  it('pinta las 6 tarjetas con la foto a sangre y el nombre encima', () => {
    const wrapper = mountHero(SERVICES)
    const cards = wrapper.findAll('.hero__card')

    expect(cards).toHaveLength(6)
    expect(cards[0].find('img.hero__card-img').attributes('src'))
      .toBe('https://fake-storage.test/services/hotdog.jpg')
    expect(cards[0].find('.hero__card-label').text()).toBe('Hot Dog')
    expect(wrapper.find('.hero__card-emoji').exists()).toBe(false)
  })

  it('la tarjeta solo se marca lista cuando su foto se ha cargado', async () => {
    const wrapper = mountHero(SERVICES)
    const card = wrapper.findAll('.hero__card')[0]

    // Sin `--ready` la tarjeta no se pinta (queda el hueco, pero invisible)
    expect(card.classes()).not.toContain('hero__card--ready')

    await card.find('img').trigger('load')

    expect(card.classes()).toContain('hero__card--ready')
  })

  it('las tarjetas sin foto no caen al emoji y no llegan a mostrarse', () => {
    const wrapper = mountHero(SERVICES)
    const card = wrapper.findAll('.hero__card')[1]

    expect(card.find('img').exists()).toBe(false)
    expect(card.find('.hero__card-label').text()).toBe('Glitter Bar')
    expect(card.classes()).not.toContain('hero__card--ready')
  })

  it('sin catálogo cargado mantiene los 6 huecos con sus nombres', () => {
    const wrapper = mountHero([])

    expect(wrapper.findAll('.hero__card')).toHaveLength(6)
    expect(wrapper.findAll('img')).toHaveLength(0)
    expect(wrapper.text()).toContain('Mini Feria')
  })
})
