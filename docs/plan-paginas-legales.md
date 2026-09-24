# Plan: Páginas legales (Aviso legal + Política de privacidad)

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca

## Contexto

La web local tiene la página `/politica-cookies`; faltan las otras dos páginas legales de la web
original. Se crean **Aviso legal** (`/aviso-legal/`) y **Política de privacidad**
(`/politica-de-privacidad/`) con los textos extraídos de la web original (documentados en
`docs/contenido-web-original.md`), usando el mismo estilo visual que la página de cookies
(`hero-small` + bloque `.legal`).

- Rutas iguales que la web original (bueno para SEO).
- Textos literales del original (incluidas erratas como «El usted responderá…»,
  «DULZIA SALAMANCA EVENTOSB» u «ORIGINALIA SALAMANCA CB»); se marcan para revisión del usuario.
- Los enlaces se añaden en el footer, junto a «Política de cookies», y las 2 URLs al sitemap.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Crear `frontend/src/pages/AvisoLegalPage.vue` con el texto del aviso legal original | ✅ Hecha |
| 2 | Crear `frontend/src/pages/PoliticaPrivacidadPage.vue` con el texto de la política de privacidad original | ✅ Hecha |
| 3 | Añadir rutas `/aviso-legal` y `/politica-de-privacidad` al router | ✅ Hecha |
| 4 | Añadir los enlaces en el footer junto a «Política de cookies» | ✅ Hecha |
| 5 | Añadir las 2 URLs al `sitemap.xml` | ✅ Hecha |
| 6 | Verificar compilación (dev server) y que las páginas responden | ✅ Hecha (módulos compilan, rutas 200, textos en el bundle) |
