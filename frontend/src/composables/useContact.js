import { computed, ref } from 'vue'

const BASE = '/api/contact'

// Durante el prerender del build se inyecta un snapshot (window.__DULZIA_PRERENDER__)
// con los datos de contacto: sin él el pie, las legales y el JSON-LD de las
// páginas estáticas saldrían con los valores por defecto.
const snapshot = typeof window !== 'undefined' ? window.__DULZIA_PRERENDER__ : null

/**
 * Lo que publica la web mientras el panel no diga otra cosa. El servidor no
 * tiene valor por defecto para estos datos (GET /api/contact devuelve `null` si
 * no hay nada configurado): el respaldo vive aquí, que es donde se pinta.
 */
export const CONTACT_DEFAULTS = {
  email: 'info@dulziasalamancaeventos.com',
  phone: '+34 629 991 659',
}

/** Deja un teléfono en solo dígitos: es lo que necesitan `tel:` y `wa.me`. */
export function phoneDigits(phone) {
  return String(phone ?? '').replace(/\D/g, '')
}

// Los mismos datos los pintan el pie, el banner, la página de contacto, las
// legales y la portada, así que viven a nivel de módulo y se piden una vez por
// sesión (mismo patrón que useServices/useCategories).
const data = ref({ ...CONTACT_DEFAULTS })
const loading = ref(false)
const loaded = ref(false)
const error = ref(null)

let pending = null

/** Mezcla lo configurado sobre los valores de siempre: un campo vacío o ausente
 *  se queda con el suyo, campo a campo. */
function apply(remote) {
  data.value = {
    email: remote?.email || CONTACT_DEFAULTS.email,
    phone: remote?.phone || CONTACT_DEFAULTS.phone,
  }
}

export function useContact() {
  async function fetchContact({ force = false } = {}) {
    if (snapshot?.contact) {
      apply(snapshot.contact)
      loaded.value = true
      return
    }

    if (pending) return pending
    if (loaded.value && !force) return

    loading.value = true
    error.value = null

    pending = (async () => {
      try {
        const res = await fetch(BASE)
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        apply(await res.json())
        loaded.value = true
      } catch (e) {
        // Sin marcar `loaded`: un fallo se reintenta al volver a navegar. Los
        // valores por defecto siguen puestos, así que la web nunca se queda sin
        // teléfono ni email.
        error.value = e.message
      } finally {
        loading.value = false
        pending = null
      }
    })()

    return pending
  }

  // Se pide sola: hasta la página más simple lleva el pie, que los necesita.
  fetchContact()

  const email = computed(() => data.value.email)
  const phone = computed(() => data.value.phone)
  // Los enlaces salen del mismo número que se ve: escritos a mano se
  // desincronizan en cuanto se cambia el teléfono en el panel.
  const telHref = computed(() => `tel:+${phoneDigits(data.value.phone)}`)
  const whatsappHref = computed(() => `https://wa.me/${phoneDigits(data.value.phone)}`)

  return { email, phone, telHref, whatsappHref, loading, loaded, error, fetchContact }
}
