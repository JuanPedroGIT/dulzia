// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

const CATALOG = [{ id: 'candy-bar', name: 'Candy Bar' }]

let fetchMock

beforeEach(() => {
  fetchMock = vi.fn(async () => ({ ok: true, status: 200, json: async () => CATALOG }))
  vi.stubGlobal('fetch', fetchMock)
})

afterEach(() => {
  vi.unstubAllGlobals()
})

// El catálogo vive a nivel de módulo, así que cada test necesita el módulo recién
// cargado para partir de cero.
async function freshUseServices() {
  vi.resetModules()
  return (await import('@/composables/useServices.js')).useServices
}

describe('useServices — catálogo compartido', () => {
  it('pide el catálogo una sola vez aunque lo usen varias páginas', async () => {
    const useServices = await freshUseServices()

    const home = useServices()
    await home.fetchAll()

    const detail = useServices()
    await detail.fetchAll()

    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(fetchMock).toHaveBeenCalledWith('/api/services')
    expect(detail.services.value).toEqual(CATALOG)
    expect(detail.loaded.value).toBe(true)
  })

  it('dos páginas que montan a la vez comparten una única petición', async () => {
    const useServices = await freshUseServices()

    const first = useServices()
    const second = useServices()
    await Promise.all([first.fetchAll(), second.fetchAll()])

    expect(fetchMock).toHaveBeenCalledTimes(1)
  })

  it('con `force` sí vuelve a pedirlo', async () => {
    const useServices = await freshUseServices()

    await useServices().fetchAll()
    await useServices().fetchAll({ force: true })

    expect(fetchMock).toHaveBeenCalledTimes(2)
  })

  it('un fallo no marca el catálogo como cargado y se puede reintentar', async () => {
    fetchMock.mockResolvedValueOnce({ ok: false, status: 500 })
    const useServices = await freshUseServices()

    const { error, loaded, fetchAll } = useServices()
    await fetchAll()

    expect(error.value).toBe('HTTP 500')
    expect(loaded.value).toBe(false)

    await fetchAll()

    expect(fetchMock).toHaveBeenCalledTimes(2)
    expect(loaded.value).toBe(true)
  })
})
