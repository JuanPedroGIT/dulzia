> Plan de implementación — Fecha: 2026-09-25 · Proyecto: dulziasalamanca

# Plan de implementación — Paleta fucsia + pistacho
**Proyecto:** Dulzia Salamanca Eventos
**Objetivo:** Reforzar la identidad de marca (fucsia + verde pistacho) en toda la web, no solo en el header y el hero.

---

## Estado de ejecución

| Etapa | Estado | Notas |
|---|---|---|
| 1. Tokens de color | ✅ Hecha | `frontend/src/styles/variables.scss` |
| 2. Fucsia fuera del header | ✅ Hecha | Botón principal en fucsia oscuro (AA) |
| 3. Pistacho vivo | ✅ Hecha | Sustituye al verde apagado en todo el sitio |
| 4. Fondos de sección alternos | ✅ Hecha | blanco → pistacho → pistacho claro → fucsia |
| 5. Tipografía | ✅ Hecha | Tokens ya existían; 3 titulares con palabra en fucsia |
| 6. Elementos decorativos | ✅ Hecha | Iconos SVG propios y bordes de tarjeta. **Separadores descartados** |
| 7. Verificación final | ✅ Hecha | Contraste AA medido, móvil/escritorio, y validación visual de Juan Pedro (26/09) |

### Decisiones tomadas al ejecutar

- **Botón principal en `#e0009f`, no en `#ff00c1`.** El punto 2 pedía fucsia puro y el punto 7 exige AA:
  con texto blanco, `#ff00c1` da 3,49:1 y `#e0009f` da 4,50:1. El hover sí va a fucsia puro.
- **Alcance: solo la web pública.** El panel de admin tiene su propia paleta rosa (`#c8748a`) y CSS
  escrito a mano; no se toca.
- **Separadores decorativos entre secciones: descartados** por decisión de Juan Pedro, para una
  segunda pasada.
- **Nuevo tono `$color-fucsia-text` (`#a80078`).** El fucsia de marca no llega a 4,5:1 como texto
  pequeño (3,49:1 sobre blanco, 2,96:1 sobre pistacho claro). Este es el mismo tono (317°) más
  oscuro, para etiquetas, categorías y estrellas.
- **La tira de abajo del footer vuelve al verde oscuro de marca (`#2b3f21`)** a petición de Juan
  Pedro. De paso se estilizan los enlaces legales de esa tira (Política de cookies, Aviso legal,
  Privacidad), que no llevaban clase y heredaban el color oscuro del body: sobre el verde no se
  leían. Ahora van al 85% de blanco (8,2:1) con hover en fucsia claro (8,8:1).
- **Secciones sobre pistacho vivo** a petición de Juan Pedro (26/09): las reseñas de la portada y
  "Nuestros valores" en Nosotros van con fondo `$color-pistacho` saturado (el plan decía pistacho
  claro). Las etiquetas fucsia de esas secciones pasan a `$color-fucsia-text` (~3,4:1 sobre el
  pistacho, por debajo de AA, aceptado a cambio del acento de marca).

### Pendiente conocido

- **La palabra "celebraciones" del titular del hero** va en fucsia sobre el degradado pistacho: 1,64:1.
  Es el peor contraste de la página. El punto 2 decía "ya está, mantener", así que no se ha tocado;
  para arreglarlo habría que aclarar el fondo del hero por detrás del titular o sacar la palabra a
  una pastilla clara.
- **Validación del equipo de marca** antes de publicar (último punto del apartado 7).

---

## 1. Definir tokens de color (variables CSS) ✅

Antes de tocar ninguna sección, centralizar los colores como variables para poder ajustarlos globalmente en el futuro.

El proyecto usa **SCSS**, no variables CSS, así que los tokens viven en
`frontend/src/styles/variables.scss` como variables `$`. Se añadió la paleta de marca
(`$color-fucsia*`, `$color-pistacho*`, `$color-text`, `$color-cream`) y los nombres antiguos
(`$color-mint*`, `$color-pink*`, `$color-green-dark`) quedaron como alias que apuntan a ella: por
eso el cambio de paleta se ha propagado a todo el sitio sin tocar cada componente.

- [x] Localizar el archivo de estilos globales → `frontend/src/styles/variables.scss`
- [x] Sustituir los colores hardcodeados existentes por estas variables (web pública; el admin queda fuera de alcance)
- [x] Actualizar `meta-theme-color` si se decide mantener `#FF00C1` como color de marca principal → ya era `#ff00c1`, se mantiene

---

## 2. Aplicar el fucsia fuera del header ✅

- [x] Palabra destacada en el titular del hero ("celebraciones") → ya estaba, mantenida
- [x] Botón "Pide tu presupuesto gratis" → fucsia oscuro, hover en fucsia puro
- [x] Icono/etiqueta "LO QUE HACEMOS" → mantenida y replicada en "GOOGLE · RESEÑAS" y en las categorías de tarjeta
- [x] Botón/enlace "Ver ejemplos →" de cada tarjeta de servicio → ahora en fucsia
- [x] Estrellas de valoración en reseñas → ya estaban en fucsia; se oscurecen a `$color-fucsia-text` por contraste
- [x] CTA final "Pide presupuesto gratis" → botón claro sobre bloque fucsia (sobre fucsia no puede ser fucsia)

---

## 3. Sustituir el verde apagado por el pistacho vivo ✅

- [x] Fondo degradado del hero → ahora `$color-pistacho`
- [x] Fondo del bloque de cifras → mismo ajuste
- [x] Botón "Pide tu presupuesto gratis" del hero → no se mantiene verde: pasa a fucsia (punto 2)
- [x] Título "Servicios que enamoran" → en `$color-pistacho-dark`, 3,81:1 sobre blanco (AA para titular grande)

---

## 4. Fondos de sección alternos ✅

- [x] Sección de servicios → fondo `$color-white`
- [x] Sección de reseñas → fondo `$color-pistacho` (vivo, a petición de Juan Pedro el 26/09; el plan decía pistacho claro)
- [x] Bloque de cifras → se mantiene en pistacho
- [x] CTA final → fondo fucsia oscuro con texto blanco y la palabra "evento" en pistacho claro

---

## 5. Tipografía ✅

- [x] Confirmar las dos familias tipográficas actuales y documentarlas como tokens → ya existían: `$font-heading` (Playfair Display) y `$font-body` (Inter)
- [x] Aplicar fucsia a palabras clave dentro de titulares importantes → hero ("celebraciones"), servicios ("enamoran") y CTA ("evento")
- [x] Revisar contraste de `$color-text` (#2b2b2b) sobre pistacho y pistacho claro → 6,67:1 y 12:1, ambos por encima de AA

---

## 6. Elementos decorativos ✅

- [x] Iconos de la sección de cifras → sustituidos los emojis por iconos SVG de línea propios, en disco blanco con el trazo en fucsia
- [x] Bordes/sombras de tarjetas de servicio → borde en `$color-pistacho-light`, que pasa a `$color-fucsia-light` en hover
- [ ] Separadores entre secciones → **descartado en esta pasada**

---

## 7. Verificación final 🔄

- [x] Revisar contraste de texto/fondo en todas las combinaciones nuevas (WCAG AA mínimo) → 33 comprobaciones medidas en el navegador con los colores ya computados: 0 fallos
- [x] Comprobar consistencia en móvil y escritorio → capturas a 1440 px y 390 px
- [x] Validar que el fucsia y el pistacho aparecen de forma equilibrada en cada scroll → el fucsia entra en nav, hero, etiquetas, tarjetas, reseñas y CTA
- [x] Pasar la página a alguien del equipo de marca para validación visual antes de publicar → revisada por Juan Pedro (26/09): "todo bien"

---

## Notas
- Los hexadecimales exactos pueden ajustarse ±5% en saturación/luminosidad según pruebas visuales reales en pantalla, pero mantener la relación fucsia vivo / pistacho vivo / neutros suaves.
- Priorizar los puntos 2 y 3 (repartir fucsia, saturar pistacho) porque son los cambios de mayor impacto visual con menor esfuerzo de implementación.
