const BASE_URL = import.meta.env.VITE_API_URL ?? ''

async function request(method, path, body) {
  const res = await fetch(`${BASE_URL}${path}`, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: body ? JSON.stringify(body) : undefined,
  })

  const data = await res.json().catch(() => ({}))

  if (!res.ok) {
    const err = new Error(data.error ?? 'Error de servidor')
    err.status = res.status
    err.data = data
    throw err
  }

  return data
}

export const api = {
  post: (path, body) => request('POST', path, body),
  get:  (path) => request('GET', path),
}
