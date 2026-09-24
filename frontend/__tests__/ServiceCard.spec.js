// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ServiceCard from '@/components/features/ServiceCard.vue'

const SERVICE = {
  id: 'candy-bar',
  name: 'Candy Bar',
  emoji: '🍬',
  category: 'food',
  description: 'Mesa dulce',
  features: [],
}

function mountCard(overrides = {}) {
  return mount(ServiceCard, {
    props: { service: { ...SERVICE, ...overrides } },
    // El stub tiene que pintar el slot: el contenido de la tarjeta va dentro.
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
  })
}

describe('ServiceCard — imagen de la sección', () => {
  it('muestra la foto cuando el servicio tiene imagen', () => {
    const wrapper = mountCard({ image: 'https://fake-storage.test/services/propia.jpg' })

    const img = wrapper.find('img.card__img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('https://fake-storage.test/services/propia.jpg')
    expect(img.attributes('alt')).toBe('Candy Bar')
    expect(wrapper.find('.card__emoji').exists()).toBe(false)
  })

  it('cae al emoji cuando no hay imagen', () => {
    const wrapper = mountCard({ image: null })

    expect(wrapper.find('img.card__img').exists()).toBe(false)
    expect(wrapper.find('.card__emoji').text()).toBe('🍬')
  })

  it('deja el hueco vacío si no hay ni imagen ni emoji', () => {
    const wrapper = mountCard({ image: null, emoji: '' })

    expect(wrapper.find('img.card__img').exists()).toBe(false)
    expect(wrapper.find('.card__emoji').text()).toBe('')
    expect(wrapper.find('.card__name').text()).toBe('Candy Bar')
  })
})
