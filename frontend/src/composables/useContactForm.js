import { reactive, ref } from 'vue'
import { contactService } from '@/services/contactService.js'

export function useContactForm() {
  const form = reactive({
    name:      '',
    email:     '',
    phone:     '',
    eventType: '',
    message:   '',
  })

  const errors    = reactive({})
  const isLoading = ref(false)
  const isSuccess = ref(false)
  const serverError = ref(null)

  function clearErrors() {
    Object.keys(errors).forEach(k => delete errors[k])
    serverError.value = null
  }

  async function submitForm() {
    clearErrors()
    isLoading.value = true

    try {
      await contactService.submit({
        name:      form.name,
        email:     form.email,
        phone:     form.phone || undefined,
        eventType: form.eventType || undefined,
        message:   form.message,
      })
      isSuccess.value = true
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        Object.assign(errors, err.data.errors)
      } else {
        serverError.value = 'Error al enviar el formulario. Inténtalo de nuevo o llámanos.'
      }
    } finally {
      isLoading.value = false
    }
  }

  return { form, errors, isLoading, isSuccess, serverError, submitForm }
}
