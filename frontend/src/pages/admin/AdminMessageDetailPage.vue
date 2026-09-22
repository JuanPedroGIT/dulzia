<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMessages, formatDateTime } from '@/composables/useMessages.js'

const route  = useRoute()
const router = useRouter()
const { message, loading, error, fetchOne, markRead, remove } = useMessages()

onMounted(() => fetchOne(route.params.id))

function onToggleRead() {
  if (!message.value) return
  markRead(message.value.id, !message.value.is_read)
    .catch(e => alert('Error: ' + e.message))
}

function onDelete() {
  if (!message.value) return
  if (!confirm('¿Borrar este mensaje? Esta acción no se puede deshacer.')) return
  remove(message.value.id)
    .then(() => router.push('/dulzia-panel/mensajes'))
    .catch(e => alert('Error: ' + e.message))
}
</script>

<template>
  <div class="admin-message-detail">
    <header class="admin-page-header">
      <div class="admin-header__inner">
        <router-link to="/dulzia-panel/mensajes" class="btn-back">← Mensajes</router-link>
        <span class="header-title">Detalle del mensaje</span>
      </div>
    </header>

    <main class="admin-main">
      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="error || !message" class="state-msg state-msg--error">
        {{ error || 'Mensaje no encontrado' }}
      </div>

      <template v-else>
        <div class="detail-card">
          <div class="detail-card__header">
            <div>
              <h2 class="detail-card__name">{{ message.name }}</h2>
              <span :class="['status-badge', message.is_read ? 'status-badge--read' : 'status-badge--unread']">
                {{ message.is_read ? 'Leído' : 'Nuevo' }}
              </span>
            </div>
            <span class="detail-card__date">{{ formatDateTime(message.submitted_at) }}</span>
          </div>

          <dl class="detail-fields">
            <div><dt>Email</dt><dd><a :href="`mailto:${message.email}`">{{ message.email }}</a></dd></div>
            <div><dt>Teléfono</dt><dd>{{ message.phone ?? '—' }}</dd></div>
            <div><dt>Tipo de evento</dt><dd>{{ message.event_type ?? '—' }}</dd></div>
            <div><dt>Email enviado</dt><dd>
              {{ message.email_sent ? 'Sí' + (message.email_sent_at ? ' (' + formatDateTime(message.email_sent_at) + ')' : '') : 'No — no llegó al correo' }}
            </dd></div>
            <div><dt>IP del remitente</dt><dd>{{ message.ip_address ?? '—' }}</dd></div>
          </dl>

          <h3 class="detail-card__label">Mensaje</h3>
          <blockquote class="detail-card__message">{{ message.message }}</blockquote>

          <div class="detail-actions">
            <button class="btn-secondary" @click="onToggleRead">
              {{ message.is_read ? 'Marcar como no leído' : 'Marcar como leído' }}
            </button>
            <button class="btn-danger" @click="onDelete">Borrar mensaje</button>
          </div>
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.admin-message-detail{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-page-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:900px;margin:0 auto;display:flex;align-items:center;gap:1rem}
.btn-back{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.85rem;color:#555;text-decoration:none;transition:border-color .2s;white-space:nowrap}
.btn-back:hover{border-color:#c8748a;color:#c8748a}
.header-title{font-size:1.05rem;font-weight:700;color:#1a1a1a;flex:1}
.admin-main{max-width:900px;margin:0 auto;padding:2rem 1.5rem}
.state-msg{text-align:center;padding:3rem;color:#888;font-size:.95rem}
.state-msg--error{color:#c0392b}
.detail-card{background:white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:1.75rem}
.detail-card__header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem}
.detail-card__name{font-size:1.2rem;font-weight:800;color:#1a1a1a;margin:0 0 .4rem}
.detail-card__date{font-size:.8rem;color:#999;white-space:nowrap}
.detail-fields{display:grid;grid-template-columns:1fr;gap:.75rem;margin:0 0 1.5rem}
.detail-fields div{display:flex;flex-direction:column;gap:.15rem}
.detail-fields dt{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#999}
.detail-fields dd{margin:0;font-size:.925rem;color:#333}
.detail-fields a{color:#c8748a;text-decoration:none}
.detail-fields a:hover{text-decoration:underline}
.detail-card__label{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#999;margin:0 0 .5rem}
.detail-card__message{margin:0;padding:1rem 1.25rem;background:#faf8f6;border-left:3px solid #c8748a;border-radius:0 8px 8px 0;font-size:.95rem;line-height:1.7;color:#333;white-space:pre-wrap;overflow-wrap:break-word}
.detail-actions{display:flex;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap}
.btn-secondary{padding:.6rem 1.2rem;background:#f0ece8;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:600;color:#555}
.btn-secondary:hover{background:#e5e1dd}
.btn-danger{padding:.6rem 1.2rem;background:#fde8e8;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:600;color:#c0392b}
.btn-danger:hover{background:#f5c6c6}
.status-badge{display:inline-block;padding:.2rem .65rem;border-radius:20px;font-size:.75rem;font-weight:700}
.status-badge--unread{background:#fde8e8;color:#c0392b}
.status-badge--read{background:#f0ece8;color:#777}
@media (min-width: 640px) {
  .detail-fields{grid-template-columns:repeat(2,1fr)}
}
</style>
