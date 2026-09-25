<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEmailSettings } from '@/composables/useEmailSettings.js'
import { useContactDetails } from '@/composables/useContactDetails.js'
import { CONTACT_DEFAULTS } from '@/composables/useContact.js'

const router = useRouter()

// Dos ajustes independientes: a dónde llegan los avisos (interno) y lo que la
// web publica como datos de contacto.
const recipient = useEmailSettings()
const details   = useContactDetails()

const recipientSaved = ref(false)
const recipientError = ref('')
const detailsSaved   = ref(false)
const detailsError   = ref('')

onMounted(() => {
  recipient.fetchSettings()
  details.fetchDetails()
})

function onSaveRecipient() {
  recipientError.value = ''
  recipientSaved.value = false
  recipient.save()
    .then(() => {
      recipientSaved.value = true
      // Al vaciar un campo reaparece el valor del servidor: hay que releer
      // para que los inputs muestren lo que de verdad se va a usar.
      return recipient.fetchSettings()
    })
    .catch(e => {
      if (e.message === '401') { router.push('/dulzia-panel/login'); return }
      recipientError.value = e.message
    })
}

function onSaveDetails() {
  detailsError.value = ''
  detailsSaved.value = false
  details.save()
    .then(() => {
      detailsSaved.value = true
      return details.fetchDetails()
    })
    .catch(e => {
      if (e.message === '401') { router.push('/dulzia-panel/login'); return }
      detailsError.value = e.message
    })
}

function onResetRecipient() {
  recipient.email.value = ''
  recipient.name.value = ''
  onSaveRecipient()
}

function onResetDetails() {
  details.email.value = ''
  details.phone.value = ''
  onSaveDetails()
}
</script>

<template>
  <div class="admin-email-settings">
    <header class="admin-page-header">
      <div class="admin-header__inner">
        <router-link to="/dulzia-panel/mensajes" class="btn-back">← Mensajes</router-link>
        <span class="header-title">⚙️ Ajustes de contacto</span>
      </div>
    </header>

    <main class="admin-main">
      <div v-if="recipient.loading.value || details.loading.value" class="state-msg">Cargando…</div>
      <div v-else-if="recipient.error.value" class="state-msg state-msg--error">{{ recipient.error.value }}</div>
      <div v-else-if="details.error.value" class="state-msg state-msg--error">{{ details.error.value }}</div>

      <template v-else>
        <form class="settings-card" @submit.prevent="onSaveRecipient">
          <h2 class="settings-card__title">Avisos del formulario</h2>
          <p class="settings-card__intro">
            Dirección a la que llegan los mensajes del formulario de contacto. Es interna:
            no se publica en la web. Si dejas un campo vacío, se usa el valor por defecto del servidor.
          </p>

          <label class="form-label">
            Email de destino
            <input v-model="recipient.email.value" type="email" :disabled="recipient.saving.value" placeholder="nombre@dominio.com" />
          </label>
          <p class="field-source">
            {{ recipient.emailSource.value === 'db' ? 'Configurado en el panel' : 'Valor por defecto del servidor' }}
          </p>

          <label class="form-label">
            Nombre del destinatario
            <input v-model="recipient.name.value" type="text" maxlength="150" :disabled="recipient.saving.value" placeholder="Dulzia Salamanca Eventos" />
          </label>
          <p class="field-source">
            {{ recipient.nameSource.value === 'db' ? 'Configurado en el panel' : 'Valor por defecto del servidor' }}
          </p>

          <p v-if="recipientError" class="form-error">{{ recipientError }}</p>
          <p v-else-if="recipientSaved" class="form-ok">Guardado. Los próximos mensajes llegarán a esta dirección.</p>

          <div class="form-actions">
            <button type="button" class="btn-secondary" :disabled="recipient.saving.value" @click="onResetRecipient">Restablecer</button>
            <button type="submit" class="btn-save" :disabled="recipient.saving.value">
              {{ recipient.saving.value ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </form>

        <form class="settings-card" @submit.prevent="onSaveDetails">
          <h2 class="settings-card__title">Datos publicados en la web</h2>
          <p class="settings-card__intro">
            Estos dos datos salen en la web: en el pie, en la página de contacto, en las políticas
            legales y en los datos estructurados de Google. El botón de WhatsApp usa el mismo
            teléfono. Si dejas un campo vacío, la web muestra el suyo.
          </p>

          <label class="form-label">
            Email de contacto
            <input v-model="details.email.value" type="email" :disabled="details.saving.value" :placeholder="CONTACT_DEFAULTS.email" />
          </label>
          <p class="field-source">
            {{ details.emailSource.value === 'db' ? 'Configurado en el panel' : `Sin configurar: la web muestra ${CONTACT_DEFAULTS.email}` }}
          </p>

          <label class="form-label">
            Teléfono de contacto
            <input v-model="details.phone.value" type="tel" maxlength="30" :disabled="details.saving.value" :placeholder="CONTACT_DEFAULTS.phone" />
          </label>
          <p class="field-source">
            {{ details.phoneSource.value === 'db' ? 'Configurado en el panel' : `Sin configurar: la web muestra ${CONTACT_DEFAULTS.phone}` }}
          </p>

          <p v-if="detailsError" class="form-error">{{ detailsError }}</p>
          <p v-else-if="detailsSaved" class="form-ok">Guardado. La web ya muestra estos datos.</p>

          <div class="form-actions">
            <button type="button" class="btn-secondary" :disabled="details.saving.value" @click="onResetDetails">Restablecer</button>
            <button type="submit" class="btn-save" :disabled="details.saving.value">
              {{ details.saving.value ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </form>
      </template>
    </main>
  </div>
</template>

<style scoped>
.admin-email-settings{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-page-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:900px;margin:0 auto;display:flex;align-items:center;gap:1rem}
.btn-back{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.85rem;color:#555;text-decoration:none;transition:border-color .2s;white-space:nowrap}
.btn-back:hover{border-color:#c8748a;color:#c8748a}
.header-title{font-size:1.05rem;font-weight:700;color:#1a1a1a;flex:1}
.admin-main{max-width:900px;margin:0 auto;padding:2rem 1.5rem;display:flex;flex-direction:column;gap:1.5rem}
.state-msg{text-align:center;padding:3rem;color:#888;font-size:.95rem}
.state-msg--error{color:#c0392b}
.settings-card{background:white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:1.75rem;max-width:560px}
.settings-card__title{font-size:1rem;font-weight:700;color:#1a1a1a;margin:0 0 .5rem}
.settings-card__intro{font-size:.875rem;color:#777;line-height:1.6;margin:0 0 1.5rem}
.form-label{display:flex;flex-direction:column;gap:.35rem;font-size:.825rem;font-weight:700;color:#444}
.form-label input{padding:.65rem .85rem;border:1.5px solid #e5e1dc;border-radius:8px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s;background:white}
.form-label input:focus{border-color:#c8748a}
.form-label input:disabled{background:#faf8f6;color:#999}
.field-source{font-size:.75rem;color:#999;margin:.4rem 0 1.1rem}
.form-error{color:#c0392b;font-size:.85rem;margin:0 0 .5rem}
.form-ok{color:#1e7e4a;font-size:.85rem;margin:0 0 .5rem}
.form-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;flex-wrap:wrap}
.btn-secondary{padding:.7rem 1.2rem;background:#f0ece8;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:600;color:#555}
.btn-secondary:disabled{opacity:.55;cursor:not-allowed}
.btn-save{padding:.7rem 1.5rem;background:#c8748a;color:white;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:700}
.btn-save:disabled{opacity:.55;cursor:not-allowed}
</style>
