<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import BaseFileUpload from '@/components/ui/BaseFileUpload.vue'
import { apiGetServices, apiGetService, apiGetMessages, apiGetCategories, apiCreateService, apiUpdateService, apiDeactivateService, apiActivateService, apiSetServiceFeatured } from '@/services/adminService'

const router = useRouter()
const { logout } = useAuth()
const services  = ref([])
const loading   = ref(true)
const pageError = ref('')
const filter    = ref('all') // 'all' | 'active' | 'inactive'
// Las categorías se gestionan en su propia sección del panel: aquí solo se leen
// para el desplegable del modal y la etiqueta de la tabla.
const categories = ref([])
function categoryLabel(cat) { return categories.value.find(c => c.id === cat)?.name ?? cat }
const filteredServices = computed(() => {
  if (filter.value === 'active')   return services.value.filter(s => s.is_active)
  if (filter.value === 'inactive') return services.value.filter(s => !s.is_active)
  return services.value
})
const modal = ref({ open: false, mode: 'create', loading: false, fetching: false, error: '' })
const form  = ref({ id: '', name: '', description: '', features: '', category: 'food', image: null, thumbnail: null, currentImageUrl: '', fallbackImage: '', removeImage: false })
function emptyForm() {
  return { id: '', name: '', description: '', features: '', category: 'food', image: null, thumbnail: null, currentImageUrl: '', fallbackImage: '', removeImage: false }
}
// Aviso de qué se está viendo cuando la sección no tiene foto propia.
const coverHint = computed(() => {
  if (modal.value.mode !== 'edit' || form.value.image || form.value.currentImageUrl) return ''
  return form.value.fallbackImage
    ? 'Sin foto propia: se muestra la primera foto de su galería.'
    : 'Sin foto propia: se muestra el emoji de la sección.'
})
function featuresArray(str) { return str.split('\n').map(s => s.trim()).filter(Boolean) }
async function fetchServices() {
  loading.value = true; pageError.value = ''
  try { services.value = await apiGetServices() }
  catch (e) { if (e.message === '401') { router.push('/dulzia-panel/login'); return } pageError.value = 'Error al cargar' }
  finally { loading.value = false }
}
const unreadMessages = ref(0)
async function fetchUnreadCount() {
  try { unreadMessages.value = (await apiGetMessages(1)).unreadCount }
  catch { /* el badge se queda a 0 si la petición falla */ }
}
async function fetchCategories() {
  try { categories.value = await apiGetCategories() }
  catch { /* el desplegable se queda vacío si la petición falla */ }
}
onMounted(() => { fetchServices(); fetchUnreadCount(); fetchCategories() })
async function handleLogout() { await logout(); router.push('/dulzia-panel/login') }
function openCreate() {
  form.value = emptyForm()
  modal.value = { open: true, mode: 'create', loading: false, fetching: false, error: '' }
}
// El listado del panel (GET /api/admin/services) solo trae nombre, categoría,
// imagen y nº de fotos: sin pedir el detalle el modal se abriría en blanco y al
// guardar se sobrescribirían la descripción y las características con valores vacíos.
async function openEdit(s) {
  form.value = { ...emptyForm(), id: s.id, name: s.name, category: s.category }
  modal.value = { open: true, mode: 'edit', loading: false, fetching: true, error: '' }
  try {
    const full = await apiGetService(s.id)
    form.value = {
      ...emptyForm(),
      id: full.id,
      name: full.name,
      description: full.description ?? '',
      features: (full.features ?? []).join('\n'),
      category: full.category,
      // imageUrl = foto propia (lo que edita el panel); image = la que se muestra
      // ahora mismo (la propia o, si no hay, la primera de la galería).
      currentImageUrl: full.imageUrl ?? '',
      fallbackImage: full.imageUrl ? '' : (full.image ?? ''),
    }
    modal.value.fetching = false
  } catch (e) {
    // Sin datos fiables no se abre el formulario: guardarlo borraría el contenido actual.
    modal.value.open = false
    if (e.message === '401') { router.push('/dulzia-panel/login'); return }
    alert('No se pudieron cargar los datos de la sección: ' + e.message)
  }
}
// BaseFileUpload emite el File recortado y null al pulsar la X (quitar la foto).
function onImageFile(file) {
  if (file instanceof File) {
    form.value.image = file
    form.value.removeImage = false
  } else {
    form.value.image = null
    form.value.thumbnail = null
    form.value.currentImageUrl = ''
    form.value.removeImage = true
  }
}
// La miniatura viaja en la misma petición que la foto grande.
function onImageThumbnail(file) { form.value.thumbnail = file instanceof File ? file : null }
async function submitForm() {
  modal.value.loading = true; modal.value.error = ''
  const fd = new FormData()
  fd.append('name', form.value.name)
  fd.append('description', form.value.description)
  fd.append('category', form.value.category)
  featuresArray(form.value.features).forEach(f => fd.append('features[]', f))
  if (form.value.image instanceof File) fd.append('image', form.value.image)
  if (form.value.thumbnail instanceof File) fd.append('thumbnail', form.value.thumbnail)
  if (form.value.removeImage) fd.append('removeImage', '1')
  try {
    if (modal.value.mode === 'create') await apiCreateService(fd)
    else await apiUpdateService(form.value.id, fd)
    modal.value.open = false; await fetchServices()
  } catch (e) { modal.value.error = e.message }
  finally { modal.value.loading = false }
}
async function deactivateService(s) {
  if (!confirm('¿Desactivar "' + s.name + '"? Dejará de aparecer en la web.')) return
  try { await apiDeactivateService(s.id); await fetchServices() }
  catch (e) { alert('Error al desactivar: ' + e.message) }
}
async function activateService(s) {
  try { await apiActivateService(s.id); await fetchServices() }
  catch (e) { alert('Error al activar: ' + e.message) }
}
// El check se guarda al instante. La fila se actualiza en memoria en vez de
// recargar la tabla entera; si la petición falla se revierte y se avisa.
async function toggleFeatured(s) {
  const featured = !s.featured
  s.featured = featured
  try {
    await apiSetServiceFeatured(s.id, featured)
  } catch (e) {
    // El await de la petición deja pintado el valor optimista, así que al
    // revertir el estado Vue reescribe el input por sí solo.
    s.featured = !featured
    alert('Error al guardar la portada: ' + e.message)
  }
}
function goToPhotos(id) { router.push('/dulzia-panel/servicios/' + id) }
</script>

<template>
  <div class="admin">
    <header class="admin-header">
      <div class="admin-header__inner">
        <h1>✨ Dulzia — Panel de administración</h1>
        <button class="btn-logout" @click="handleLogout">Cerrar sesión</button>
      </div>
    </header>
    <main class="admin-main">
      <div class="toolbar">
        <div>
          <h2 class="toolbar__title">Secciones</h2>
          <p class="toolbar__sub">Gestiona los servicios que aparecen en la web.</p>
        </div>
        <div class="toolbar__actions">
          <router-link to="/dulzia-panel/mensajes" class="btn-messages">
            📩 Mensajes
            <span v-if="unreadMessages > 0" class="msg-badge">{{ unreadMessages }}</span>
          </router-link>
          <router-link to="/dulzia-panel/ajustes-email" class="btn-messages">⚙️ Ajustes de email</router-link>
          <router-link to="/dulzia-panel/categorias" class="btn-messages">🏷️ Categorías</router-link>
          <button class="btn-primary" @click="openCreate">+ Nueva sección</button>
        </div>
      </div>
      <div class="filter-bar">
        <button :class="['filter-btn', { active: filter === 'all' }]"      @click="filter = 'all'">Todos <span class="filter-count">{{ services.length }}</span></button>
        <button :class="['filter-btn', { active: filter === 'active' }]"   @click="filter = 'active'">Activos <span class="filter-count">{{ services.filter(s => s.is_active).length }}</span></button>
        <button :class="['filter-btn', { active: filter === 'inactive' }]" @click="filter = 'inactive'">Inactivos <span class="filter-count">{{ services.filter(s => !s.is_active).length }}</span></button>
      </div>
      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="pageError" class="state-msg state-msg--error">{{ pageError }}</div>
      <div v-else class="table-wrap">
        <table class="services-table">
          <thead>
            <tr>
              <th>Sección</th><th>Categoría</th><th class="th-center">Fotos</th><th class="th-center" title="Sale en las tarjetas del inicio y en la parrilla de la portada">Portada</th><th class="th-center">Estado</th><th class="th-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filteredServices" :key="s.id" :class="{ 'row--inactive': !s.is_active }">
              <td>
                <img v-if="s.image" :src="s.thumbnail || s.image" :alt="s.name" class="svc-thumb" loading="lazy" />
                <span v-else class="svc-emoji">{{ s.emoji }}</span>
                <span class="svc-name">{{ s.name }}</span>
              </td>
              <td><span class="badge">{{ categoryLabel(s.category) }}</span></td>
              <td class="td-center"><span class="photo-count">{{ s.photoCount }}</span></td>
              <td class="td-center">
                <input
                  type="checkbox"
                  class="featured-check"
                  :checked="s.featured"
                  :aria-label="`Mostrar ${s.name} en la portada`"
                  @change="toggleFeatured(s)"
                />
              </td>
              <td class="td-center">
                <span :class="['status-badge', s.is_active ? 'status-badge--active' : 'status-badge--inactive']">
                  {{ s.is_active ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="td-right">
                <div class="actions">
                  <button class="btn-action btn-action--photos" @click="goToPhotos(s.id)">Fotos</button>
                  <button class="btn-action btn-action--edit"   @click="openEdit(s)">Editar</button>
                  <button v-if="s.is_active"  class="btn-action btn-action--deactivate" @click="deactivateService(s)">Desactivar</button>
                  <button v-else              class="btn-action btn-action--activate"   @click="activateService(s)">Activar</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredServices.length === 0">
              <td colspan="6" class="state-msg">No hay servicios en esta categoría.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
    <div v-if="modal.open" class="modal-overlay" @click.self="modal.open = false">
      <div class="modal">
        <h3>{{ modal.mode === 'create' ? 'Nueva sección' : 'Editar sección' }}</h3>
        <p v-if="modal.fetching" class="modal-loading">Cargando datos…</p>
        <form v-else class="modal-form" @submit.prevent="submitForm">
          <label class="form-label">Nombre<input v-model="form.name" type="text" required :disabled="modal.loading" /></label>
          <label class="form-label">Categoría
            <select v-model="form.category" :disabled="modal.loading">
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </label>
          <div class="form-label">
            Foto de la sección
            <BaseFileUpload
              :current-image="form.currentImageUrl"
              :disabled="modal.loading"
              @change="onImageFile"
              @thumbnail="onImageThumbnail"
            />
            <span v-if="coverHint" class="form-hint">{{ coverHint }}</span>
          </div>
          <label class="form-label">Descripción<textarea v-model="form.description" rows="3" required :disabled="modal.loading" /></label>
          <label class="form-label">Características (una por línea)<textarea v-model="form.features" rows="4" :disabled="modal.loading" /></label>
          <p v-if="modal.error" class="modal-error">{{ modal.error }}</p>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="modal.open = false" :disabled="modal.loading">Cancelar</button>
            <button type="submit" class="btn-save" :disabled="modal.loading">{{ modal.loading ? 'Guardando…' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:1000px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
.admin-header h1{font-size:1.1rem;font-weight:700;color:#1a1a1a;margin:0}
.btn-logout{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.8rem;color:#666;transition:border-color .2s,color .2s}
.btn-logout:hover{border-color:#c8748a;color:#c8748a}
.admin-main{max-width:1000px;margin:0 auto;padding:2rem 1.5rem}
.toolbar{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.toolbar__title{font-size:1.3rem;font-weight:800;color:#1a1a1a;margin:0 0 .25rem}
.toolbar__sub{font-size:.875rem;color:#888;margin:0}
.btn-primary{padding:.65rem 1.25rem;background:#c8748a;color:white;border:none;border-radius:9px;font-size:.9rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:background .2s}
.btn-primary:hover{background:#b5637a}
.toolbar__actions{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
.btn-messages{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.1rem;border:1.5px solid #e5e1dc;border-radius:9px;background:white;color:#555;text-decoration:none;font-size:.9rem;font-weight:700;white-space:nowrap;transition:border-color .2s,color .2s}
.btn-messages:hover{border-color:#c8748a;color:#c8748a}
.msg-badge{background:#c8748a;color:white;border-radius:20px;padding:.05rem .5rem;font-size:.75rem}
.state-msg{text-align:center;padding:3rem;color:#888;font-size:.95rem}
.state-msg--error{color:#c0392b}
.table-wrap{background:white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow-x:auto}
.services-table{width:100%;min-width:700px;border-collapse:collapse}
.services-table thead{background:#faf8f6;border-bottom:1px solid #ebe8e4}
.services-table th{padding:.75rem 1.25rem;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#999;text-align:left;white-space:nowrap}
.th-center{text-align:center}.th-right{text-align:right}
.services-table td{padding:1rem 1.25rem;border-bottom:1px solid #f2efec;vertical-align:middle;white-space:nowrap}
.services-table tbody tr:last-child td{border-bottom:none}
.services-table tbody tr:hover{background:#fdfbf9}
.td-center{text-align:center}.td-right{text-align:right}
.svc-thumb{width:36px;height:36px;object-fit:cover;border-radius:8px;margin-right:.6rem;vertical-align:middle;background:#f0ece8}
.svc-emoji{font-size:1.4rem;margin-right:.6rem}.svc-name{font-weight:600;color:#1a1a1a;font-size:.95rem}
.badge{display:inline-block;padding:.25rem .65rem;background:#f0ece8;border-radius:20px;font-size:.75rem;font-weight:600;color:#666}
.photo-count{display:inline-block;background:#eef6f4;color:#3a8a7a;font-weight:700;font-size:.85rem;border-radius:20px;padding:.2rem .7rem}
.featured-check{width:18px;height:18px;accent-color:#c8748a;cursor:pointer}
.actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-action{padding:.4rem .85rem;border:none;border-radius:7px;font-size:.8rem;font-weight:600;cursor:pointer;transition:opacity .15s;white-space:nowrap}
.btn-action:hover{opacity:.8}
.btn-action--photos{background:#e8f4f2;color:#2e7d6e}
.btn-action--edit{background:#f0ece8;color:#555}
.btn-action--deactivate{background:#fde8e8;color:#c0392b}
.btn-action--activate{background:#e8f4ee;color:#1e7e4a}
.filter-bar{display:flex;gap:.5rem;margin-bottom:1.25rem}
.filter-btn{padding:.4rem .9rem;border:1.5px solid #e5e1dc;border-radius:20px;background:white;cursor:pointer;font-size:.8rem;font-weight:600;color:#666;transition:border-color .2s,color .2s,background .2s;display:flex;align-items:center;gap:.4rem}
.filter-btn:hover{border-color:#c8748a;color:#c8748a}
.filter-btn.active{background:#c8748a;border-color:#c8748a;color:white}
.filter-count{display:inline-block;background:rgba(0,0,0,.1);border-radius:20px;padding:.05rem .45rem;font-size:.72rem}
.filter-btn.active .filter-count{background:rgba(255,255,255,.25)}
.status-badge{display:inline-block;padding:.2rem .65rem;border-radius:20px;font-size:.75rem;font-weight:700}
.status-badge--active{background:#e8f4ee;color:#1e7e4a}
.status-badge--inactive{background:#f5f0ee;color:#999}
.row--inactive td{opacity:.55}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:100;padding:1rem}
.modal{background:white;border-radius:16px;padding:2rem;width:100%;max-width:520px;max-height:90vh;overflow-y:auto}
.modal h3{margin:0 0 1.5rem;font-size:1.1rem;font-weight:800}
.modal-form{display:flex;flex-direction:column;gap:1rem}
.modal-loading{text-align:center;padding:2rem;color:#888;font-size:.9rem;margin:0}
.form-label{display:flex;flex-direction:column;gap:.35rem;font-size:.825rem;font-weight:700;color:#444}
.form-hint{font-size:.75rem;font-weight:500;color:#999}
.form-label input,.form-label select,.form-label textarea{padding:.65rem .85rem;border:1.5px solid #e5e1dc;border-radius:8px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s;background:white}
.form-label input:focus,.form-label select:focus,.form-label textarea:focus{border-color:#c8748a}
.modal-error{color:#c0392b;font-size:.85rem;margin:0}
.modal-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:.5rem}
.btn-cancel{padding:.7rem 1.2rem;background:#f0ece8;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:600;color:#555}
.btn-save{padding:.7rem 1.5rem;background:#c8748a;color:white;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:700}
.btn-save:disabled{opacity:.55;cursor:not-allowed}
</style>