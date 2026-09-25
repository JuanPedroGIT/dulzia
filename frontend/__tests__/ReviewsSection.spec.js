// @vitest-environment jsdom
import { describe, it, expect, vi, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ReviewsSection from '@/components/features/ReviewsSection.vue'

// El "hace 2 meses" se calcula al pintar, así que se fija el reloj: si no, el
// test cambiaría de resultado con el paso del tiempo.
function mountAt(isoDay) {
  vi.useFakeTimers()
  vi.setSystemTime(new Date(`${isoDay}T12:00:00`))

  const wrapper = mount(ReviewsSection)

  vi.useRealTimers()

  return wrapper
}

function dates(wrapper) {
  return wrapper.findAll('.reviews__date').map(node => node.text())
}

afterEach(() => {
  vi.useRealTimers()
})

describe('ReviewsSection — reseñas de la portada', () => {
  it('pinta las reseñas con su inicial, su texto y sus estrellas', () => {
    const wrapper = mountAt('2026-09-25')
    const cards = wrapper.findAll('.reviews__card')

    // Se enseñan tres: las de Mayte y Raquel están comentadas en el componente.
    expect(cards).toHaveLength(3)
    expect(cards[0].find('.reviews__name').text()).toBe('Taly Hdez Luengo')
    expect(cards[0].find('.reviews__avatar').text()).toBe('T')
    expect(cards[0].find('.reviews__text').text()).toContain('la recena de nuestra boda')
    expect(cards[0].find('.reviews__card-stars').text()).toBe('★★★★★')
    expect(cards[0].find('.reviews__card-stars').attributes('aria-label')).toBe('5 de 5 estrellas')
  })

  it('las estrellas que se ven llevan su equivalente en texto', () => {
    const wrapper = mountAt('2026-09-25')

    expect(wrapper.find('.reviews__card-stars').attributes('aria-label')).toBe('5 de 5 estrellas')
  })

  it('la antigüedad sale de la fecha de cada reseña', () => {
    const wrapper = mountAt('2026-09-25')

    expect(dates(wrapper)).toEqual(['hace 2 meses', 'hace 3 meses', 'hace 3 meses'])
  })

  it('envejece sola: la misma reseña pasa a meses, a un año y a años', () => {
    expect(dates(mountAt('2026-08-25'))[0]).toBe('hace 1 mes')
    expect(dates(mountAt('2027-09-25'))[0]).toBe('hace 1 año')
    expect(dates(mountAt('2029-09-25'))[0]).toBe('hace 3 años')
  })

  it('por debajo del mes cuenta días, no "hace 0 meses"', () => {
    expect(dates(mountAt('2026-08-01'))[0]).toBe('hace 7 días')
  })

  it('enseña la nota media y cuántas reseñas tiene el perfil', () => {
    const wrapper = mountAt('2026-09-25')

    expect(wrapper.find('.reviews__score-num').text()).toBe('4,9')
    expect(wrapper.find('.reviews__score-count').text()).toBe('(146 reseñas)')
  })

  it('el enlace lleva al perfil de Google y abre fuera de la web', () => {
    const wrapper = mountAt('2026-09-25')
    const link = wrapper.find('.reviews__cta a')

    expect(link.attributes('href')).toBe('https://maps.app.goo.gl/MYuXG2xSAJKYDhzM8')
    expect(link.attributes('target')).toBe('_blank')
    expect(link.attributes('rel')).toContain('noopener')
    // Tamaño medio: con el `lg` el texto no cabía en un móvil y se salía.
    expect(link.classes()).toContain('btn--md')
  })
})
