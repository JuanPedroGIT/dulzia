<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { apiGetCategories, apiCreateCategory, apiUpdateCategory, apiDeleteCategory } from '@/services/adminService'

const router = useRouter()
const categories = ref([])
const loading = ref(true)
const pageError = ref('')

const modal = ref({ open: false, mode: 'create', id: '', loading: false, error: '' })
const form = ref({ name: '', emoji: '', sort_order: 0 })
// El identificador no se edita (los servicios lo referencian): en el alta se
// muestra el que se generará del nombre, como pista.
const generatedId = ref('')

async function fetchCategories() {
  loading.value = true; pageError.value = ''
  try { categories.value = await apiGetCategories() }
  catch (e) { if (e.message === '401') { router.push('/dulzia-panel/login'); return } pageError.value = 'Error al cargar' }
  finally { loading.value = false }
}
onMounted(fetchCategories)

function goBack() { router.push('/dulzia-panel') }

function slugify(name) {
  return name
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')   // quita acentos
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
}

function openCreate() {
  form.value = { name: '', emoji: '', sort_order: nextSortOrder() }
  generatedId.value = ''
  modal.value = { open: true, mode: 'create', id: '', loading: false, error: '' }
}

function openEdit(category) {
  form.value = { name: category.name, emoji: category.emoji ?? '', sort_order: category.sort_order }
  modal.value = { open: true, mode: 'edit', id: category.id, loading: false, error: '' }
}

function closeModal() { modal.value.open = false }

// La nueva se coloca al final de la lista por defecto.
function nextSortOrder() {
  return categories.value.length
    ? Math.max(...categories.value.map(c => c.sort_order)) + 1
    : 0
}

async function submitForm() {
  modal.value.loading = true; modal.value.error = ''
  const payload = { name: form.value.name, emoji: form.value.emoji, sort_order: Number(form.value.sort_order) || 0 }
  try {
    if (modal.value.mode === 'create') await apiCreateCategory(payload)
    else await apiUpdateCategory(modal.value.id, payload)
    closeModal(); await fetchCategories()
  } catch (e) { modal.value.error = e.message }
  finally { modal.value.loading = false }
}

async function removeCategory(category) {
  if (!confirm(`¿Borrar la categoría "${category.name}"?`)) return
  try { await apiDeleteCategory(category.id); await fetchCategories() }
  // El backend bloquea el borrado si hay secciones usándola (409): su mensaje ya
  // explica cuántas son, así que se muestra tal cual.
  catch (e) { alert(e.message) }
}
</script>

<template>
  <div class="admin-categories">
    <header class="admin-page-header">
      <div class="admin-header__inner">
        <button class="btn-back" @click="goBack">← Secciones</button>
        <h1 class="header-title">Categorías</h1>
        <button class="btn-add" @click="openCreate">+ Nueva categoría</button>
      </div>
    </header>

    <main class="admin-main">
      <p class="hint">
        Las categorías son las pestañas del catálogo y la etiqueta que muestra cada
        sección. El identificador se genera del nombre y no se puede cambiar.
      </p>

      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="pageError" class="state-msg state-msg--error">{{ pageError }}</div>

      <div v-else class="table-wrap">
        <table class="categories-table">
          <thead>
            <tr>
              <th>Nombre</th><th>Identificador</th><th class="th-center">Secciones</th><th class="th-center">Orden</th><th class="th-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in categories" :key="c.id">
              <td>
                <span v-if="c.emoji" class="cat-emoji">{{ c.emoji }}</span>
                <span class="cat-name">{{ c.name }}</span>
              </td>
              <td><code class="cat-id">{{ c.id }}</code></td>
              <td class="td-center"><span class="count">{{ c.serviceCount }}</span></td>
              <td class="td-center">{{ c.sort_order }}</td>
              <td class="td-right">
                <div class="actions">
                  <button class="btn-action btn-action--edit" @click="openEdit(c)">Editar</button>
                  <button class="btn-action btn-action--delete" @click="removeCategory(c)">Borrar</button>
                </div>
              </td>
            </tr>
            <tr v-if="categories.length === 0">
              <td colspan="5" class="state-msg">No hay categorías.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <div v-if="modal.open" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>{{ modal.mode === 'create' ? 'Nueva categoría' : 'Editar categoría' }}</h3>
        <form class="modal-form" @submit.prevent="submitForm">
          <label class="form-label">
            Nombre
            <input
              :value="form.name"
              type="text"
              required
              :disabled="modal.loading"
              @input="e => { form.name = e.target.value; generatedId = slugify(e.target.value) }"
            />
          </label>
          <label class="form-label">
            Emoji (opcional, se usa en las pestañas del catálogo)
            <input :value="form.emoji" type="text" maxlength="8" :disabled="modal.loading" @input="e => form.emoji = e.target.value" />
          </label>
          <label class="form-label">
            Orden
            <input :value="form.sort_order" type="number" min="0" :disabled="modal.loading" @input="e => form.sort_order = e.target.value" />
          </label>
          <p v-if="modal.mode === 'create' && generatedId" class="form-hint">
            Identificador: <code>{{ generatedId }}</code>
          </p>
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
.admin-categories{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
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
.table-wrap{background:white;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.categories-table{width:100%;border-collapse:collapse;font-size:.9rem}
.categories-table th{text-align:left;padding:.75rem 1rem;background:#faf8f6;color:#6b7280;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #ebe8e4}
.categories-table td{padding:.8rem 1rem;border-bottom:1px solid #f3f0ed;vertical-align:middle}
.th-center{text-align:center}
.th-right{text-align:right}
.td-center{text-align:center}
.td-right{text-align:right}
.cat-name{font-weight:600;color:#1a1a1a}
.cat-emoji{margin-right:.5rem}
.cat-id{font-size:.8rem;color:#6b7280;background:#f3f0ed;padding:.15rem .45rem;border-radius:6px}
.count{display:inline-block;background:#eef6f4;color:#3a8a7a;font-weight:700;font-size:.85rem;border-radius:20px;padding:.2rem .7rem}
.actions{display:flex;gap:.4rem;justify-content:flex-end}
.btn-action{padding:.35rem .7rem;border-radius:7px;border:1.5px solid transparent;font-size:.8rem;font-weight:600;cursor:pointer;white-space:nowrap}
.btn-action--edit{background:#f0f7f5;color:#3a8a7a;border-color:#d6ebe5}
.btn-action--edit:hover{background:#e2f2ed}
.btn-action--delete{background:#fdf0f3;color:#c0392b;border-color:#f6d9e0}
.btn-action--delete:hover{background:#fbe3e9}
.modal-overlay{position:fixed;inset:0;background:rgba(26,26,26,.5);display:flex;align-items:center;justify-content:center;padding:1rem;z-index:50}
.modal{background:white;border-radius:14px;padding:1.5rem;width:100%;max-width:460px}
.modal h3{margin:0 0 1rem;color:#1a1a1a}
.modal-form{display:flex;flex-direction:column;gap:.9rem}
.form-label{display:flex;flex-direction:column;gap:.3rem;font-size:.85rem;font-weight:600;color:#444}
.form-label input{padding:.55rem .7rem;border:1.5px solid #d0ccc8;border-radius:8px;font-size:.9rem;font-family:inherit}
.form-label input:focus{outline:none;border-color:#c8748a}
.form-hint{font-size:.8rem;color:#6b7280;margin:0}
.form-hint code{background:#f3f0ed;padding:.1rem .4rem;border-radius:5px}
.modal-error{color:#c0392b;font-size:.85rem;margin:0}
.modal-actions{display:flex;gap:.6rem;justify-content:flex-end;margin-top:.4rem}
.btn-cancel{padding:.55rem 1rem;background:white;border:1.5px solid #d0ccc8;border-radius:9px;font-weight:600;font-size:.875rem;cursor:pointer;color:#555}
.btn-save{padding:.55rem 1.2rem;background:#c8748a;color:white;border:none;border-radius:9px;font-weight:700;font-size:.875rem;cursor:pointer}
.btn-save:disabled,.btn-cancel:disabled{opacity:.6;cursor:not-allowed}
</style>
