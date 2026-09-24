// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

const CATEGORIES = [
  { id: 'food', name: 'Gastronomía', emoji: '🍴', sort_order: 0 },
  { id: 'animacion', name: 'Animación', emoji: null, sort_order: 1 },
]

let fetchMock

beforeEach(() => {
  fetchMock = vi.fn(async () => ({ ok: true, status: 200, json: async () => CATEGORIES }))
  vi.stubGlobal('fetch', fetchMock)
})

afterEach(() => {
  vi.unstubAllGlobals()
})

// El estado vive a nivel de módulo: cada test necesita el módulo recién cargado.
async function freshUseCategories() {
  vi.resetModules()
  return (await import('@/composables/useCategories.js')).useCategories
}

describe('useCategories', () => {
  it('pide las categorías una sola vez aunque las usen varios componentes', async () => {
    const useCategories = await freshUseCategories()

    const card = useCategories()
    await card.fetchAll()

    const page = useCategories()
    await page.fetchAll()

    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(fetchMock).toHaveBeenCalledWith('/api/categories')
    expect(page.categories.value).toEqual(CATEGORIES)
  })

  it('se pide sola: quien solo quiere el nombre no tiene que disparar nada', async () => {
    const useCategories = await freshUseCategories()

    useCategories()

    expect(fetchMock).toHaveBeenCalledTimes(1)
  })

  it('traduce el identificador a nombre', async () => {
    const useCategories = await freshUseCategories()

    const { fetchAll, categoryName } = useCategories()
    await fetchAll()

    expect(categoryName('food')).toBe('Gastronomía')
    expect(categoryName('animacion')).toBe('Animación')
  })

  it('si la categoría ya no existe muestra el identificador, no un hueco', async () => {
    const useCategories = await freshUseCategories()

    const { fetchAll, categoryName } = useCategories()
    await fetchAll()

    expect(categoryName('borrada')).toBe('borrada')
    expect(categoryName(undefined)).toBe('')
  })
})
