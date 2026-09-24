# Plan: miniaturas + foto grande de verdad (1600 px)

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca
> Estado: **ejecutado** (queda la prueba manual de subida desde el panel)

## Contexto

Hoy se guarda **una sola imagen por foto** y esa misma URL se usa para todo: el thumb de 28 px del
panel, el avatar de 128 px de la ficha, las tarjetas de ~380 px y el lightbox a 70vh. Dos
consecuencias:

1. **El carrusel se ve blando.** El recortador (`ImageCropperModal.vue`) exporta el canvas al
   tamaño del contenedor en pantalla: **~560×420 px como máximo** (bastante menos en móvil). Esa
   es la "foto grande" que se abre a pantalla completa.
2. **Las listas cargan de más.** El fichero de ~80 KB se sirve igual para una fila de 36 px que
   para una tarjeta.

**Decisiones tomadas:**

1. **Las miniaturas se generan en el navegador**, reutilizando el canvas del recortador, y se suben
   junto a la foto grande en la misma petición (`image` + `thumbnail`). Cero dependencias nuevas,
   cero cambios en el Dockerfile, cero CPU en el servidor.
2. **La foto grande pasa a 1600 px de ancho** (4:3 → 1600×1200, JPEG q0.85 ≈ 300-400 KB), con tope
   en la resolución natural del recorte para no inventar píxeles.
3. **Miniatura de 640 px de ancho** (640×480, JPEG q0.82 ≈ 50-70 KB), un único tamaño para todos
   los contextos de lista.

**Consecuencias asumidas:**

- Las **fotos ya subidas** (≤560 px, sin miniatura) siguen sirviéndose tal cual: caen al respaldo
  `thumbnail ?? image`. No hay comando de relleno porque no hay librería de imagen en el backend.
  Para que ganen calidad y miniatura hay que **resubirlas desde el panel**.
- Al subir la foto grande a 1600 px, el lightbox cargaría N fotos de ~350 KB de golpe (hoy carga N
  de ~80 KB). Por eso el plan incluye **cargar solo la diapositiva actual y sus vecinas**.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Esquema: `thumbnail_url` en `service` y `service_example` + migración | ✅ Hecha |
| 2 | Recortador: exporta 1600 px + 640 px y emite ambos blobs | ✅ Hecha |
| 3 | `BaseFileUpload` propaga la miniatura y el panel la envía (`FormData`) | ✅ Hecha |
| 4 | Backend: commands, factory, controllers y handlers guardan/borran las dos URLs | ✅ Hecha |
| 5 | Serialización: `thumbnail` (resuelta) en catálogo, listado y detalle del panel | ✅ Hecha |
| 6 | Web pública: miniaturas en tarjetas/hero/rejilla; `image` en el lightbox | ✅ Hecha |
| 7 | Lightbox: cargar solo la diapositiva actual y sus vecinas | ✅ Hecha |
| 8 | Tests (PHPUnit + Vitest) | ✅ Hechos (143 + 56 + 38 en verde) |
| 9 | Snapshot de prerender + `PROJECT_SPEC.md` | ✅ Hechos |
| 10 | Prueba manual de subida desde el panel | ⬜ Pendiente (requiere sesión de admin) |

## Detalle por etapa

### 1. Esquema

- `backend/src/Entity/ServiceExample.php`: `?string $thumbnailUrl` (VARCHAR 500, nullable),
  parámetro opcional al final del constructor, `getThumbnailUrl()` (cruda) y
  `getDisplayThumbnail()` = `thumbnailUrl ?? imageUrl`. `toArray()` añade `thumbnail`.
- `backend/src/Entity/Service.php`: igual, con `getDisplayThumbnail()` que resuelve
  propia → primera de la galería, en espejo con `getDisplayImage()`.
- `backend/migrations/Version20260924200000.php`: dos `ALTER TABLE ... ADD thumbnail_url`.

**Por qué una columna y no derivar la URL por convención**: el fichero lo genera el navegador y lo
sube el `store()` existente, que asigna un nombre aleatorio e independiente. Guardar la URL evita
que un despiste deje un 404 en la web; si no hay miniatura, la entidad devuelve la foto grande y el
frontend nunca ve un hueco.

### 2. Recortador (`frontend/src/components/ui/ImageCropperModal.vue`)

- `confirm()` pasa a dibujar dos veces desde la imagen original (no una miniatura de la grande):
  `exportAt(1600)` y `exportAt(640)`.
- Factor de escala: `min(objetivo / cw, 1 / scale)` — el segundo término es el tope en la
  resolución natural del recorte (`cw / scale` px reales), así una foto pequeña no se amplía.
- Se rellena el canvas en blanco **antes** de dibujar: al alejar el zoom quedan franjas sin pintar
  que en JPEG salen negras.
- `toBlob` es asíncrono: se esperan los dos y se emite `confirm({ full, thumbnail })`.
- Calidades: grande `0.85`, miniatura `0.82`.

### 3. Subida (`BaseFileUpload.vue` + panel)

- `onCropConfirm` acepta el objeto, envuelve los dos blobs en `File` y emite además el evento
  `thumbnail` (el `modelValue`/`change` de siempre se mantiene con la foto grande, para no romper
  a los consumidores ni sus tests).
- `removeFile()` emite `thumbnail: null`.
- `pages/admin/AdminDashboardPage.vue` (foto de sección) y `pages/admin/AdminServiceDetailPage.vue`
  (alta y edición de galería): guardan el `File` de la miniatura y lo añaden al `FormData` como
  `thumbnail`.

### 4. Backend

- Commands (`CreateServiceCommand`, `UpdateServiceCommand`, `AddPhotoCommand`, `UpdatePhotoCommand`):
  `?UploadedFile $thumbnail = null`.
- `ServiceCommandFactory` y `AdminPhotoController`: leen `$request->files->get('thumbnail')`.
- Handlers: guardan con el `store()` que ya existe. En los que reemplazan o borran se llama a
  `delete()` para **las dos URLs** (el `delete()` del storage es idempotente y ya ignora URLs que no
  son del bucket, así que es seguro con fotos antiguas sin miniatura).
- `UpdateServiceHandler` conserva su orden store → save → delete de la anterior, ahora con las dos.

### 5. Serialización

- Público (`Service::toArray()`, `ServiceExample::toArray()`): `thumbnail` resuelta junto a `image`.
- Panel listado (`ListServicesHandler`): `image` + `thumbnail`.
- Panel detalle (`GetServiceHandler`): `thumbnailUrl` cruda (la que editaría el modal) y `thumbnail`
  resuelta, en espejo con la dualidad `imageUrl`/`image` que ya existe.

### 6. Web pública

| Sitio | Campo |
|---|---|
| `HeroSection.vue` (6 tarjetas) | `thumbnail` |
| `ServiceCard.vue` (portada, listado, relacionados) | `thumbnail` |
| `ServicioDetallePage.vue` avatar 128 px | `thumbnail` |
| `ServicioDetallePage.vue` rejilla de la galería | `thumbnail` |
| `ServicioDetallePage.vue` lightbox | `image` (grande) |
| `AdminDashboardPage.vue` fila 36 px | `thumbnail` |
| `AdminServiceDetailPage.vue` cabecera y tarjetas | `thumbnail` |

### 7. Lightbox

- `trackSlides` añade clones para el carrusel infinito: hoy monta N+2 `<img>` de golpe. Pasa a
  poner `:src` solo en la diapositiva actual y sus vecinas (la anterior y la siguiente ya están
  cargadas cuando se desliza, el resto se pide al llegar).
- La rejilla ya tiene la miniatura en caché del navegador: se pinta como fondo de la diapositiva
  para que el hueco se vea al instante mientras llega la grande.

### 8. Tests

- PHPUnit: entidades (cadena de respaldo de `getDisplayThumbnail`), handlers (se guardan/borran las
  dos URLs), factory (lee `thumbnail`), integración (`AdminServiceControllerTest` con multipart de
  dos ficheros).
- Vitest: `ServiceCard`/`HeroSection` usan `thumbnail`; el panel manda `thumbnail` en el `FormData`;
  y un test nuevo del recortador para los dos tamaños exportados.

### 9. Documentación

- Regenerar `frontend/prerender-data/snapshot.json` (el build lo reescribe; ahora incluye
  `thumbnail`).
- `docs/PROJECT_SPEC.md`: `thumbnail_url` en las entidades y la nota de variantes en el patrón de
  "Fotos en Cloudflare R2".

## Verificación

1. `make test-unit`, `make test-integration`, `npm --prefix frontend run test` en verde.
2. `npm run build`: prerender de las rutas con `thumbnail` en las tarjetas y `image` en el lightbox.
3. Manual (requiere sesión de admin): subir una foto y comprobar en el bucket los **dos** objetos;
   que la tarjeta pida la miniatura y el lightbox la grande; borrar y ver que desaparecen las dos.
4. `make migrate` en el despliegue.

## Verificación hecha

- `make test-unit` (**143**), `make test-integration` (**56**) y `npm --prefix frontend run test`
  (**38**) en verde.
- Migración `Version20260924200000` aplicada en la BD de desarrollo; `thumbnail_url` presente en
  `service` y `service_example` (comprobado con `\d` en psql).
- `GET /api/services`, `GET /api/services/{id}` y `GET /api/admin/services/{id}` devuelven
  `thumbnail` en los tres niveles (sección, `examples[]` y `photos[]`).
- `npm run build`: las 15 rutas prerenderizadas sin error. Con las fotos actuales (sin miniatura)
  el HTML sirve la foto grande, que es el respaldo previsto.
- El recortador tiene test propio de los dos tamaños: 1600×1200 + 640×480 con una foto de
  4000×3000, y sin ampliar por encima del recorte real en fotos pequeñas.

## Pendiente

- **Prueba manual de subida desde el panel** (subir una foto, comprobar que se crean los dos
  objetos en R2, que la tarjeta pide la miniatura y que al borrar desaparecen las dos). No se pudo
  automatizar: hace falta sesión de admin.
- **Resubir las fotos actuales** desde el panel si se quiere que ganen la calidad nueva y su
  miniatura: las que ya están en R2 miden ≤560 px (el recorte antiguo) y no tienen miniatura, así
  que siguen sirviéndose a tamaño completo mediante el respaldo.
- **Producción**: ejecutar la migración en el despliegue
  (`php bin/console doctrine:migrations:migrate --no-interaction`).

## Rollback

- Revertir el commit: la columna `thumbnail_url` queda nullable y sin uso (no rompe nada), y el
  frontend vuelve a `image` en todos los sitios. Las miniaturas ya subidas quedan huérfanas en R2.
