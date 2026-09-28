# Plan: banner informativo de portada

> Plan de implementación — Fecha: 2026-09-28 · Proyecto: dulziasalamanca
> Estado: **ejecutado**

## Contexto

La web no tiene forma de anunciar algo puntual ("este puente en X", "nuevo servicio",
"reserva tu fecha") sin tocar código. Se pide un **banner informativo** arriba del todo,
sobre la web entera (con posibilidad de cerrarlo), con **título**, **descripción** y una
ventana de **desde / hasta** que lo haga aparecer y desaparecer solo, más su zona de
**creación y edición** en el admin.

**Decisiones tomadas:**

1. **Entidad propia `banner`, no filas de settings.** La tabla `settings` es clave/valor
   para valores sueltos (email, teléfono); un banner se crea, se edita, se borra y pueden
   convivir varios (el de esta semana y el que se programe para el mes que viene). Eso pide
   tabla propia con su migración, como `service` o `category`.
2. **La ventana la mandan las fechas**: un banner se ve solo si hoy está dentro de
   `desde`–`hasta` (días de calendario, ambos inclusive, sin horas ni zonas horarias).
   Así se puede preparar un aviso futuro y olvidarse de quitarlo.
3. **Un solo banner activo en la web a la vez**: si coinciden varios en rango, manda el
   actualizado más reciente. La regla es simple y el admin lo ve de un vistazo (estado
   Activo / Programado / Caducado).
4. **Va arriba del todo, sobre la web entera**: una barra fija por encima de la
   navegación, visible en **todas las páginas** (se monta en `App.vue`, no en la portada),
   con **botón de cerrar** (✕). La altura de la barra se mide en vivo con `ResizeObserver`
   y se publica como variable CSS (`--banner-h`): el nav fijo y su espaciador se desplazan
   lo que mida, así da igual que el texto ocupe una línea o dos. Al cerrar, la variable
   vuelve a 0 y el nav sube a su sitio.
5. **El cierre se recuerda por sesión**: al cerrar se guarda el id del banner en
   `sessionStorage` y no vuelve a salir aunque se navegue o se recargue; en la siguiente
   visita (sesión nueva) el aviso aparece otra vez, que es lo que se espera de un anuncio.
   Si el admin lo edita con el mismo id, para la sesión en curso sigue cerrado.
6. **Las fechas no se enseñan en la web**: son de administración. El visitante ve el aviso
   mientras toca y deja de verlo cuando caduca, sin más.
7. **Sin datos estructurados**: un aviso temporal no es contenido de negocio para JSON-LD.
8. **El borrado es simple**: el banner no lo referencia nada, así que no hay 409 como en
   categorías; borrar es confirmar y fuera.
9. **La API pública solo devuelve el activo** (`GET /api/banner`, como `/api/contact`): la
   web no necesita saber que existen banners caducados, y así el HTML prerenderizado
   funciona igual que con el contacto (clave `banner` en el snapshot).

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Entidad `Banner` + migración + repositorio (Domain/Infrastructure) | ✅ Hecha |
| 2 | Aplicación: Create/Update/Delete/List/GetActive + controladores (admin y público) | ✅ Hecha |
| 3 | Tests PHPUnit del backend | ✅ Hechos (222 unitarios + 96 de integración en verde) |
| 4 | `useBanner` + `BannerBar` global en `App.vue` (fija, con cierre por sesión) + clave `banner` en el snapshot | ✅ Hecha |
| 5 | Tarjeta en el panel + `AdminBannersPage` (lista, alta, edición, borrado) + ruta | ✅ Hecha |
| 6 | Tests Vitest + build con el HTML prerenderizado | ✅ Hechos (132 en verde; HTML sin barra con `banner: null`) |
| 7 | `PROJECT_SPEC.md` y commit | ✅ Hecho (commit `25c8204`) |
| 8 | Despliegue: `make migrate` + snapshot + revisión en producción | ⬜ Pendiente (deploy del usuario) |

## Desviaciones sobre el plan

- **La ventana al revés se valida con `Assert\Callback`** en el command, no con
  `Assert\Expression`: este último exige la dependencia `symfony/expression-language`, que
  no compensa instalar para una sola comparación. Sigue siendo 422 con el mismo mensaje,
  ahora asociado al campo `endsAt`.
- **Los tests que ordenan por `updated_at` duermen ~1 s**: la columna es `TIMESTAMP(0)` y
  varias escrituras en el mismo segundo empatarían (en producción un humano no crea y edita
  en el mismo segundo; el test sí).
- **Tras el despliegue (28/09)**: a ≤400 px la descripción de la barra ya no se parte, se
  corta con puntos — así la barra mide siempre 64 px (una línea) y coincide con la altura
  horneada por el prerender: antes, a 320 px crecía a 82 px y el nav tapaba la segunda
  línea hasta que montaba el JS. De paso, el botón "Ver todos los servicios (N)" del
  ServicesOverview desbordaba 15 px a 320 px: mismo arreglo que el CTA de reseñas.

## Detalle por etapa

### 1. Entidad y repositorio

- `Entity/Banner.php`: `id`, `title` (string, 120), `description` (text), `startsAt`
  (DATE, obligatoria), `endsAt` (DATE, obligatoria), `createdAt`/`updatedAt` con
  lifecycle callbacks, como `ContactSubmission`.
- Migración `Version20260928xxxxxx.php` con la tabla `banner` (las columnas de fechas
  como `DATE`, no `DATETIME`: el banner vive en días).
- `Domain/Banner/BannerRepositoryInterface.php`:
  `save()`, `remove()`, `find(int $id): ?Banner`, `findAll(): array` (para el admin,
  ordenado por `updatedAt` desc) y `findActiveOn(\DateTimeImmutable $date): ?Banner`
  (con `starts_at <= :d AND ends_at >= :d`, la más reciente).
- `Infrastructure/Persistence/Doctrine/DoctrineBannerRepository.php` y
  `Domain/Banner/BannerNotFoundException.php` (como el resto de excepciones de dominio).

### 2. Aplicación y controladores

- Comandos con validación `Assert` en las propiedades (la única fuente de verdad, como
  siempre):
  - `CreateBannerCommand` / `UpdateBannerCommand`: `title` NotBlank + `Length(max:120)`,
    `description` NotBlank, `startsAt`/`endsAt` como strings
    `Assert\DateTime(format: 'Y-m-d')`. La regla cruzada **hasta ≥ desde** se comprueba
    en el handler y lanza la excepción de dominio → 422 con mensaje claro.
  - `DeleteBannerCommand`: `id` + excepción si no existe (404).
  - `ListBannersQuery` (admin) y `GetActiveBannerQuery` (público, con `today` inyectado
    para poder testear la ventana).
- `AdminBannerController` (protegido como el resto del admin):
  `GET /api/admin/banners`, `POST /api/admin/banners`,
  `PUT /api/admin/banners/{id}`, `DELETE /api/admin/banners/{id}`.
- `BannerController` público: `GET /api/banner` →
  `{ "banner": { id, title, description, startsAt, endsAt } | null }`.
  El `ApiExceptionListener` ya convierte las excepciones de dominio en 404/422/409.
- En `adminService.js`: `apiGetBanners`, `apiCreateBanner`, `apiUpdateBanner`,
  `apiDeleteBanner`.

### 3. Tests PHPUnit

Handler de `GetActiveBanner` con ventanas (antes / dentro / después / borde inclusive /
varios activos → el más reciente), validación del comando (campos vacíos, fechas mal
formadas, hasta antes que desde → 422) y borrado de un id inexistente (404).

### 4. Web pública

- `composables/useBanner.js`, calco de `useContact`: caché reactiva de módulo, `pending`
  para deduplicar, snapshot de prerender como estado inicial (`window.__DULZIA_PRERENDER__.banner`)
  y `fetch` al montar. `banner` es un `ref(null)` cuando no hay aviso.
- `components/layout/BannerBar.vue` (va en `App.vue`, encima de `<NavBar />`):
  - Si no hay aviso o está cerrado, **no pinta nada**.
  - `position: fixed; top: 0; inset-inline: 0; z-index: 101` (por encima del nav, que
    queda en 100): siempre a la vista, en todas las páginas.
  - Un `ResizeObserver` sobre la barra publica su altura en
    `document.documentElement.style.setProperty('--banner-h', ...)`. El `NavBar` pasa a
    `top: var(--banner-h, 0px)` y el `.nav__spacer` a
    `height: calc($nav-height + var(--banner-h, 0px))`, así la página entera baja lo que
    mida la barra. Al desmontar se restaura a 0.
  - Botón ✕ con `aria-label="Cerrar aviso"`: guarda el id en `sessionStorage`
    (`banner-cerrado`), la barra desaparece y el nav sube. Los estilos son los de la web
    (fondo fucsia o pistacho con texto legible, título en negrita y descripción debajo,
    compacta en móvil).
- El comando de snapshot (documentado en `plan-seo.md`) añade la clave `banner`, igual
  que se hizo con `contact`.

### 5. Admin

- Nueva tarjeta en `AdminDashboardPage`: **📣 Banner informativo** → "El aviso que se ve
  arriba del todo en la web: título, texto y fechas" → `/dulzia-panel/banners`.
- Ruta `/dulzia-panel/banners` en el router.
- `AdminBannersPage.vue`, con el patrón de la página de categorías: cabecera con
  "← Panel" y botón "+ Nuevo banner", tabla con título, desde, hasta y **estado**
  (Activo / Programado / Caducado, calculado contra la fecha de hoy al pintar), modal de
  crear/editar con los cuatro campos (las fechas como `<input type="date">`) y borrar con
  confirmación. El estado va en una pastilla de color para encontrarlo de un vistazo.

### 6. Tests Vitest

`useBanner.spec.js` (dedupe, snapshot, sin banner activo),
`BannerBar.spec.js` (no pinta sin aviso; pinta título y descripción; al cerrar guarda en
`sessionStorage` y desaparece; no reaparece si el id está en `sessionStorage`),
`AdminBannersPage.spec.js` (alta, edición, borrado, estado calculado) y el ajuste del
spec de `HomePage` (petición a `/api/banner` en la lista de URLs permitidas).

### 7. Documentación

`PROJECT_SPEC.md`: entidad `banner`, los dos endpoints nuevos, la página de admin y la
sección de portada.

## Puntos abiertos (decidir antes de la etapa 2)

1. **`hasta` obligatoria** o dejar el aviso indefinido si se vacía. Propuesto: obligatoria
   (el usuario pidió "desde y hasta"; si algún día quieren un aviso sin caducidad, se pone
   una fecha lejana o se añade el caso).
2. **Eliminar al caducar o conservar**: se conservan en la lista (historial por si se
   repiten); borrar es manual. Si molestan, se añade un filtro más adelante.
3. **Cierre por sesión o permanente**: propuesto `sessionStorage` (al día siguiente el
   aviso vuelve a salir). Si se prefiere que un aviso cerrado no moleste más, se cambia a
   `localStorage` con un botón de "restaurar" en el admin; no hace falta decidirlo ahora,
   la diferencia es una línea.

## Verificación

1. `vendor/bin/phpunit` y `npm --prefix frontend run test` en verde.
2. `npm run build`: el HTML prerenderizado sale con la barra solo si hay un banner activo
   en el momento del build, y no rompe sin él.
3. Manual (requiere sesión de admin): crear un banner con la ventana de hoy y verlo arriba
   del todo en todas las páginas (también en `/servicios` y `/contacto`), con el nav
   debajo sin taparlo; cerrarlo con la ✕ y ver que el nav sube; recargar y comprobar que
   sigue cerrado; cambiar `hasta` a ayer y verlo desaparecer; borrarlo. Todo también en el
   móvil.
4. Despliegue: `make migrate` y regenerar el snapshot.
