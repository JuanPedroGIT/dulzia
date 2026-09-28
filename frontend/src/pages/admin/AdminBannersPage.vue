<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { apiGetBanners, apiCreateBanner, apiUpdateBanner, apiDeleteBanner } from '@/services/adminService'

const router = useRouter()
const banners = ref([])
const loading = ref(true)
const pageError = ref('')

const modal = ref({ open: false, mode: 'create', id: '', loading: false, error: '' })
const form = ref({ title: '', description: '', starts_at: '', ends_at: '' })

async function fetchBanners() {
  loading.value = true; pageError.value = ''
  try { banners.value = await apiGetBanners() }
  catch (e) { if (e.message === '401') { router.push('/dulzia-panel/login'); return } pageError.value = 'Error al cargar' }
  finally { loading.value = false }
}
onMounted(fetchBanners)

function openCreate() {
  form.value = { title: '', description: '', starts_at: '', ends_at: '' }
  modal.value = { open: true, mode: 'create', id: '', loading: false, error: '' }
}

function openEdit(banner) {
  form.value = { title: banner.title, description: banner.description, starts_at: banner.starts_at, ends_at: banner.ends_at }
  modal.value = { open: true, mode: 'edit', id: banner.id, loading: false, error: '' }
}

function closeModal() { modal.value.open = false }

async function submitForm() {
  modal.value.loading = true; modal.value.error = ''
  const payload = { ...form.value }
  try {
    if (modal.value.mode === 'create') await apiCreateBanner(payload)
    else await apiUpdateBanner(modal.value.id, payload)
    closeModal(); await fetchBanners()
  } catch (e) { modal.value.error = e.message }
  finally { modal.value.loading = false }
}

async function removeBanner(banner) {
  if (!confirm(`¿Borrar el banner "${banner.title}"?`)) return
  try { await apiDeleteBanner(banner.id); await fetchBanners() }
  catch (e) { alert(e.message) }
}

// El estado se calcula contra la fecha de hoy al pintar (en local, no en UTC:
// en UTC un banner del día en curso puede saltar al día siguiente de madrugada).
function localDateStr(d = new Date()) {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

// Activo: hoy dentro de la ventana (bordes inclusive); Programado: todavía no
// ha empezado; Caducado: ya terminó. "AAAA-MM-DD" se compara bien como texto.
function statusOf(banner, today = localDateStr()) {
  if (banner.starts_at > today) return 'programado'
  if (banner.ends_at < today) return 'caducado'
  return 'activo'
}

const STATUS_LABELS = { activo: 'Activo', programado: 'Programado', caducado: 'Caducado' }
</script>

<template>
  <div class="admin-banners">
    <header class="admin-page-header">
      <div class="admin-header__inner">
        <router-link to="/dulzia-panel" class="btn-back">← Panel</router-link>
        <h1 class="header-title">📣 Banner informativo</h1>
        <button class="btn-add" @click="openCreate">+ Nuevo banner</button>
      </div>
    </header>

    <main class="admin-main">
      <p class="hint">
        El aviso que se ve arriba del todo en la web, en todas las páginas. Solo se
        enseña dentro de su ventana de fechas: si hay varios en ventana, manda el
        editado más reciente. El visitante puede cerrarlo (vuelve a salir al día
        siguiente).
      </p>

      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="pageError" class="state-msg state-msg--error">{{ pageError }}</div>

      <div v-else class="table-wrap">
        <table class="banners-table">
          <thead>
            <tr>
              <th>Título</th><th>Desde</th><th>Hasta</th><th class="th-center">Estado</th><th class="th-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in banners" :key="b.id">
              <td>
                <span class="banner-title">{{ b.title }}</span>
                <p class="banner-description">{{ b.description }}</p>
              </td>
              <td>{{ b.starts_at }}</td>
              <td>{{ b.ends_at }}</td>
              <td class="td-center">
                <span class="pill" :class="`pill--${statusOf(b)}`">{{ STATUS_LABELS[statusOf(b)] }}</span>
              </td>
              <td class="td-right">
                <div class="actions">
                  <button class="btn-action btn-action--edit" @click="openEdit(b)">Editar</button>
                  <button class="btn-action btn-action--delete" @click="removeBanner(b)">Borrar</button>
                </div>
              </td>
            </tr>
            <tr v-if="banners.length === 0">
              <td colspan="5" class="state-msg">No hay banners.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <div v-if="modal.open" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>{{ modal.mode === 'create' ? 'Nuevo banner' : 'Editar banner' }}</h3>
        <form class="modal-form" @submit.prevent="submitForm">
          <label class="form-label">
            Título
            <input :value="form.title" type="text" required maxlength="120" :disabled="modal.loading" @input="e => form.title = e.target.value" />
          </label>
          <label class="form-label">
            Descripción
            <textarea :value="form.description" required maxlength="500" rows="3" :disabled="modal.loading" @input="e => form.description = e.target.value"></textarea>
          </label>
          <div class="form-row">
            <label class="form-label">
              Desde
              <input :value="form.starts_at" type="date" required :disabled="modal.loading" @input="e => form.starts_at = e.target.value" />
            </label>
            <label class="form-label">
              Hasta
              <input :value="form.ends_at" type="date" required :disabled="modal.loading" @input="e => form.ends_at = e.target.value" />
            </label>
          </div>
          <p v-if="modal.error" class="modal-error">{{ modal.error }}</p>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="closeModal" :disabled="modal.loading">Cancelar</button>
            <button type="submit" class="btn-save" :disabled="modal.loading">{{ modal.loading ? 'Guardando…' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-banners{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-page-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:1rem}
.btn-back{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.85rem;color:#555;white-space:nowrap}
.btn-back:hover{border-color:#c8748a;color:#c8748a}
.header-title{font-size:1.05rem;font-weight:700;color:#1a1a1a;flex:1}
.btn-add{padding:.55rem 1.1rem;background:#c8748a;color:white;border:none;border-radius:9px;font-size:.875rem;font-weight:700;cursor:pointer;white-space:nowrap;margin-left:auto}
.btn-add:hover{background:#b5637a}
.admin-main{max-width:1100px;margin:0 auto;padding:1.5rem}
.hint{font-size:.85rem;color:#6b7280;margin:0 0 1.2rem;line-height:1.6}
.state-msg{padding:2rem;text-align:center;color:#6b7280}
.state-msg--error{color:#c0392b}
.table-wrap{background:white;border-radius:12px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.banners-table{width:100%;min-width:560px;border-collapse:collapse;font-size:.9rem}
.banners-table th{text-align:left;padding:.75rem 1rem;background:#faf8f6;color:#6b7280;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #ebe8e4}
.banners-table td{padding:.8rem 1rem;border-bottom:1px solid #f3f0ed;vertical-align:middle}
.th-center{text-align:center}
.th-right{text-align:right}
.td-center{text-align:center}
.td-right{text-align:right}
.banner-title{font-weight:600;color:#1a1a1a}
.banner-description{font-size:.8rem;color:#6b7280;margin:.25rem 0 0;max-width:320px}
.pill{display:inline-block;border-radius:20px;padding:.2rem .7rem;font-weight:700;font-size:.85rem}
.pill--activo{background:#eef6f4;color:#3a8a7a}
.pill--programado{background:#fdf6e8;color:#a06b00}
.pill--caducado{background:#f0efed;color:#6b7280}
.actions{display:flex;gap:.4rem;justify-content:flex-end}
.btn-action{padding:.35rem .7rem;border-radius:7px;border:1.5px solid transparent;font-size:.8rem;font-weight:600;cursor:pointer;white-space:nowrap}
.btn-action--edit{background:#f0f7f5;color:#3a8a7a;border-color:#d6ebe5}
.btn-action--edit:hover{background:#e2f2ed}
.btn-action--delete{background:#fdf0f3;color:#c0392b;border-color:#f6d9e0}
.btn-action--delete:hover{background:#fbe3e9}
.modal-overlay{position:fixed;inset:0;background:rgba(26,26,26,.5);display:flex;align-items:center;justify-content:center;padding:1rem;z-index:50}
.modal{background:white;border-radius:14px;padding:1.5rem;width:100%;max-width:520px}
.modal h3{margin:0 0 1rem;color:#1a1a1a}
.modal-form{display:flex;flex-direction:column;gap:.9rem}
.form-label{display:flex;flex-direction:column;gap:.3rem;font-size:.85rem;font-weight:600;color:#444}
.form-label input,.form-label textarea{padding:.55rem .7rem;border:1.5px solid #d0ccc8;border-radius:8px;font-size:.9rem;font-family:inherit}
.form-label textarea{resize:vertical}
.form-label input:focus,.form-label textarea:focus{outline:none;border-color:#c8748a}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
@media (max-width:420px){.form-row{grid-template-columns:1fr}}
.modal-error{color:#c0392b;font-size:.85rem;margin:0}
.modal-actions{display:flex;gap:.6rem;justify-content:flex-end;margin-top:.4rem}
.btn-cancel{padding:.55rem 1rem;background:white;border:1.5px solid #d0ccc8;border-radius:9px;font-weight:600;font-size:.875rem;cursor:pointer;color:#555}
.btn-save{padding:.55rem 1.2rem;background:#c8748a;color:white;border:none;border-radius:9px;font-weight:700;font-size:.875rem;cursor:pointer}
.btn-save:disabled,.btn-cancel:disabled{opacity:.6;cursor:not-allowed}
</style>
