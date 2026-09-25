import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useContactDetails } from '../src/composables/useContactDetails.js'

vi.mock('../src/services/adminService.js', () => ({
  apiGetContactDetails: vi.fn(),
  apiUpdateContactDetails: vi.fn(),
}))

import { apiGetContactDetails, apiUpdateContactDetails } from '../src/services/adminService.js'

describe('useContactDetails', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('carga lo configurado y de dónde sale cada campo', async () => {
    apiGetContactDetails.mockResolvedValueOnce({
      email: 'hola@dulzia.es',
      phone: '+34 629 991 659',
      email_source: 'db',
      phone_source: 'default',
    })

    const { email, phone, emailSource, phoneSource, fetchDetails } = useContactDetails()
    await fetchDetails()

    expect(email.value).toBe('hola@dulzia.es')
    expect(phone.value).toBe('+34 629 991 659')
    expect(emailSource.value).toBe('db')
    expect(phoneSource.value).toBe('default')
  })

  it('sin configurar deja los campos vacíos, no con el valor por defecto', async () => {
    // El valor por defecto lo pone la web al pintar: el panel no debe
    // confundirlo con algo que alguien haya configurado.
    apiGetContactDetails.mockResolvedValueOnce({
      email: null,
      phone: null,
      email_source: 'default',
      phone_source: 'default',
    })

    const { email, phone, fetchDetails } = useContactDetails()
    await fetchDetails()

    expect(email.value).toBe('')
    expect(phone.value).toBe('')
  })

  it('guarda lo que haya en los campos', async () => {
    apiUpdateContactDetails.mockResolvedValueOnce({ ok: true })

    const { email, phone, save } = useContactDetails()
    email.value = 'nuevo@dulzia.es'
    phone.value = '+34 600 000 000'

    await save()

    expect(apiUpdateContactDetails).toHaveBeenCalledWith({
      email: 'nuevo@dulzia.es',
      phone: '+34 600 000 000',
    })
  })

  it('envía cadenas vacías para volver al valor por defecto de la web', async () => {
    apiUpdateContactDetails.mockResolvedValueOnce({ ok: true })

    const { save } = useContactDetails()
    await save()

    expect(apiUpdateContactDetails).toHaveBeenCalledWith({ email: '', phone: '' })
  })

  it('propaga el error al guardar y suelta `saving`', async () => {
    apiUpdateContactDetails.mockRejectedValueOnce(new Error('El teléfono no es válido'))

    const { saving, save } = useContactDetails()

    await expect(save()).rejects.toThrow('El teléfono no es válido')
    expect(saving.value).toBe(false)
  })

  it('deja el error de lectura en `error` sin lanzarlo', async () => {
    apiGetContactDetails.mockRejectedValueOnce(new Error('Error 500'))

    const { error, loading, fetchDetails } = useContactDetails()
    await fetchDetails()

    expect(error.value).toBe('Error 500')
    expect(loading.value).toBe(false)
  })
})
