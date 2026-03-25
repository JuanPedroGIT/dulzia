<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiGetService, apiAddPhoto, apiUpdatePhoto, apiDeletePhoto } from '@/services/adminService'

const router  = useRouter()
const route   = useRoute()
const service = ref(null)
const loading = ref(true)
const pageError = ref('')

const addModal  = ref({ open: false, loading: false, error: '' })
const addForm   = ref({ title: '', description: '', image: null })
const editModal = ref({ open: false, photoId: '', loading: false, error: '' })
const editForm  = ref({ title: '', description: '', image: null, currentImageUrl: '' })

async function fetchService() {
  loading.value = true; pageError.value = ''
  try { service.value = await apiGetService(route.params.id) }
  catch (e) { if (e.message === '401') { router.push('/dulzia-panel/login'); return } pageError.value = 'Error al cargar' }
  finally { loading.value = false }
}
onMounted(fetchService)

function goBack() { router.push('/dulzia-panel') }

function openAdd() {
  addForm.value = { title: '', description: '', image: null }
  addModal.value = { open: true, loading: false, error: '' }
}
function closeAdd() { addModal.value.open = false }

async function submitAdd() {
  const fd = new FormData()
  fd.append('title', addForm.value.title)
  fd.append('description', addForm.value.description)
  if (addForm.value.image) fd.append('image', addForm.value.image)
  addModal.value.loading = true; addModal.value.error = ''
  try { await apiAddPhoto(route.params.id, fd); closeAdd(); await fetchService() }
  catch (e) { addModal.value.error = e.message }
  finally { addModal.value.loading = false }
}

function openEdit(photo) {
  editForm.value = { title: photo.title, description: photo.description, image: null, currentImageUrl: photo.imageUrl }
  editModal.value = { open: true, photoId: photo.id, loading: false, error: '' }
}
function closeEdit() { editModal.value.open = false }

async function submitEdit() {
  const fd = new FormData()
  fd.append('title', editForm.value.title)
  fd.append('description', editForm.value.description)
  if (editForm.value.image) fd.append('image', editForm.value.image)
  editModal.value.loading = true; editModal.value.error = ''
  try { await apiUpdatePhoto(editModal.value.photoId, fd); closeEdit(); await fetchService() }
  catch (e) { editModal.value.error = e.message }
  finally { editModal.value.loading = false }
}

async function deletePhoto(photoId) {
  if (!confirm('¿Eliminar esta foto?')) return
  try { await apiDeletePhoto(photoId); await fetchService() }
  catch (e) { alert('Error: ' + e.message) }
}

function onAddFile(e)  { addForm.value.image  = e.target.files[0] || null }
function onEditFile(e) { editForm.value.image = e.target.files[0] || null }
</script>

<template>
  <div class="admin">
    <header class="admin-header">
      <div class="admin-header__inner">
        <button class="btn-back" @click="goBack">← Secciones</button>
        <span v-if="service" class="header-title">{{ service.emoji }} {{ service.name }}</span>
        <button class="btn-add-photo" v-if="service" @click="openAdd">+ Añadir foto</button>
      </div>
    </header>

    <main class="admin-main">
      <div v-if="loading" class="state-msg">Cargando…</div>
      <div v-else-if="pageError" class="state-msg state-msg--error">{{ pageError }}</div>

      <template v-else-if="service">
        <div v-if="service.photos.length === 0" class="empty">
          <p>Sin fotos aún. Añade la primera.</p>
          <button class="btn-primary" @click="openAdd">+ Añadir foto</button>
        </div>

        <div v-else class="photos-grid">
          <div v-for="photo in service.photos" :key="photo.id" class="photo-card">
            <img :src="photo.imageUrl" :alt="photo.title" class="photo-card__img" />
            <div class="photo-card__info">
              <p class="photo-card__title">{{ photo.title }}</p>
              <p class="photo-card__desc">{{ photo.description }}</p>
            </div>
            <div class="photo-card__actions">
              <button class="btn-edit" @click="openEdit(photo)">Editar</button>
              <button class="btn-delete" @click="deletePhoto(photo.id)">Eliminar</button>
            </div>
          </div>
        </div>
      </template>
    </main>

    <!-- Modal Añadir -->
    <div v-if="addModal.open" class="modal-overlay" @click.self="closeAdd">
      <div class="modal">
        <h3>Añadir foto</h3>
        <form class="modal-form" @submit.prevent="submitAdd">
          <label class="form-label">Título<input v-model="addForm.title" type="text" required :disabled="addModal.loading" /></label>
          <label class="form-label">Descripción<textarea v-model="addForm.description" rows="3" required :disabled="addModal.loading" /></label>
          <label class="form-label">Imagen<input type="file" accept="image/*" @change="onAddFile" :disabled="addModal.loading" /></label>
          <p v-if="addModal.error" class="modal-error">{{ addModal.error }}</p>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="closeAdd" :disabled="addModal.loading">Cancelar</button>
            <button type="submit" class="btn-save" :disabled="addModal.loading">{{ addModal.loading ? 'Guardando…' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Editar -->
    <div v-if="editModal.open" class="modal-overlay" @click.self="closeEdit">
      <div class="modal">
        <h3>Editar foto</h3>
        <form class="modal-form" @submit.prevent="submitEdit">
          <label class="form-label">Título<input v-model="editForm.title" type="text" required :disabled="editModal.loading" /></label>
          <label class="form-label">Descripción<textarea v-model="editForm.description" rows="3" required :disabled="editModal.loading" /></label>
          <label class="form-label">Nueva imagen (opcional)<input type="file" accept="image/*" @change="onEditFile" :disabled="editModal.loading" /></label>
          <img v-if="editForm.currentImageUrl" :src="editForm.currentImageUrl" alt="Actual" class="edit-preview" />
          <p v-if="editModal.error" class="modal-error">{{ editModal.error }}</p>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="closeEdit" :disabled="editModal.loading">Cancelar</button>
            <button type="submit" class="btn-save" :disabled="editModal.loading">{{ editModal.loading ? 'Guardando…' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:1rem}
.btn-back{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.85rem;color:#555;transition:border-color .2s;white-space:nowrap}
.btn-back:hover{border-color:#c8748a;color:#c8748a}
.header-title{font-size:1.05rem;font-weight:700;color:#1a1a1a;flex:1}
.btn-add-photo{padding:.55rem 1.1rem;background:#c8748a;color:white;border:none;border-radius:9px;font-size:.875rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:background .2s;margin-left:auto}
.btn-add-photo:hover{background:#b5637a}
.admin-main{max-width:1100px;margin:0 auto;padding:2rem 1.5rem}
.state-msg{text-align:center;padding:3rem;color:#888}
.state-msg--error{color:#c0392b}
.empty{text-align:center;padding:3rem;color:#aaa;display:flex;flex-direction:column;align-items:center;gap:1rem}
.btn-primary{padding:.65rem 1.25rem;background:#c8748a;color:white;border:none;border-radius:9px;font-size:.9rem;font-weight:700;cursor:pointer}
.photos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem}
.photo-card{background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;flex-direction:column}
.photo-card__img{width:100%;height:180px;object-fit:cover}
.photo-card__info{padding:.9rem;flex:1}
.photo-card__title{font-weight:700;font-size:.9rem;margin:0 0 .4rem;color:#1a1a1a}
.photo-card__desc{font-size:.8rem;color:#777;margin:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.photo-card__actions{display:flex;gap:.5rem;padding:.75rem;border-top:1px solid #f0f0f0}
.btn-edit,.btn-delete{flex:1;padding:.4rem;border:none;border-radius:7px;font-size:.8rem;cursor:pointer;font-weight:600}
.btn-edit{background:#f0ece8;color:#555}.btn-edit:hover{background:#e5e1dd}
.btn-delete{background:#fde8e8;color:#c0392b}.btn-delete:hover{background:#f5c6c6}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:100;padding:1rem}
.modal{background:white;border-radius:16px;padding:2rem;width:100%;max-width:480px;max-height:90vh;overflow-y:auto}
.modal h3{margin:0 0 1.5rem;font-size:1.1rem;font-weight:800}
.modal-form{display:flex;flex-direction:column;gap:1rem}
.form-label{display:flex;flex-direction:column;gap:.35rem;font-size:.825rem;font-weight:700;color:#444}
.form-label input,.form-label textarea{padding:.65rem .85rem;border:1.5px solid #e5e1dc;border-radius:8px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s;background:white}
.form-label input:focus,.form-label textarea:focus{border-color:#c8748a}
.edit-preview{width:100%;max-height:160px;object-fit:cover;border-radius:8px}
.modal-error{color:#c0392b;font-size:.85rem;margin:0}
.modal-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:.5rem}
.btn-cancel{padding:.7rem 1.2rem;background:#f0ece8;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:600;color:#555}
.btn-save{padding:.7rem 1.5rem;background:#c8748a;color:white;border:none;border-radius:8px;cursor:pointer;font-size:.875rem;font-weight:700}
.btn-save:disabled{opacity:.55;cursor:not-allowed}
</style>