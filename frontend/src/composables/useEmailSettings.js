import { ref } from 'vue'
import { apiGetContactRecipient, apiUpdateContactRecipient } from '@/services/adminService.js'

/**
 * Ajustes del destinatario de los mensajes de contacto.
 *
 * Convención del proyecto: la lectura se traga el error y lo deja en `error`;
 * la escritura lo propaga para que la página decida qué mostrar.
 */
export function useEmailSettings() {
  const email       = ref('')
  const name        = ref('')
  const emailSource = ref('env') // 'env' = valor por defecto del servidor | 'db' = configurado en el panel
  const nameSource  = ref('env')
  const loading     = ref(false)
  const saving      = ref(false)
  const error       = ref(null)

  async function fetchSettings() {
    loading.value = true
    error.value = null
    try {
      const data = await apiGetContactRecipient()
      email.value       = data.email ?? ''
      name.value        = data.name ?? ''
      emailSource.value = data.email_source ?? 'env'
      nameSource.value  = data.name_source ?? 'env'
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  /**
   * Guarda lo que haya ahora mismo en los campos. Enviar un campo vacío borra
   * el ajuste, que es como se vuelve al valor por defecto del servidor.
   */
  async function save() {
    saving.value = true
    try {
      await apiUpdateContactRecipient({ email: email.value, name: name.value })
    } finally {
      saving.value = false
    }
  }

  return { email, name, emailSource, nameSource, loading, saving, error, fetchSettings, save }
}
