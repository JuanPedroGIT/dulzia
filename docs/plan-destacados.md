# Plan: servicios destacados en la portada (check en el panel)

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca
> Estado: **ejecutado** (queda la prueba manual desde el panel)

## Contexto

Qué sale en la portada está fijado en el código y no se puede tocar desde el panel:

- Las **6 tarjetas del hero** (`HeroSection.vue`) son una lista de ids escrita a mano
  (`carrito-hot-dog`, `glitter-bar`, `fuente-chocolate`, `candy-bar`, `photocall`, `mini-ferias`),
  con etiquetas cortas propias ("Hot Dog", "Chocolate"…).
- La parrilla de **"Servicios que enamoran"** (`ServicesOverview.vue`) coge los **6 primeros del
  catálogo por `sort_order`**, sin criterio editorial.

Cambiar cualquiera de las dos cosas hoy es tocar código y desplegar.

**Decisiones tomadas:**

1. **Una sola columna** `is_featured` en `service`: el servicio marcado sale en las dos zonas
   (hero y parrilla). Un único check que explicar.
2. En el hero, la etiqueta de cada tarjeta pasa a ser **el nombre del servicio** (desaparecen las
   etiquetas cortas escritas a mano).
3. El check se pone **en la propia fila de la tabla** del panel y se guarda al instante.

**Consecuencias asumidas:**

- Las dos zonas pasan a mostrar el **mismo conjunto**. Hoy no coinciden (el hero tiene
  glitter-bar, photocall y mini-ferias; la parrilla tiene palomitero, algodón y carteles).
- La migración **marca los 6 del hero**, que es el conjunto curado a mano y el que se nombró: la
  portada queda igual que hoy arriba y la parrilla pasa a mostrar esos 6. Desde el panel se cambia
  en dos clics.
- El hero se queda en **6 tarjetas como máximo** (las primeras por `sort_order`); si se marcan
  menos, se muestran las que haya (la rejilla es de 3 columnas y se adapta). La parrilla muestra
  **todas** las marcadas, y si no hay ninguna la sección no se pinta.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Esquema: `service.is_featured` + migración con el backfill de los 6 del hero | ✅ Hecha |
| 2 | Backend: entidad, `SetFeatured` (command + handler), ruta y serialización | ✅ Hecha |
| 3 | Panel: check en la fila de la tabla que guarda al instante | ✅ Hecha |
| 4 | Portada: hero y parrilla desde los servicios marcados | ✅ Hecha |
| 5 | Tests (PHPUnit + Vitest) | ✅ Hechos (149 + 59 + 54 en verde) |
| 6 | Snapshot de prerender + `PROJECT_SPEC.md` | ✅ Hechos |
| 7 | Prueba manual de marcado desde el panel | ⬜ Pendiente (requiere sesión de admin) |

## Detalle por etapa

### 1. Esquema

- `Entity/Service.php`: `bool $isFeatured = false` con `isFeatured()` y `setFeatured(bool)`
  (en espejo con `isActive`/`activate()`/`deactivate()`).
- Migración `Version20260924210000.php`:
  `ALTER TABLE service ADD is_featured BOOLEAN NOT NULL DEFAULT FALSE` y un `UPDATE` que marca los
  6 ids del hero, para que la portada no se quede vacía al desplegar.

### 2. Backend

- `Application/Service/SetFeatured/`: `SetFeaturedCommand(id, featured)` + `SetFeaturedHandler`
  (busca, `setFeatured()`, `save()`; `NotFoundException` si no existe). Mismo patrón que
  `ActivateServiceHandler`.
- `AdminServiceController`: `POST /api/admin/services/{id}/featured` con body JSON/forma
  `{"featured": true|false}` — un solo endpoint idempotente en lugar de dos rutas
  (`feature`/`unfeature`), que para un check es la mitad de código. El booleano se lee con
  `filter_var(..., FILTER_VALIDATE_BOOLEAN)` como el `removeImage` de `ServiceCommandFactory`.
- Serialización: `Service::toArray()` (catálogo público) y `ListServicesHandler` (tabla del panel)
  emiten `featured`.

### 3. Panel

- `AdminDashboardPage.vue`: columna **Portada** con un check por fila. Al cambiarlo llama al
  endpoint y actualiza la fila en memoria (sin recargar la tabla entera); si falla, revierte y
  avisa. El `colspan` de la fila vacía pasa de 5 a 6.
- `adminService.js`: `apiSetServiceFeatured(id, featured)`.

### 4. Portada

- `HeroSection.vue`: fuera la lista de ids escrita a mano; las tarjetas salen de
  `services.filter(s => s.featured).slice(0, 6)` y la etiqueta es `service.name`. Cada
  tarjeta es un `router-link` a `/servicios/{id}`. Se mantiene el patrón de "la tarjeta
  no se pinta hasta que su foto carga".
- `ServicesOverview.vue`: la parrilla muestra **todas** las marcadas; sin ninguna, la sección no
  se pinta (antes enseñaba los 6 primeros del catálogo, que no era una decisión editorial).

### 5. Tests

- PHPUnit: entidad (`isFeatured` por defecto y setter), `SetFeaturedHandler` (marca, desmarca y
  404), integración (la ruta cambia el estado, aparece en el listado del panel y en el catálogo
  público, y no toca el resto de campos).
- Vitest: `HeroSection` (salen solo las marcadas, con el nombre como etiqueta, máximo 6),
  `ServicesOverview` (solo las marcadas y se oculta sin ninguna), `AdminDashboardPage` (el check
  llama al endpoint con el valor nuevo).

### 6. Documentación

- Regenerar `frontend/prerender-data/snapshot.json`: la portada prerenderizada necesita `featured`
  para pintar el hero.
- `docs/PROJECT_SPEC.md`: columna en las entidades, fila del endpoint y nota de qué pinta la
  portada.

## Verificación

1. `make test-unit`, `make test-integration`, `npm --prefix frontend run test` en verde.
2. `GET /api/services` devuelve `featured` y los 6 del hero marcados.
3. `npm run build`: el hero prerenderizado sale con 6 tarjetas y los nombres de servicio.
4. Manual (requiere sesión de admin): marcar y desmarcar desde la tabla y ver el cambio.
5. `make migrate` en el despliegue.

## Verificación hecha

- `make test-unit` (**149**), `make test-integration` (**59**) y `npm --prefix frontend run test`
  (**54**) en verde.
- Migración `Version20260924210000` aplicada en la BD de desarrollo: `is_featured` en `service` y
  los **6 ids del hero** marcados (`carrito-hot-dog`, `candy-bar`, `fuente-chocolate`, `photocall`,
  `mini-ferias`, `glitter-bar`).
- `GET /api/services` y `GET /api/admin/services` devuelven `featured`.
- `npm run build`: las 15 rutas prerenderizadas sin error. La portada sale con las **mismas 6** en
  el hero y en la parrilla, ahora con el nombre del servicio como etiqueta, y las 6 tarjetas del
  hero son `<a href="/servicios/{id}">` en el HTML.
- Un test de Vitest destapó un fallo real de diseño del propio test (el panel muta la fila, y el
  mock devolvía siempre el mismo objeto, contaminando los tests siguientes); el fixture ahora
  devuelve una copia por montaje.

## Pendiente

- **Prueba manual desde el panel** (marcar y desmarcar una sección y ver el cambio en la portada).
  No se pudo automatizar: hace falta sesión de admin.
- **Textos del hero**: al pasar a ser el nombre del servicio, alguno queda largo para una tarjeta
  cuadrada pequeña (p. ej. "Decoraciones y Photocall"). Si molesta, hay dos salidas: acortar el
  nombre del servicio o añadir un campo de etiqueta corta para el hero.
- **Producción**: ejecutar la migración en el despliegue
  (`php bin/console doctrine:migrations:migrate --no-interaction`) y **regenerar el snapshot** de
  prerender si se cambian los destacados antes de construir.
