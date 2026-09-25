// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import StatsBar from '@/components/features/StatsBar.vue'

function mountStats(props = {}) {
  return mount(StatsBar, { props })
}

// El valor de la tarjeta de servicios, para no confundirlo con los otros números
// de la barra ("500+", "100%"), que llevan ceros.
function servicesValue(wrapper) {
  const item = wrapper
    .findAll('.stats__item')
    .find(i => i.text().includes('Servicios disponibles'))

  return item.find('.stats__value').text()
}

describe('StatsBar — cifras de la barra de la portada', () => {
  it('cuenta los servicios que le pasa la portada, no un número escrito a mano', () => {
    expect(servicesValue(mountStats({ serviceCount: 11 }))).toBe('11')
    expect(servicesValue(mountStats({ serviceCount: 14 }))).toBe('14')
  })

  it('deja el hueco mientras no llega el catálogo, en vez de poner un cero', () => {
    expect(servicesValue(mountStats())).toBe('…')
  })

  it('avisa de que se mueven por toda la península', () => {
    expect(mountStats({ serviceCount: 11 }).text()).toContain('Nos movemos por toda la península')
  })
})
