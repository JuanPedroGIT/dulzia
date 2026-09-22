<script setup>
import { onMounted } from 'vue'
import { useMessages, formatDateTime } from '@/composables/useMessages.js'

const { messages, loading, error, page, totalPages, total, unreadCount, fetchPage, markRead, remove } = useMessages()

onMounted(() => fetchPage())

function onToggleRead(m) {
  markRead(m.id, !m.is_read).catch(e => alert('Error: ' + e.message))
}

function onDelete(m) {
  if (!confirm('¿Borrar el mensaje de "' + m.name + '"? Esta acción no se puede deshacer.')) return
  remove(m.id).catch(e => alert('Error: ' + e.message))
}
</script>

<template>
  <div class="admin-messages">
    <header class="admin-page-header">
      <div class="admin-header__inner">
        <router-link to="/dulzia-panel" class="btn-back">← Panel</router-link>
        <span class="header-title">📩 Mensajes</span>
        <span v-if="unreadCount > 0" class="header-badge">{{ unreadCount }} sin leer</span>
      </div>
    </header>

    <main class="admin-main">
      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="error" class="state-msg state-msg--error">{{ error }}</div>

      <template v-else>
        <p class="summary">{{ total }} mensajes · {{ unreadCount }} sin leer</p>

        <div class="table-wrap">
          <table class="messages-table">
            <thead>
              <tr>
                <th>Fecha</th><th>Nombre</th><th>Email</th><th>Evento</th>
                <th class="th-center">Email enviado</th><th class="th-center">Estado</th><th class="th-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in messages" :key="m.id" :class="{ 'row--unread': !m.is_read }">
                <td>{{ formatDateTime(m.submitted_at) }}</td>
                <td><span v-if="!m.is_read" class="unread-dot"></span><span class="svc-name">{{ m.name }}</span></td>
                <td>{{ m.email }}</td>
                <td>{{ m.event_type ?? '—' }}</td>
                <td class="td-center">{{ m.email_sent ? 'Sí' : 'No' }}</td>
                <td class="td-center">
                  <span :class="['status-badge', m.is_read ? 'status-badge--read' : 'status-badge--unread']">
                    {{ m.is_read ? 'Leído' : 'Nuevo' }}
                  </span>
                </td>
                <td class="td-right">
                  <div class="actions">
                    <router-link class="btn-action btn-action--view" :to="`/dulzia-panel/mensajes/${m.id}`">Ver</router-link>
                    <button class="btn-action btn-action--edit" @click="onToggleRead(m)">
                      {{ m.is_read ? 'No leído' : 'Leído' }}
                    </button>
                    <button class="btn-action btn-action--delete" @click="onDelete(m)">Borrar</button>
                  </div>
                </td>
              </tr>
              <tr v-if="messages.length === 0">
                <td colspan="7" class="state-msg">No hay mensajes todavía.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="totalPages > 1" class="pagination">
          <button class="btn-page" :disabled="page <= 1" @click="fetchPage(page - 1)">← Anterior</button>
          <span class="pagination__info">Página {{ page }} de {{ totalPages }}</span>
          <button class="btn-page" :disabled="page >= totalPages" @click="fetchPage(page + 1)">Siguiente →</button>
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.admin-messages{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-page-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:1rem}
.btn-back{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.85rem;color:#555;text-decoration:none;transition:border-color .2s;white-space:nowrap}
.btn-back:hover{border-color:#c8748a;color:#c8748a}
.header-title{font-size:1.05rem;font-weight:700;color:#1a1a1a;flex:1}
.header-badge{background:#c8748a;color:white;border-radius:20px;padding:.2rem .7rem;font-size:.75rem;font-weight:700}
.admin-main{max-width:1100px;margin:0 auto;padding:2rem 1.5rem}
.summary{font-size:.875rem;color:#888;margin:0 0 1rem}
.state-msg{text-align:center;padding:3rem;color:#888;font-size:.95rem}
.state-msg--error{color:#c0392b}
.table-wrap{background:white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow-x:auto}
.messages-table{width:100%;min-width:820px;border-collapse:collapse}
.messages-table thead{background:#faf8f6;border-bottom:1px solid #ebe8e4}
.messages-table th{padding:.75rem 1.25rem;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#999;text-align:left;white-space:nowrap}
.messages-table td{padding:1rem 1.25rem;border-bottom:1px solid #f2efec;vertical-align:middle;white-space:nowrap;font-size:.9rem;color:#444}
.messages-table tbody tr:last-child td{border-bottom:none}
.messages-table tbody tr:hover{background:#fdfbf9}
.th-center{text-align:center}.th-right{text-align:right}.td-center{text-align:center}.td-right{text-align:right}
.svc-name{font-weight:600;color:#1a1a1a}
.unread-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#c8748a;margin-right:.5rem;vertical-align:middle}
.row--unread td{background:#fdf6f8}
.status-badge{display:inline-block;padding:.2rem .65rem;border-radius:20px;font-size:.75rem;font-weight:700}
.status-badge--unread{background:#fde8e8;color:#c0392b}
.status-badge--read{background:#f0ece8;color:#777}
.actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-action{padding:.4rem .85rem;border:none;border-radius:7px;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none;transition:opacity .15s;white-space:nowrap}
.btn-action:hover{opacity:.8}
.btn-action--view{background:#e8f4f2;color:#2e7d6e}
.btn-action--edit{background:#f0ece8;color:#555}
.btn-action--delete{background:#fde8e8;color:#c0392b}
.pagination{display:flex;align-items:center;justify-content:center;gap:1rem;margin-top:1.5rem}
.pagination__info{font-size:.875rem;color:#666}
.btn-page{padding:.45rem 1rem;background:white;border:1.5px solid #e5e1dc;border-radius:8px;cursor:pointer;font-size:.85rem;font-weight:600;color:#555;transition:border-color .2s,color .2s}
.btn-page:hover:not(:disabled){border-color:#c8748a;color:#c8748a}
.btn-page:disabled{opacity:.4;cursor:not-allowed}
</style>
