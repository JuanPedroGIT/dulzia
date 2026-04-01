// Usar VITE_API_URL si está definida, sino usar ruta relativa (para proxy via nginx)
const BASE = import.meta.env.VITE_API_URL || ''

function headers() {
  const token = localStorage.getItem('admin_token')
  return { Authorization: `Bearer ${token}` }
}

async function handleResponse(res) {
  if (res.status === 401) throw new Error('401')
  const data = await res.json()
  if (!res.ok) throw new Error(data.error || `Error ${res.status}`)
  return data
}

// ── Auth ─────────────────────────────────────────────────────────────────

export async function apiLogin(username, password) {
  const res = await fetch(`${BASE}/api/admin/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password }),
  })
  return handleResponse(res)
}

export async function apiLogout() {
  await fetch(`${BASE}/api/admin/logout`, { method: 'POST', headers: headers() })
}

// ── Secciones ─────────────────────────────────────────────────────────────

export async function apiGetServices() {
  const res = await fetch(`${BASE}/api/admin/services`, { headers: headers() })
  return handleResponse(res)
}

export async function apiGetService(id) {
  const res = await fetch(`${BASE}/api/admin/services/${id}`, { headers: headers() })
  return handleResponse(res)
}

export async function apiCreateService(data) {
  const res = await fetch(`${BASE}/api/admin/services`, {
    method: 'POST',
    headers: { ...headers(), 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  })
  return handleResponse(res)
}

export async function apiUpdateService(id, data) {
  const res = await fetch(`${BASE}/api/admin/services/${id}`, {
    method: 'PUT',
    headers: { ...headers(), 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  })
  return handleResponse(res)
}

export async function apiDeactivateService(id) {
  const res = await fetch(`${BASE}/api/admin/services/${id}`, {
    method: 'DELETE',
    headers: headers(),
  })
  return handleResponse(res)
}

export async function apiActivateService(id) {
  const res = await fetch(`${BASE}/api/admin/services/${id}/activate`, {
    method: 'POST',
    headers: headers(),
  })
  return handleResponse(res)
}

// ── Fotos ─────────────────────────────────────────────────────────────────

export async function apiAddPhoto(serviceId, formData) {
  const res = await fetch(`${BASE}/api/admin/services/${serviceId}/photos`, {
    method: 'POST',
    headers: headers(),
    body: formData,
  })
  return handleResponse(res)
}

export async function apiUpdatePhoto(photoId, formData) {
  const res = await fetch(`${BASE}/api/admin/photos/${photoId}`, {
    method: 'POST',
    headers: headers(),
    body: formData,
  })
  return handleResponse(res)
}

export async function apiDeletePhoto(photoId) {
  const res = await fetch(`${BASE}/api/admin/photos/${photoId}`, {
    method: 'DELETE',
    headers: headers(),
  })
  return handleResponse(res)
}
