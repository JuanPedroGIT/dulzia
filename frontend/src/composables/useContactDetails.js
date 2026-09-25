import { ref } from 'vue'
import { apiGetContactDetails, apiUpdateContactDetails } from '@/services/adminService.js'

/**
 * Datos de contacto que se publican en la web: el email y el teléfono que
 * pintan el pie, la página de contacto, las legales y el JSON-LD.
 *
 * Convención del proyecto: la lectura se traga el error y lo deja en `error`;
 * la escritura lo propaga para que la página decida qué mostrar.
 */
export function useContactDetails() {
  const email       = ref('')
  const phone       = ref('')
  const emailSource = ref('default') // 'default' = sin configurar (la web usa el suyo) | 'db' = configurado en el panel
  const phoneSource = ref('default')
  const loading     = ref(false)
  const saving      = ref(false)
  const error       = ref(null)

  async function fetchDetails() {
    loading.value = true
    error.value = null
    try {
      const data = await apiGetContactDetails()
      email.value       = data.email ?? ''
      phone.value       = data.phone ?? ''
      emailSource.value = data.email_source ?? 'default'
      phoneSource.value = data.phone_source ?? 'default'
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  /**
   * Guarda lo que haya ahora mismo en los campos. Enviar un campo vacío borra
   * el ajuste, que es como se vuelve al valor con el que la web viene de serie.
   */
  async function save() {
    saving.value = true
    try {
      await apiUpdateContactDetails({ email: email.value, phone: phone.value })
    } finally {
      saving.value = false
    }
  }

  return { email, phone, emailSource, phoneSource, loading, saving, error, fetchDetails, save }
}
