# Plan: foto de la sección (sustituye al emoji)

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca
> Estado: **ejecutado** (queda la prueba manual de subida desde el panel)

## Contexto

El modal de crear/editar sección del panel (`/dulzia-panel`) pedía un **emoji** (texto) que se
usaba como icono del servicio en toda la web. Ahora se sube una **foto** con la misma
funcionalidad que las fotos de galería (`BaseFileUpload` + recorte), y esa foto sustituye al emoji
en la portada, el catálogo, la ficha del servicio y el panel.

**Decisiones tomadas:**
1. La foto sustituye al emoji **en toda la web**, incluidas las 6 tarjetas de acceso rápido del
   hero de portada.
2. Si una sección **no tiene foto propia** se usa **la primera foto de su galería**; si tampoco
   tiene, se cae al **emoji** (se conserva en BD como último respaldo, ya no editable en el panel).
3. La **X** del componente de subida **quita** la foto de la sección (vuelve al respaldo).

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Columna `service.image_url` (entidad + migración) | ✅ Hecha |
| 2 | Subida en la misma petición (multipart) + borrado en R2 | ✅ Hecha |
| 3 | Serialización: `image` resuelta (público y panel) + `imageUrl` propia (detalle del panel) | ✅ Hecha |
| 4 | Modal del panel: campo de foto, FormData, X = quitar, miniatura en la tabla | ✅ Hecha |
| 5 | Web pública: tarjetas, hero de la ficha y hero de portada | ✅ Hecha |
| 6 | SEO: `image` en el JSON-LD del catálogo + snapshot de prerender regenerado | ✅ Hecha |
| 7 | Tests (backend unitarios/integración + frontend) | ✅ Hechos (123 + 50 + 24 en verde) |
| 8 | Prueba manual de subida desde el panel | ⬜ Pendiente (requiere sesión de admin) |

## Detalle por etapa

### 1. Esquema

- `backend/src/Entity/Service.php`: `?string $imageUrl` (VARCHAR 500, nullable),
  `getImageUrl()`/`setImageUrl()`, y `getDisplayImage()` con la resolución
  propia → primera de la galería → `null`. `toArray()` emite `image`.
- `backend/migrations/Version20260924180000.php`: `ALTER TABLE service ADD image_url VARCHAR(500) DEFAULT NULL`.
- El orden de `examples` pasa a `sortOrder ASC, id ASC` (desempate determinista para el respaldo).

### 2. Subida

- `ServiceCommandFactory::parse()` acepta **JSON y multipart** (`getContentTypeFormat()`); en
  multipart `features` llega como `features[]`. `emoji` pasa a opcional: `null` en la edición
  significa *conservar el actual* (si no, cada guardado del panel habría borrado el emoji de
  respaldo de las 11 secciones).
- `CreateServiceCommand`/`UpdateServiceCommand` ganan `?UploadedFile $image` y
  `UpdateServiceCommand` además `bool $removeImage`.
- Handlers: suben con `FileStorageInterface::store()` y borran la anterior **después** de guardar
  (si el guardado falla, la foto vieja sigue existiendo; un objeto huérfano es más barato que una
  imagen rota).
- **La ruta de edición pasa a `PUT · POST`**: PHP solo rellena `$_POST`/`$_FILES` en peticiones
  POST, así que el multipart va por POST (igual que la edición de fotos, que ya usa POST). El PUT
  con JSON se mantiene por compatibilidad.

### 3. Serialización

- Público (`Service::toArray()`): `image` resuelta → la consumen portada, catálogo y ficha.
- Panel listado (`ListServicesHandler`): `image` resuelta (miniatura).
- Panel detalle (`GetServiceHandler`): `imageUrl` (propia, la que edita el modal) e `image`
  (resuelta, la que se ve). El modal lee `imageUrl`; si leyera `image`, la X parecería quitar la
  foto de la galería.

### 4. Panel

- `AdminDashboardPage.vue`: el input de Emoji se sustituye por `BaseFileUpload`
  (`:current-image` + `@change`), con un aviso de qué se está mostrando cuando no hay foto propia.
  El formulario se envía como `FormData` (`features[]`, `image` solo si hay fichero, `removeImage`).
  Miniatura de 36 px en la tabla (respaldo: emoji).
- `AdminServiceDetailPage.vue`: miniatura de 28 px en la cabecera.
- `adminService.js`: `apiCreateService`/`apiUpdateService` aceptan JSON o `FormData` (multipart
  siempre por POST, sin `Content-Type` manual).

### 5. Web pública

- `ServiceCard.vue`: `card__img` (16/10, `object-fit: cover`, `loading="lazy"`) con respaldo al
  bloque de emoji; `ServicioDetallePage.vue`: `hero__photo` de 128 px en el mismo hueco que el
  emoji; `HeroSection.vue`: las 6 tarjetas de acceso rápido del hero son cuadrados con la foto a
  sangre y el nombre encima (degradado), **sin emoji**, y solo se muestran cuando su foto está
  cargada. El catálogo lo carga `HomePage.vue` **una sola vez** y se lo pasa a `HeroSection` y
  `ServicesOverview` (antes cada uno pedía `/api/services`).

### 6. SEO

- `useSeo.js`: cada `Service` del `ItemList` incluye `image` cuando existe.
- Snapshot de prerender regenerado (`frontend/prerender-data/snapshot.json`, incluye `image`).

## Verificación hecha

- `make migrate` aplicada y `image_url` presente en la BD de desarrollo.
- `GET /api/services`: los 11 servicios resuelven su `image` a la primera foto de galería y
  conservan el emoji.
- `make test-unit` (123), `make test-integration` (50) y `npm --prefix frontend run test` (24) en verde.
- `npm run build`: prerender de las 15 rutas con foto en las 6 tarjetas del hero, las 6 del catálogo,
  las 11 fichas de servicio y el JSON-LD.

## Pendiente

- **Subida real desde el panel** (subir una foto, ver la miniatura, pulsar la X y comprobar que
  vuelve a la foto de la galería). No se pudo automatizar: hace falta sesión de admin, y crear un
  usuario temporal no es algo que deba hacerse sin permiso.
- **Producción**: ejecutar la migración en el despliegue
  (`php bin/console doctrine:migrations:migrate --no-interaction`).
