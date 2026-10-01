# Plan: entrar directo al panel si el token sigue válido

> Plan de implementación — fichero versionado en `docs/` del repo.
> Fecha: 2026-10-01 · Proyecto: dulziasalamanca · Estado: **en ejecución**

## Contexto

Hoy `/dulzia-panel/login` muestra siempre el formulario, aunque `localStorage.admin_token`
contenga un token válido. El token es opaco (64 hex) y su validez vive solo en el servidor
(tabla `admin_token`, caducidad de 8 horas en `DatabaseAdminTokenStore`), así que el navegador
no puede saber si sigue valiendo sin preguntar.

**Objetivo:** al entrar en `/dulzia-panel/login`, si hay token guardado y sigue válido →
entrar directo al panel; si no hay token o está caducado/invalidado → formulario de siempre.

**Decisiones tomadas:**

1. **Endpoint nuevo `GET /api/admin/check`** en vez de reusar otro GET de admin como sonda:
   una petición a `/api/admin/services` valida el token pero descarga el catálogo entero para
   tirarlo; el check es una línea y queda utilizable por cualquier pantalla futura.
   La auth es automática: `AdminAuthListener` protege todo `/api/admin/*` salvo el login,
   así que el endpoint **no valida nada por sí mismo** — token válido → 200 `{ok: true}`,
   caducado/ausente → 401 JSON (vía `ApiExceptionListener`). Sin handler nuevo: no hay caso
   de uso, solo un eco del estado que ya comprueba el listener.
2. **La comprobación vive en la pantalla de login**, no en el guard del router: el guard
   (`requiresAuth`) solo mira si el token existe, que es lo que pide este plan no tocar.
   La validación real ocurre en el único sitio donde hoy se decide "formulario o panel".
3. **Sin cambios en `useAuth`**: su `isLoggedIn` es presencia de token, y aquí no se usa.
   La página importa `apiCheckToken` directamente, igual que otras páginas importan sus
   servicios de datos.

---

## Estado de ejecución

> Leyenda: ⬜ Pendiente · 🔄 En curso · ✅ Hecha

| Etapa | Estado | Fecha |
|---|---|---|
| 0 — Guardar este plan en `docs/plan-auto-login.md` | ✅ Hecha | 2026-10-01 |
| 1 — Backend: `GET /api/admin/check` + tests de integración | ✅ Hecha | 2026-10-01 |
| 2 — Frontend: `apiCheckToken()` y lógica en `AdminLoginPage.vue` | ✅ Hecha | 2026-10-01 |
| 3 — Test de frontend `AdminLoginPage.spec.js` | ✅ Hecha | 2026-10-01 |
| 4 — Verificación end-to-end | 🔄 En curso (falta la prueba manual en navegador) | |

---

## Etapa 0 — Guardar el plan en `docs/` ✅ (2026-10-01)

Crear `docs/plan-auto-login.md` con este contenido, incluida la sección **Estado de ejecución**.

**Criterios de aceptación**
- El fichero existe en `docs/` y su tabla de estado refleja la etapa en curso. ✅

## Etapa 1 — Backend: `GET /api/admin/check` + tests ✅ (2026-10-01)

| Fichero | Contenido |
|---|---|
| `backend/src/Controller/AdminAuthController.php` | Método `check()`: `#[Route('/api/admin/check', methods: ['GET'])]` → `JsonResponse(['ok' => true])`. Comentario aclarando que **no valida nada**: si llega aquí es que `AdminAuthListener` ya aceptó el Bearer (es el patrón de `logout()`) |
| `backend/tests/Integration/Controller/AdminAuthControllerTest.php` | Tres tests: 200 + `{ok:true}` con token real (via `$this->login()`); 401 sin cabecera; 401 con token inventado de 64 hex (no existe en la tabla) |

**Criterios de aceptación**
- `make test-integration` en verde, incluidos los tres casos nuevos. ✅

## Etapa 2 — Frontend: servicio + pantalla de login ✅ (2026-10-01)

**Editados**

- `frontend/src/services/adminService.js` — en la sección `// ── Auth ──`, `apiCheckToken()`:
  `GET ${BASE}/admin/check` con `headers()`. `handleResponse` ya convierte el 401 en
  `Error('401')`, que es justo la señal que consume la página.
- `frontend/src/pages/admin/AdminLoginPage.vue` — en `onMounted`:
  - Sin token en `localStorage` → nada, se muestra el formulario de siempre (sin petición).
  - Con token → estado `checking = true` y `apiCheckToken()`:
    - ok → `router.push('/dulzia-panel')`
    - `401` → `localStorage.removeItem('admin_token')` (token caducado/invalidado) y formulario
    - otro error (red, servidor caído) → formulario **sin borrar el token**: puede seguir
      valiendo y borrarlo solo empeora; si el usuario re-usa el formulario, `login()` lo
      sustituye igualmente.
  - Mientras `checking`, en vez del formulario se muestra un aviso
    `Comprobando sesión…` (el logo y el título del card se quedan), para que no se vea el
    parpadeo formulario → redirect.

**Criterios de aceptación**
- `npm run build` de producción correcto. ✅

## Etapa 3 — Test de frontend ✅ (2026-10-01)

**Nuevo**

- `frontend/__tests__/AdminLoginPage.spec.js` — esqueleto de `AdminContactSettingsPage.spec.js`
  (`// @vitest-environment jsdom`, mock de `vue-router` con `push = vi.fn()`, mock de
  `@/services/adminService` con `apiCheckToken`/`apiLogin`, `vi.clearAllMocks()` +
  `localStorage.clear()` en `beforeEach`). Casos:
  1. Token guardado y check ok → `push('/dulzia-panel')`.
  2. Sin token → no llama a `apiCheckToken` y muestra el formulario (`#username`).
  3. Token guardado y check responde 401 → borra el token, muestra el formulario, no navega.
  4. Check falla por otro motivo → muestra el formulario y **mantiene** el token.
  5. Login manual: rellena usuario/contraseña, submit → `apiLogin` llamado con los valores
     y `push('/dulzia-panel')` (regresión del flujo existente).

**Criterios de aceptación**
- `make test-frontend` en verde. ✅

## Etapa 4 — Verificación end-to-end 🔄 (2026-10-01)

**Hecho (2026-10-01):**
- `make test` completo en verde: 222 unitarios (682 aserciones), 99 de integración
  (361 aserciones, incluyen los 3 casos nuevos de `/check`), 139 de frontend
  (incluyen los 6 casos nuevos de la pantalla de login).
- Sonda real contra el backend de dev (`:8000`, código montado en caliente):
  `GET /api/admin/check` sin cabecera → **401** `{"error":"No autorizado"}`; con un
  token válido recién insertado → **200** `{"ok":true}` (fila de prueba borrada después).
- `npm run build` de producción correcto y el prerender sigue diciendo
  "All routes rendered successfully!".

**Pendiente (necesita navegador):**
1. Sin token → `/dulzia-panel/login` muestra el formulario y no hay petición a `/check`.
2. Login → entra al panel. Recargar `/dulzia-panel/login` → "Comprobando sesión…" y
   redirect directo al panel.
3. `docker exec -i shared-postgres-db psql -U postgres -d dulzia -c "DELETE FROM admin_token"`
   y recargar `/dulzia-panel/login` → formulario y token borrado de localStorage.

Producción (cuando corresponda): `git pull` → `make prod-up` (reconstruye el frontend;
el backend solo añade una ruta, sin migración).

---

## Riesgos

- **Doble petición al entrar**: el check es un GET ligero (una consulta por índice único), así
  que el coste de entrar con token válido es esa petición + el redirect. Asumible.
- **El guard del router sigue mirando solo presencia de token**: con un token caducado se puede
  entrar a `/dulzia-panel` y ver 401 en cada API (comportamiento preexistente). Este plan no lo
  toca; si molesta, el `GET /check` ya queda disponible para endurecerlo en otro plan.
- **`failOnWarning`/`failOnNotice` en PHPUnit a `true`**: no hay casts ni claves inexistentes en
  el código nuevo.
- **Prerender**: `/dulzia-panel/login` no está en las rutas de `vite-plugin-prerender`, así que
  el cambio no toca el snapshot SEO. Verificar igualmente que el build dice
  "All routes rendered successfully!".
