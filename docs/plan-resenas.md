# Plan: sección de reseñas en la portada

> Plan de implementación — Fecha: 2026-09-25 · Proyecto: dulziasalamanca
> Estado: **en curso** (reseñas reales puestas; falta la nota media)

## Contexto

El negocio tiene reseñas en Google y no se enseñaban en la web. La maqueta de referencia
(la pasó el cliente) es una página oscura con la nota media arriba y una lista de reseñas;
es prueba social, así que su sitio es la portada, entre "Servicios que enamoran" y el
banner de presupuesto.

**Decisiones tomadas:**

1. **Sección en la portada** (`ReviewsSection`), no página propia: la prueba social tiene
   que verse sin navegar. El componente se puede soltar en otra página sin tocar nada.
2. **Los estilos son los de la web, no los de la maqueta**: la maqueta venía en ciruela y
   dorado, que no son de la marca. Se rehace con `gradient-mint` (como la barra de cifras y
   el pie), tarjetas blancas con el mixin `card` y el rosa de acento en estrellas y avatares.
3. **Datos en el propio componente**, en una sola constante (como los valores de
   `/nosotros`). Cuando haya de dónde leerlos, se cambia esa constante y el resto no se toca.
4. **Los textos van tal cual los escribieron** los clientes: no se corrigen tildes,
   espacios ni puntuación. Son testimonios, no textos de marketing.
5. **La antigüedad se calcula**, no se escribe: cada reseña guarda su fecha y la etiqueta
   ("hace 3 meses") se deriva al pintar, así que envejece sola.
6. **Sin datos estructurados** (`Review`/`aggregateRating` en JSON-LD). El plan de SEO ya
   descartó `aggregateRating` por no haber valoraciones fiables, y declarar a Google una
   nota sin comprobar sería peor que no declararla.
7. Los avatares son un círculo con la inicial: la mayoría de reseñas de Google no traen foto.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Sección maquetada con los estilos de la web (portada) | ✅ Hecha |
| 2 | Tests y build con el HTML prerenderizado | ✅ Hechos (97 en verde) |
| 3 | Sustituir los datos de ejemplo por las reseñas reales | ✅ Hecha (5 reseñas de Google) |
| 4 | **Poner la nota media real** (la fila está oculta hasta entonces) | ⬜ Pendiente |
| 5 | (Opcional) Gestionarlas desde el panel o leerlas con la API de Places | ⬜ Pendiente |

## Lo que falta (etapa 4)

La fila de la nota media —el "4,9 ★★★★★ (127 reseñas)" de la maqueta— **no se pinta**:
`rating` está en `null` y el dato real no lo tenemos. Es un dato público y comprobable
(está en el perfil de Google del negocio), así que no se inventa. En cuanto se sepa:

```js
const rating = { score: '4,9', count: 127 }   // y la fila aparece sola
```

Ojo con el `count`: es el número total de reseñas del perfil, no las que se enseñan aquí
(que son 5).

## Notas sobre los datos

- **Las fechas son aproximadas al mes.** Google solo publica "hace 3 meses", así que cada
  reseña guarda un día dentro de ese mes. Si algún cliente dice la fecha exacta, se cambia
  en su reseña y la etiqueta se recalcula sola.
- **La reseña de Teresa viene cortada** por Google (su "… Más"): termina en "…" para que no
  parezca que el texto acaba ahí. Con el final completo, se completa la constante.
- **Las respuestas del propietario no están.** Tres de las cinco reseñas las tienen, pero
  venían recortadas con "…" en el copiado. Si se quieren enseñar (dan buena imagen: se
  responde a todo el mundo), hay que pegarlas enteras.
- **Las estrellas**: las cinco van a 5 sobre 5, que es lo que refleja el copiado. Si alguna
  es de 4, se cambia su `rating` y la tarjeta pinta 4.

## Detalle por etapa

### 1. Sección

- `components/features/ReviewsSection.vue`: cabecera (kicker, titular, entradilla y —cuando
  haya dato— la nota media con estrellas), rejilla de tarjetas (autor con inicial, antigüedad,
  estrellas y texto) y botón al perfil de Google (`target="_blank"` + `rel="noopener"`).
- `HomePage.vue`: se monta entre `ServicesOverview` y `CtaBanner`.
- Las estrellas llevan `role="img"` con `aria-label` ("5 de 5 estrellas"): quien no las ve
  recibe la nota en texto.

### 3. Fechas y antigüedad

`ago(fecha)` cuenta meses de calendario (mismo día del mes, no días sueltos) y, por debajo
del mes, días —para que una reseña recién llegada no diga "hace 0 meses"—, y a partir del
año pasa a "hace 1 año", "hace 3 años". Como se calcula al pintar, no hay que revisar la
sección cada vez que pasa el tiempo; solo el HTML prerenderizado se queda con la etiqueta
del día del build hasta la siguiente compilación.

## Verificación

1. `npm --prefix frontend run test` en verde (**97**, 7 de la sección).
2. `npm run build`: la sección sale en el HTML prerenderizado de la portada, con las cinco
   reseñas y sus antigüedades calculadas.
3. Manual: comprobarla en `/` y en el móvil, y que el botón abre el perfil de Google en una
   pestaña nueva.
