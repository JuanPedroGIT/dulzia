// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

const ACTIVE = { id: 'b1', title: 'Aviso', description: 'Texto', starts_at: '2026-10-01', ends_at: '2026-10-31' }

let fetchMock

beforeEach(() => {
  fetchMock = vi.fn(async () => ({ ok: true, status: 200, json: async () => ({ banner: ACTIVE }) }))
  vi.stubGlobal('fetch', fetchMock)
})

afterEach(() => {
  vi.unstubAllGlobals()
})

// El banner vive a nivel de módulo, así que cada test necesita el módulo recién
// cargado para partir de cero.
async function freshUseBanner() {
  vi.resetModules()
  return await import('@/composables/useBanner.js')
}

describe('useBanner — banner informativo', () => {
  it('queda en null cuando no hay ningún banner en ventana', async () => {
    fetchMock.mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ banner: null }) })
    const { useBanner } = await freshUseBanner()

    const { banner, fetchBanner } = useBanner()
    await fetchBanner()

    expect(banner.value).toBeNull()
  })

  it('pide el banner una sola vez', async () => {
    const { useBanner } = await freshUseBanner()

    const first = useBanner()
    await first.fetchBanner()

    const second = useBanner()
    await second.fetchBanner()

    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(fetchMock).toHaveBeenCalledWith('/api/banner')
    expect(second.banner.value).toEqual(ACTIVE)
  })

  it('mantiene el banner aunque la API falle', async () => {
    fetchMock.mockResolvedValueOnce({ ok: false, status: 500 })
    const { useBanner } = await freshUseBanner()

    const { banner, error, fetchBanner } = useBanner()
    await fetchBanner()

    expect(banner.value).toBeNull()
    expect(error.value).toBe('HTTP 500')
  })

  it('con `force` sí vuelve a pedirlo', async () => {
    const { useBanner } = await freshUseBanner()

    const { fetchBanner } = useBanner()
    await fetchBanner()
    await fetchBanner({ force: true })

    expect(fetchMock).toHaveBeenCalledTimes(2)
  })
})
