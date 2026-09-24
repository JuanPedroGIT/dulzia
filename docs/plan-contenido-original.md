# Plan: Copiar textos y fotos de la web original al proyecto local

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca

## Contexto

Se va a rediseñar la web y el contenido se migra a mano desde la web actual
(https://www.dulziasalamancaeventos.com, WordPress). Objetivo acordado con el usuario:

- **Textos de las secciones existentes** → base de datos (`service`: nombre y descripción reales de los 11 servicios).
- **Fotos** → subirlas al bucket **Cloudflare R2** (`services/*` para galerías de servicios, `site/*` para las de la home) y guardar las referencias en la BD (`service_example`).
- Todo el contenido original queda además documentado en `docs/contenido-web-original.md` (incluye las secciones que no viven en BD: hero, Quiénes somos, Bodas, Eventos Especiales, Comuniones, Valores, Testimonios, Contacto).

Datos verificados:

- Stack Docker levantada: `dulzia-backend` (Symfony, con aws-sdk-php instalado), `dulzia-frontend`, `shared-postgres-db`.
- El `.env` raíz ya tiene las 5 variables `R2_*` rellenas (mismo bucket que producción, según plan-r2).
- BD actual: 11 servicios con descripciones de relleno + 44 filas `service_example` (42 picsum + 2 fotos R2 reales del carrito que se conservan).
- `CloudflareR2Storage` es el almacenamiento activo (`config/services.yaml`): keys `services/<hex>.<ext>` y URL pública `${R2_PUBLIC_URL}/services/<key>`.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Recolección del contenido de la web original (textos exactos + listas de fotos por página) | ✅ Hecha |
| 2 | Descarga de las fotos a staging local + generación de manifiesto (key R2 → servicio → pie de foto) | ✅ Hecha (130 filas BD, 129 fotos únicas + 5 de la home, 6,4 MB) |
| 3 | Subida de las fotos al bucket R2 (`services/*` y `site/*`) | ✅ Hecha (134 objetos, 0 fallos, públicas vía r2.dev) |
| 4 | Migración Doctrine: textos reales en `service` + fotos reales en `service_example` (borra picsum, conserva las 2 fotos R2 existentes) | ✅ Hecha (`Version20260924120000.php`) |
| 5 | Ejecutar la migración en la BD local | ✅ Hecha (142 queries, verificado) |
| 6 | Verificación: API y web muestran textos y fotos reales | ✅ Hecha (nombres y descripciones reales, 132 filas todas R2, frontend 200) |
| 7 | Documento de referencia `docs/contenido-web-original.md` con todo el contenido original | ✅ Hecha (home, nosotros, contacto, servicios con inventario de fotos, aviso legal y política de privacidad) |

## Detalle de la etapa 4 (migración)

- Fichero nuevo `backend/migrations/Version20260924XXXXXX.php` (estilo del resto: `App\Migrations`, `addSql`).
- `UPDATE service SET name=…, description=… WHERE id=…` para los 11 servicios (textos literales de la web original, tal cual).
- `DELETE FROM service_example WHERE image_url LIKE 'https://picsum.photos/%' AND service_id IN (…11 ids…)`.
- `INSERT INTO service_example (id, service_id, title, description, image_url, sort_order)` con las fotos reales: `image_url = <R2_PUBLIC_URL>/services/<key>`, `title` = pie de foto original (o "«Servicio» — foto N"), `description` = pie de foto o texto genérico (columna NOT NULL), `sort_order` continuando tras las filas que sobrevivan.
- `down()`: restaura los estados previos (textos antiguos y filas picsum no se restauran — rollback documentado).

## Notas

- Las fotos de la home (hero, 3 de galería, avatar de testimonios) se suben al bucket bajo `site/` para el rediseño; no tienen tabla propia en BD y se documentan en el doc de contenido.
- Los textos se copian **literales**, incluidos los errores tipográficos del original (p. ej. "recenas", "estais") — es una copia, no una corrección.
- Staging de fotos fuera del repo (temp de Windows); tras la subida a R2 y la migración se elimina.
