# Plan: Mejoras de SEO

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca
> Estado: **preparado, pendiente de abordar** (decisión del usuario: «lo abordaremos más tarde»)

## Contexto

Diagnóstico hecho el 2026-09-24 sobre el estado actual del SEO del proyecto.

**Ya implementado (no tocar):** `useSeo` (title, description, canonical, OG, Twitter Card, locale
`es_ES`) en todas las páginas · JSON-LD `LocalBusiness` en la home · `sitemap.xml` (home, servicios
y detalle, nosotros, contacto, aviso-legal, política-de-privacidad) · `robots.txt` con sitemap ·
`lang="es"` · prerender de las 4 rutas principales · nginx con gzip y caché 1 año en estáticos ·
alt reales en las fotos de las galerías.

**Problemas detectados:** imagen OG apunta a `/og-image.jpg` que no existe · las 11 páginas de
servicio no están prerenderizadas · sin 301 preparados para la migración desde la web WordPress ·
JSON-LD local sin dirección ni catálogo de servicios · H1 de la home sin keywords locales ·
`politica-cookies` falta en el sitemap · páginas legales sin description propia.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Imagen OG real (arreglar `/og-image.jpg` rota) | ⬜ Pendiente |
| 2 | Prerender de las 11 páginas de servicio | ⬜ Pendiente |
| 3 | Redirects 301 de URLs antiguas (WordPress) → nuevas en `nginx.conf` | ⬜ Pendiente |
| 4 | JSON-LD ampliado: dirección/geo en LocalBusiness + OfferCatalog/Service + BreadcrumbList | ⬜ Pendiente |
| 5 | H1 de la home con keywords locales | ⬜ Pendiente |
| 6 | Sitemap: añadir `/politica-cookies` y revisar entradas | ⬜ Pendiente |
| 7 | Details: description en páginas legales, `width`/`height` en imágenes (CLS) | ⬜ Pendiente |
| 8 | Tareas fuera del código (Google Business Profile, Search Console, reseñas) | ⬜ Pendiente |

## Detalle por etapa

### 1. Imagen OG real

- `frontend/src/composables/useSeo.js`: `DEFAULT_IMAGE = '/og-image.jpg'` y
  `localBusinessJsonLd.image` apuntan a un fichero que **no existe** en `frontend/public/`.
- Opciones: (a) generar una imagen 1200×630 (la foto `site/hero.jpg` de R2 es 900×813) y subirla
  a `frontend/public/og-image.jpg`; (b) apuntar `DEFAULT_IMAGE` a la URL R2
  `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/hero.jpg`.
- Las OG image deben ser URL absoluta y estable; decidir (a) o (b) al ejecutar.
- Verificar con la herramienta de compartir de Facebook y el validador de Twitter.

### 2. Prerender de las 11 páginas de servicio

- `frontend/vite.config.js`: añadir a `routes` del `vite-plugin-prerender` las 11 URLs:
  `/servicios/carrito-hot-dog`, `/servicios/candy-bar`, `/servicios/carteles-bienvenida`,
  `/servicios/picnic-tipis`, `/servicios/photocall`, `/servicios/fuente-chocolate`,
  `/servicios/mini-ferias`, `/servicios/palomitero`, `/servicios/algodon-azucar`,
  `/servicios/glitter-bar`, `/servicios/regalos-personalizados`.
- ⚠️ Riesgo conocido: las páginas de detalle cargan datos de `/api/services/{id}` en runtime;
  el prerender sirve `dist/` estático y ese API no existirá ahí → páginas prerenderizadas vacías.
  Soluciones a valorar al ejecutar: proxy del PuppeteerRenderer hacia el backend, snapshot
  estático del JSON en `dist/api/…`, o inyectar los datos en el HTML.
- Las URLs antiguas de servicio (ver etapa 3) deben redirigir a estas.

### 3. Redirects 301 (migración desde WordPress)

- `frontend/nginx.conf` (producción): añadir bloque `location = /url-antigua { return 301 /nueva; }`.
- Mapeos de la web original → nueva (las barras finales de WordPress también hay que cubrir):
  - `/quienes-somos/` → `/nosotros`
  - `/servicios/carrito-de-perritos-calientes/` → `/servicios/carrito-hot-dog`
  - `/servicios/photocall-y-decoraciones/` → `/servicios/photocall`
  - `/servicios/picnic-y-tipis/` → `/servicios/picnic-tipis`
  - `/servicios/fuente-de-chocolate/` → `/servicios/fuente-chocolate`
  - `/servicios/candy-bar/` → `/servicios/candy-bar` (igual, quitar barra final)
  - (ídem sin cambios de slug: carteles-de-bienvenida, mini-ferias, palomitero,
    algodon-de-azucar, glitter-bar, regalos-personalizados)
  - `/tienda/` → decidir destino (¿`/servicios/regalos-personalizados`?)
  - `/contacto/`, `/aviso-legal/`, `/politica-de-privacidad/` → mismas rutas sin barra
- Dejarlos listos (activos o comentados) antes de apuntar el dominio nuevo.

### 4. JSON-LD ampliado

- `LocalBusiness` (`useSeo.js`): añadir `address` (PostalAddress: C/ Martín Alonso Pedraz, 14,
  37007 Salamanca, ES), `geo` (coordenadas de la ficha de Google Business — obtenerlas) y
  `areaServed` (Salamanca y alrededores).
- `Service`/`OfferCatalog` en `/servicios` y `/`: catálogo con las 11 URLs de servicio
  (nombres y descripciones reales ya en BD vía API).
- `BreadcrumbList` en `ServicioDetallePage.vue` (Inicio → Servicios → Nombre del servicio).
- Opcional: `aggregateRating` en LocalBusiness con las 3 reseñas reales (Elsa Díaz, Guiomar Pérez,
  Elena Pindado) — revisar las directrices de Google antes de activarlo.

### 5. H1 de la home con keywords

- `HeroSection.vue`: «Hacemos tus celebraciones inolvidables» no menciona Salamanca ni servicios.
- Propuesta manteniendo el diseño: «Servicios para eventos y celebraciones en Salamanca» con
  alguna palabra destacada (p. ej. «celebraciones *inolvidables*»). No repetir exactamente el
  `<title>` («Carrito Hot Dog, Candy Bar, Photocall y más en Salamanca»).

### 6. Sitemap

- Añadir `/politica-cookies` (falta; las legales nuevas ya están).
- Revisar `changefreq`/`priority` y añadir `lastmod` si se quiere mantener al día.

### 7. Detalles menores

- Description propia en `AvisoLegalPage.vue` y `PoliticaPrivacidadPage.vue` (hoy usan la genérica).
- `width`/`height` en las imágenes de la galería (`ServicioDetallePage.vue`) para evitar CLS.
- (No aplica: hreflang — sitio de un solo idioma.)

### 8. Fuera del código

- **Google Business Profile**: crear/reclamar la ficha (clave para búsquedas locales «eventos
  Salamanca»); enlazar web, teléfono e Instagram.
- **Search Console**: verificar el dominio, subir el sitemap y vigilar «no indexadas» tras la
  migración (README ya lo anota como pendiente).
- **Bing Webmaster Tools** (opcional).
- **Reseñas de Google**: pedirlas a clientes; son factor fuerte de ranking local.
- Monitorizar tráfico con Umami (ya instalado) después de la migración.

## Orden recomendado al abordar

1 (og-image, trivial) → 3 (301, imprescindible el día de la migración) → 2 (prerender) →
4 y 5 (JSON-LD + H1) → 6 y 7 (detalles) → 8 (tareas manuales en paralelo).
