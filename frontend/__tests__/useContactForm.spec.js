import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useContactForm } from '../src/composables/useContactForm.js'

vi.mock('../src/services/contactService.js', () => ({
  contactService: {
    submit: vi.fn(),
  },
}))

import { contactService } from '../src/services/contactService.js'

describe('useContactForm', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('initializes with empty form and no errors', () => {
    const { form, errors, isLoading, isSuccess } = useContactForm()
    expect(form.name).toBe('')
    expect(form.email).toBe('')
    expect(Object.keys(errors)).toHaveLength(0)
    expect(isLoading.value).toBe(false)
    expect(isSuccess.value).toBe(false)
  })

  it('sets isSuccess on successful submit', async () => {
    contactService.submit.mockResolvedValueOnce({ message: 'ok' })
    const { form, isSuccess, submitForm } = useContactForm()
    form.name = 'Juan'
    form.email = 'juan@test.com'
    form.message = 'Hola!'
    await submitForm()
    expect(isSuccess.value).toBe(true)
  })

  it('maps validation errors from 422 response', async () => {
    const apiError = new Error('Validation error')
    apiError.status = 422
    apiError.data = { errors: { email: ['El email no es válido'] } }
    contactService.submit.mockRejectedValueOnce(apiError)

    const { errors, submitForm } = useContactForm()
    await submitForm()
    expect(errors.email).toEqual(['El email no es válido'])
  })

  it('sets serverError on unexpected error', async () => {
    contactService.submit.mockRejectedValueOnce(new Error('Network error'))
    const { serverError, submitForm } = useContactForm()
    await submitForm()
    expect(serverError.value).toBeTruthy()
  })
})
