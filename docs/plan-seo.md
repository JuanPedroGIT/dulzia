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
| 1 | Imagen OG real (arreglar `/og-image.jpg` rota) | ✅ Hecha (og-image.jpg 1200×630 generada + og:image/twitter:image ahora absolutas) |
| 2 | Prerender de las 11 páginas de servicio | ✅ Hecha (snapshot inyectado, puppeteer actualizado a 24, 15 rutas con contenido verificado) |
| 3 | Redirects 301 de URLs antiguas (WordPress) → nuevas en `nginx.conf` | ✅ Hecha (20 mapeos añadidos) |
| 4 | JSON-LD ampliado: dirección/geo en LocalBusiness + OfferCatalog/Service + BreadcrumbList | ✅ Hecha (LocalBusiness con address+geo+areaServed, ItemList/Service en home y /servicios, BreadcrumbList en detalle; aggregateRating descartado: no hay valoraciones numéricas reales) |
| 5 | H1 de la home con keywords locales | ✅ Hecha («Eventos y celebraciones inolvidables en Salamanca») |
| 6 | Sitemap: añadir `/politica-cookies` y revisar entradas | ✅ Hecha |
| 7 | Details: description en páginas legales, `width`/`height` en imágenes (CLS) | ✅ Hecha (descriptions añadidas; CLS ya cubierto con `aspect-ratio` en la galería) |
| 8 | Tareas fuera del código (Google Business Profile, Search Console, reseñas) | ⬜ Pendiente (manuales — ver detalle) |

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

**✅ Ejecutado (2026-09-24):** snapshot en `frontend/prerender-data/snapshot.json` (generado desde
la API local: lista + detalle de los 11 servicios) que se inyecta en cada página renderizada vía
`inject`/`injectProperty` del PuppeteerRenderer (`window.__DULZIA_PRERENDER__`); `useServices` lo
usa como fuente en ese contexto. Además se actualizó `puppeteer` a `^24` (override en package.json)
porque el Chromium de la versión transitiva (1.20/Chrome 78) no entendía el JS moderno del bundle.
**Regenerar el snapshot cuando cambie el catálogo** (comando documentado abajo).
Incluye también las categorías: sin ellas la portada estática saldría sin pestañas y
con los identificadores en vez de los nombres.

```bash
# Regenerar el snapshot (con el backend local corriendo):
python -c "
import json, urllib.request
BASE='http://localhost:8000/api/services'
services=json.load(urllib.request.urlopen(BASE))
snap={
  'list':services,
  'details':{s['id']:json.load(urllib.request.urlopen(BASE+'/'+s['id'])) for s in services},
  'categories':json.load(urllib.request.urlopen('http://localhost:8000/api/categories')),
}
open('frontend/prerender-data/snapshot.json','w',encoding='utf-8').write(json.dumps(snap,ensure_ascii=False))
"
```

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
- **✅ Ejecutado (2026-09-24):** 20 redirects añadidos activos en `nginx.conf` (solo aplican en
  el despliegue de producción, no en dev). Decisión tomada: `/tienda/` → `/servicios/regalos-personalizados`.

### 4. JSON-LD ampliado

- `LocalBusiness` (`useSeo.js`): añadir 
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
