<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEmailSettings } from '@/composables/useEmailSettings.js'

const router = useRouter()
const { email, name, emailSource, nameSource, loading, saving, error, fetchSettings, save } = useEmailSettings()

const saveError = ref('')
const saved     = ref(false)

onMounted(() => fetchSettings())

function onSave() {
  saveError.value = ''
  saved.value = false
  save()
    .then(() => {
      saved.value = true
      // Al vaciar un campo reaparece el valor del servidor: hay que releer
      // para que los inputs muestren lo que de verdad se va a usar.
      return fetchSettings()
    })
    .catch(e => {
      if (e.message === '401') { router.push('/dulzia-panel/login'); return }
      saveError.value = e.message
    })
}

function onReset() {
  email.value = ''
  name.value = ''
  onSave()
}
</script>

<template>
  <div class="admin-settings">
    <header class="admin-page-header">
      <div class="admin-header__inner">
        <router-link to="/dulzia-panel" class="btn-back">← Panel</router-link>
        <span class="header-title">📩 Avisos por email</span>
      </div>
    </header>

    <main class="admin-main">
      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="error" class="state-msg state-msg--error">{{ error }}</div>

      <template v-else>
        <p class="intro">
          A esta dirección llegan los mensajes que envía el formulario de contacto. Es un dato
          interno: no se publica en la web. Si dejas un campo vacío, se usa el valor por
          defecto del servidor.
        </p>

        <form class="settings-card" @submit.prevent="onSave">
          <label class="form-label">
            Email de destino
            <input v-model="email" type="email" :disabled="saving" placeholder="nombre@dominio.com" />
          </label>
          <p class="field-source">
            {{ emailSource === 'db' ? 'Configurado en el panel' : 'Valor por defecto del servidor' }}
          </p>

          <label class="form-label">
            Nombre del destinatario
            <input v-model="name" type="text" maxlength="150" :disabled="saving" placeholder="Dulzia Salamanca Eventos" />
          </label>
          <p class="field-source">
            {{ nameSource === 'db' ? 'Configurado en el panel' : 'Valor por defecto del servidor' }}
          </p>

          <p v-if="saveError" class="form-error">{{ saveError }}</p>
          <p v-else-if="saved" class="form-ok">Guardado. Los próximos mensajes llegarán a esta dirección.</p>

          <div class="form-actions">
            <button type="button" class="btn-secondary" :disabled="saving" @click="onReset">Restablecer</button>
            <button type="submit" class="btn-save" :disabled="saving">{{ saving ? 'Guardando…' : 'Guardar' }}</button>
          </div>
        </form>
      </template>
    </main>
  </div>
</template>

<style scoped>
.admin-settings{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-page-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:900px;margin:0 auto;display:flex;align-items:center;gap:1rem}
.btn-back{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.85rem;color:#555;text-decoration:none;transition:border-color .2s;white-space:nowrap}
.btn-back:hover{border-color:#c8748a;color:#c8748a}
.header-title{font-size:1.05rem;font-weight:700;color:#1a1a1a;flex:1}
.admin-main{max-width:900px;margin:0 auto;padding:2rem 1.5rem}
.intro{font-size:.875rem;color:#777;line-height:1.6;margin:0 0 1.5rem;max-width:560px}
.state-msg{text-align:center;padding:3rem;color:#888;font-size:.95rem}
.state-msg--error{color:#c0392b}
.settings-card{background:white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:1.75rem;max-width:560px}
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
