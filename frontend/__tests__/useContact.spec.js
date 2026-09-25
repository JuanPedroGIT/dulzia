// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

const CONFIGURED = { email: 'hola@dulzia.es', phone: '629 000 111' }

let fetchMock

beforeEach(() => {
  fetchMock = vi.fn(async () => ({ ok: true, status: 200, json: async () => CONFIGURED }))
  vi.stubGlobal('fetch', fetchMock)
})

afterEach(() => {
  vi.unstubAllGlobals()
})

// Los datos de contacto viven a nivel de módulo, así que cada test necesita el
// módulo recién cargado para partir de cero.
async function freshUseContact() {
  vi.resetModules()
  return await import('@/composables/useContact.js')
}

describe('useContact — datos de contacto de la web', () => {
  it('pinta los valores por defecto aunque la API falle', async () => {
    fetchMock.mockResolvedValueOnce({ ok: false, status: 500 })
    const { useContact, CONTACT_DEFAULTS } = await freshUseContact()

    const { email, phone, fetchContact } = useContact()
    await fetchContact()

    // Sin datos del panel la web no se queda sin teléfono ni email.
    expect(email.value).toBe(CONTACT_DEFAULTS.email)
    expect(phone.value).toBe(CONTACT_DEFAULTS.phone)
  })

  it('pide los datos una sola vez aunque los usen varias páginas', async () => {
    const { useContact } = await freshUseContact()

    const footer = useContact()
    await footer.fetchContact()

    const contact = useContact()
    await contact.fetchContact()

    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(fetchMock).toHaveBeenCalledWith('/api/contact')
    expect(contact.email.value).toBe('hola@dulzia.es')
  })

  it('lo configurado en el panel gana sobre los valores por defecto, campo a campo', async () => {
    fetchMock.mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ email: 'hola@dulzia.es', phone: null }) })
    const { useContact, CONTACT_DEFAULTS } = await freshUseContact()

    const { email, phone, fetchContact } = useContact()
    await fetchContact()

    expect(email.value).toBe('hola@dulzia.es')
    expect(phone.value).toBe(CONTACT_DEFAULTS.phone)
  })

  it('deriva los enlaces de teléfono y WhatsApp del mismo número', async () => {
    const { useContact } = await freshUseContact()

    const { phone, telHref, whatsappHref, fetchContact } = useContact()
    await fetchContact()

    expect(phone.value).toBe('629 000 111')
    expect(telHref.value).toBe('tel:+629000111')
    expect(whatsappHref.value).toBe('https://wa.me/629000111')
  })

  it('con `force` sí vuelve a pedirlos', async () => {
    const { useContact } = await freshUseContact()

    const { fetchContact } = useContact()
    await fetchContact()
    await fetchContact({ force: true })

    expect(fetchMock).toHaveBeenCalledTimes(2)
  })
})
