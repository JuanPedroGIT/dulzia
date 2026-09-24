// Usar VITE_API_URL si está definida, sino usar /api (proxy de Vite en dev / nginx en prod)
const BASE = import.meta.env.VITE_API_URL || '/api'

function headers() {
  const token = localStorage.getItem('admin_token')
  return { Authorization: `Bearer ${token}` }
}

async function handleResponse(res) {
  if (res.status === 401) throw new Error('401')
  const data = await res.json()
  if (!res.ok) {
    // Los 422 de validación llegan como {errors: {campo: ["mensaje"]}}: sin
    // esto el usuario vería un escueto "Error 422".
    const fieldErrors = data.errors && Object.values(data.errors).flat().filter(Boolean)
    throw new Error(data.error || fieldErrors?.[0] || `Error ${res.status}`)
  }
  return data
}

// ── Auth ─────────────────────────────────────────────────────────────────

export async function apiLogin(username, password) {
  const res = await fetch(`${BASE}/admin/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password }),
  })
  return handleResponse(res)
}

export async function apiLogout() {
  await fetch(`${BASE}/admin/logout`, { method: 'POST', headers: headers() })
}

// ── Secciones ─────────────────────────────────────────────────────────────

export async function apiGetServices() {
  const res = await fetch(`${BASE}/admin/services`, { headers: headers() })
  return handleResponse(res)
}

export async function apiGetService(id) {
  const res = await fetch(`${BASE}/admin/services/${id}`, { headers: headers() })
  return handleResponse(res)
}

export async function apiCreateService(data) {
  const isForm = data instanceof FormData
  const res = await fetch(`${BASE}/admin/services`, {
    method: 'POST',
    // Con FormData no se fija Content-Type: lo pone el navegador con el boundary.
    headers: isForm ? headers() : { ...headers(), 'Content-Type': 'application/json' },
    body: isForm ? data : JSON.stringify(data),
  })
  return handleResponse(res)
}

export async function apiUpdateService(id, data) {
  // La edición con fichero (FormData) va por POST: PHP solo rellena $_POST/$_FILES
  // en peticiones POST. El JSON mantiene el PUT de siempre.
  const isForm = data instanceof FormData
  const res = await fetch(`${BASE}/admin/services/${id}`, {
    method: isForm ? 'POST' : 'PUT',
    headers: isForm ? headers() : { ...headers(), 'Content-Type': 'application/json' },
    body: isForm ? data : JSON.stringify(data),
  })
  return handleResponse(res)
}

export async function apiDeactivateService(id) {
  const res = await fetch(`${BASE}/admin/services/${id}`, {
    method: 'DELETE',
    headers: headers(),
  })
  return handleResponse(res)
}

export async function apiActivateService(id) {
  const res = await fetch(`${BASE}/admin/services/${id}/activate`, {
    method: 'POST',
    headers: headers(),
  })
  return handleResponse(res)
}

// ── Mensajes ──────────────────────────────────────────────────────────────

export async function apiGetMessages(page = 1) {
  const res = await fetch(`${BASE}/admin/messages?page=${page}`, { headers: headers() })
  return handleResponse(res)
}

export async function apiGetMessage(id) {
  const res = await fetch(`${BASE}/admin/messages/${id}`, { headers: headers() })
  return handleResponse(res)
}

export async function apiMarkMessageRead(id, read) {
  const res = await fetch(`${BASE}/admin/messages/${id}/${read ? 'read' : 'unread'}`, {
    method: 'POST',
    headers: headers(),
  })
  return handleResponse(res)
}

export async function apiDeleteMessage(id) {
  const res = await fetch(`${BASE}/admin/messages/${id}`, {
    method: 'DELETE',
    headers: headers(),
  })
  return handleResponse(res)
}

// ── Ajustes de email ──────────────────────────────────────────────────────

export async function apiGetContactRecipient() {
  const res = await fetch(`${BASE}/admin/settings/contact-recipient`, {
    headers: headers(),
  })
  return handleResponse(res)
}

export async function apiUpdateContactRecipient(data) {
  const res = await fetch(`${BASE}/admin/settings/contact-recipient`, {
    method: 'PUT',
    headers: { ...headers(), 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  })
  return handleResponse(res)
}

// ── Fotos ─────────────────────────────────────────────────────────────────

export async function apiAddPhoto(serviceId, formData) {
  const res = await fetch(`${BASE}/admin/services/${serviceId}/photos`, {
    method: 'POST',
    headers: headers(),
    body: formData,
  })
  return handleResponse(res)
}

export async function apiUpdatePhoto(photoId, formData) {
  const res = await fetch(`${BASE}/admin/photos/${photoId}`, {
    method: 'POST',
    headers: headers(),
    body: formData,
  })
  return handleResponse(res)
}

export async function apiDeletePhoto(photoId) {
  const res = await fetch(`${BASE}/admin/photos/${photoId}`, {
    method: 'DELETE',
    headers: headers(),
  })
  return handleResponse(res)
}
