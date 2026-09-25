<template>
  <section class="reviews">
    <div class="container">
      <div class="reviews__header">
        <span class="reviews__pre">Google · Reseñas</span>
        <h2 class="reviews__title">Lo que dicen de nosotros</h2>
        <p class="reviews__sub">
          Bodas, comuniones y cumpleaños. Estas son algunas de las opiniones
          que nos han dejado en nuestro perfil de Google.
        </p>

        <p v-if="rating" class="reviews__score">
          <span class="reviews__score-num">{{ rating.score }}</span>
          <span class="reviews__stars" aria-hidden="true">★★★★★</span>
          <span class="reviews__score-count">({{ rating.count }} reseñas)</span>
        </p>
      </div>

      <div class="reviews__grid">
        <article v-for="review in reviews" :key="review.name" class="reviews__card">
          <div class="reviews__author">
            <span class="reviews__avatar" aria-hidden="true">{{ initial(review.name) }}</span>
            <div>
              <p class="reviews__name">{{ review.name }}</p>
              <p class="reviews__date">{{ ago(review.date) }}</p>
            </div>
          </div>

          <p class="reviews__card-stars" role="img" :aria-label="`${review.rating} de 5 estrellas`">
            <span aria-hidden="true">{{ '★'.repeat(review.rating) }}</span>
          </p>

          <p class="reviews__text">{{ review.text }}</p>
        </article>
      </div>

      <div class="reviews__cta">
        <BaseButton :href="GOOGLE_REVIEWS_URL" target="_blank" rel="noopener" variant="outline" size="lg">
          Ver todas las reseñas en Google
        </BaseButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import BaseButton from '@/components/ui/BaseButton.vue'

/**
 * Reseñas reales del perfil de Google, copiadas el 25/09/2026.
 *
 * Los textos van TAL CUAL los escribieron: son testimonios de clientes, no
 * textos de marketing, así que no se corrigen sus tildes, sus espacios ni su
 * puntuación. La de Teresa viene cortada por Google (acaba en "… Más"): se deja
 * el final abierto para no hacer pasar por suyo un texto recortado.
 *
 * `date` es la fecha de la reseña, aproximada al mes que da Google ("hace 3
 * meses"): el día exacto no lo publica, así que se puso uno dentro de ese mes.
 */
const reviews = [
  {
    name: 'Taly Hdez Luengo',
    date: '2026-07-25',
    rating: 5,
    text: 'Contratamos la recena de nuestra boda con ellos y a parte de ayudarnos mucho a la hora de decidir por qué decantarnos , todo estaba buenísimo y súper bien preparado. Encantados de haberlos descubierto y los invitados nos felicitaron por lo rico que estaba todo! Un placer, os recomendaremos muchísimo!',
  },
  {
    name: 'Cristina Herraez',
    date: '2026-06-05',
    rating: 5,
    text: 'Han dejado todo precioso! Cuidan cada detalle, y lo mas importante apto para celiacos, para que asi mi hija pudiera disfrutar de su Candy en el dia de su comunion, sin ninguna preocupacion. El glitter fenomenal, los niñ@s e incluso mayores, super entretenidos y encantados!',
  },
  {
    name: 'teresa',
    date: '2026-06-18',
    rating: 5,
    text: 'Todo genial , desde el minuto uno que me puse en contacto con ellos . Te ayudan , te aconsejan, facilidad para hablar siempre con ellos , muy responsables , serios en su trabajo y comprometidos . …',
  },
  /*
  {
    name: 'Mayte Martin Sentenac',
    date: '2026-06-24',
    rating: 5,
    text: 'Profesionalidad, empatia y cercanía. Gracias por vuestro excelente trabajo en la comunión de nuestro hijo. Candy bar, cartel de bienvenida y regalo para los invitados.',
  },
  {
    name: 'Raquel Cuadrado Escribano',
    date: '2026-05-20',
    rating: 5,
    text: 'El 16 de mayo fue la comunión de mi hija y contraté a dulzia Salamanca Eventos montaron el candibar para mi hija y lo dejaron precioso. Fueron muy amables y estuvieron pendientes en todo momento. Muchas gracias por todo. Lo recomiendo.',
  },*/
]

/**
 * Nota media del perfil de Google (25/09/2026). El `count` es el total de reseñas
 * del perfil, no las que se enseñan aquí, que son unas pocas.
 * Si algún día no hubiera dato, basta con dejarlo en `null`: la fila no se pinta
 * (es un dato público y comprobable, no se inventa).
 */
const rating = { score: '4,9', count: 146 }

const GOOGLE_REVIEWS_URL = 'https://maps.app.goo.gl/MYuXG2xSAJKYDhzM8'

/** Inicial para el círculo, sin foto: la mayoría de reseñas de Google no la traen. */
function initial(name) {
  return name.trim().charAt(0).toUpperCase()
}

/**
 * "hace 2 meses", "hace 1 año"… a partir de la fecha de la reseña. Se calcula al
 * pintar, así que envejece sola: la de julio pasará a "hace 3 meses" en octubre
 * sin tocar los datos.
 *
 * Los meses se cuentan como en el calendario (mismo día del mes), no por días
 * sueltos; por debajo del mes, en días, para que una reseña recién llegada no
 * diga "hace 0 meses".
 */
function ago(isoDate, today = new Date()) {
  const date = new Date(`${isoDate}T00:00:00`)
  let months = (today.getFullYear() - date.getFullYear()) * 12 + today.getMonth() - date.getMonth()

  // El mes en curso todavía no cuenta si no se ha llegado al mismo día.
  if (today.getDate() < date.getDate()) months -= 1

  if (months < 1) {
    // Días de calendario (medianoche contra medianoche): de un 25 a un 1 van 7,
    // no 7 y pico. Y nunca "hace 0 días": una reseña de hoy es "hace 1 día".
    const midnight = new Date(today.getFullYear(), today.getMonth(), today.getDate())
    const days = Math.max(1, Math.round((midnight - date) / 86400000))

    return `hace ${days} ${days === 1 ? 'día' : 'días'}`
  }

  if (months < 12) {
    return `hace ${months} ${months === 1 ? 'mes' : 'meses'}`
  }

  const years = Math.floor(months / 12)

  return `hace ${years} ${years === 1 ? 'año' : 'años'}`
}
</script>

<style lang="scss" scoped>
.reviews {
  @include gradient-mint;
  @include section-padding;
  color: $color-green-dark;

  &__header {
    text-align: center;
    margin-bottom: $space-12;
  }

  &__pre {
    display: inline-block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-green-deep;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-green-dark;
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: rgba($color-green-dark, 0.8);
    max-width: 560px;
    margin-inline: auto;
    line-height: 1.7;
  }

  &__score {
    display: flex;
    align-items: baseline;
    justify-content: center;
    flex-wrap: wrap;
    gap: $space-3;
    margin-top: $space-6;
  }

  &__score-num {
    font-family: $font-heading;
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-green-deep;
    line-height: 1;
  }

  &__stars {
    color: $color-pink;
    font-size: $text-xl;
    letter-spacing: 2px;
  }

  &__score-count {
    font-size: $text-sm;
    color: rgba($color-green-dark, 0.75);
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-6;

    @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
    @include respond-to(lg) { grid-template-columns: repeat(3, 1fr); }
  }

  &__card {
    @include card;
    padding: $space-8;
    display: flex;
    flex-direction: column;
    gap: $space-4;
  }

  &__author {
    display: flex;
    align-items: center;
    gap: $space-4;
  }

  &__avatar {
    @include flex-center;
    @include gradient-pink;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: $radius-full;
    color: $color-white;
    font-size: $text-lg;
    font-weight: 700;
  }

  &__name {
    font-size: $text-base;
    font-weight: 700;
    color: $color-green-dark;
  }

  &__date {
    font-size: $text-xs;
    color: $color-text-muted;
  }

  &__card-stars {
    color: $color-pink;
    font-size: $text-base;
    letter-spacing: 2px;
  }

  &__text {
    font-size: $text-base;
    line-height: 1.7;
    color: $color-text-muted;
  }

  &__cta {
    text-align: center;
    margin-top: $space-12;
  }
}
</style>
