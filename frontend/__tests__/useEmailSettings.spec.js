import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useEmailSettings } from '../src/composables/useEmailSettings.js'

vi.mock('../src/services/adminService.js', () => ({
  apiGetContactRecipient: vi.fn(),
  apiUpdateContactRecipient: vi.fn(),
}))

import { apiGetContactRecipient, apiUpdateContactRecipient } from '../src/services/adminService.js'

describe('useEmailSettings', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('loads effective values and their source', async () => {
    apiGetContactRecipient.mockResolvedValueOnce({
      email: 'panel@example.com',
      name: 'Nombre del panel',
      email_source: 'db',
      name_source: 'env',
    })

    const { email, name, emailSource, nameSource, fetchSettings } = useEmailSettings()
    await fetchSettings()

    expect(email.value).toBe('panel@example.com')
    expect(name.value).toBe('Nombre del panel')
    expect(emailSource.value).toBe('db')
    expect(nameSource.value).toBe('env')
  })

  it('sets error and clears loading when fetch fails', async () => {
    apiGetContactRecipient.mockRejectedValueOnce(new Error('Error 500'))

    const { error, loading, fetchSettings } = useEmailSettings()
    await fetchSettings()

    expect(error.value).toBe('Error 500')
    expect(loading.value).toBe(false)
  })

  it('saves the current field values', async () => {
    apiUpdateContactRecipient.mockResolvedValueOnce({ ok: true })

    const { email, name, save } = useEmailSettings()
    email.value = 'nuevo@example.com'
    name.value  = 'Nuevo nombre'

    await save()

    expect(apiUpdateContactRecipient).toHaveBeenCalledWith({
      email: 'nuevo@example.com',
      name: 'Nuevo nombre',
    })
  })

  it('sends empty strings to go back to the server default', async () => {
    apiUpdateContactRecipient.mockResolvedValueOnce({ ok: true })

    const { save } = useEmailSettings()
    await save()

    expect(apiUpdateContactRecipient).toHaveBeenCalledWith({ email: '', name: '' })
  })

  it('propagates save errors and releases saving', async () => {
    apiUpdateContactRecipient.mockRejectedValueOnce(new Error('El email no es válido'))

    const { saving, save } = useEmailSettings()

    await expect(save()).rejects.toThrow('El email no es válido')
    expect(saving.value).toBe(false)
  })

  it('keeps saving true while the request is in flight', async () => {
    let resolveRequest
    apiUpdateContactRecipient.mockReturnValueOnce(new Promise(resolve => { resolveRequest = resolve }))

    const { saving, save } = useEmailSettings()
    const pending = save()
    expect(saving.value).toBe(true)

    resolveRequest({ ok: true })
    await pending
    expect(saving.value).toBe(false)
  })
})
