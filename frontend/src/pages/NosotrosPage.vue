<template>
  <main>
    <section class="hero-small">
      <div class="container">
        <span class="hero-small__pre">Quiénes somos</span>
        <h1 class="hero-small__title">!Bienvenidos a nuestro mundo de Eventos!</h1>
        <p class="hero-small__sub">
          Somos Yoana y Sergio, dos apasionados de la decoración y la
          organización de momentos únicos.
        </p>
      </div>
    </section>

    <!-- Story -->
    <section class="section">
      <div class="container">
        <div class="story">
          <div class="story__visual">
            <img
              class="story__photo"
              src="https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/hero.jpg"
              alt="Dulzia Salamanca Eventos"
              loading="lazy"
            />
          </div>
          <div class="story__text animate-fadeInUp">
            <span class="story__pre">Nuestra historia</span>
            <h2 class="story__title">Somos Yoana y Sergio</h2>
            <p>
              Nos dedicamos a transformar cada celebración en algo especial, cuidando cada detalle
              para que tú solo tengas que disfrutar.
            </p>
            <p>
              Decoramos todo tipo de eventos: Bodas, Comuniones, cumpleaños, fiestas privadas,
              corporativas... Además, contamos con nuestro divertido carrito de Perritos Calientes,
              crepes..., para darle un toque original y delicioso a tu celebración.
            </p>
            <p>
              Nos encanta nuestro trabajo y ponemos el corazón en cada proyecto, porque creemos
              que cada evento es una oportunidad para crear recuerdos inolvidables.
            </p>
            <p>
              Si buscas cercanía, creatividad y pasión, ¡Has llegado al lugar indicado!
            </p>
            <p>
              Cuéntanos tu próximo evento, y hagamos de ese día algo mágico.
            </p>
            <p class="story__closing">¿TE ATREVES?</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="section section--alt">
      <div class="container">
        <div class="values__header">
          <span class="values__pre">Por qué elegirnos</span>
          <h2 class="values__title">Nuestros valores</h2>
        </div>
        <div class="values__grid">
          <div v-for="(v, i) in values" :key="v.title" class="value-card" :style="{ '--delay': `${i * 100}ms` }">
            <span class="value-card__emoji">{{ v.emoji }}</span>
            <h3 class="value-card__title">{{ v.title }}</h3>
            <p class="value-card__text">{{ v.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact info -->
    <section class="section">
      <div class="container">
        <div class="info-band">
          <div class="info-band__item">
            <span class="info-band__emoji">🕐</span>
            <div>
              <strong>Horario</strong>
              <p>Lunes a viernes<br>7:00 – 18:00</p>
            </div>
          </div>
          <div class="info-band__item">
            <span class="info-band__emoji">📞</span>
            <div>
              <strong>Teléfono</strong>
              <p><a :href="telHref">{{ phone }}</a></p>
            </div>
          </div>
          <div class="info-band__item">
            <span class="info-band__emoji">✉️</span>
            <div>
              <strong>Email</strong>
              <p><a :href="`mailto:${email}`">{{ email }}</a></p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <CtaBanner />
  </main>
</template>

<script setup>
import CtaBanner from '@/components/features/CtaBanner.vue'

import { useContact } from '@/composables/useContact.js'
import { useSeo } from '@/composables/useSeo.js'

// Teléfono y email salen de lo configurado en el panel (con los de siempre
// como respaldo), igual que en el pie y en la página de contacto.
const { email, phone, telHref } = useContact()

useSeo({
  title: 'Quiénes somos',
  description: 'Conoce a Dulzia Salamanca Eventos, empresa especializada en hacer tus celebraciones únicas e inolvidables en Salamanca y alrededores.',
  path: '/nosotros',
})

const values = [
  {
    emoji: '❤️',
    title: 'Pasión',
    text: 'Nos encanta nuestro trabajo y ponemos el corazón en cada proyecto.',
  },
  {
    emoji: '🎯',
    title: 'Dedicación',
    text: 'Transformamos cada celebración en algo especial, cuidando cada detalle.',
  },
  {
    emoji: '🎀',
    title: 'Detalle',
    text: 'Algo original, bonito y personalizado que no pasará desapercibido.',
  },
  {
    emoji: '💫',
    title: 'Ilusión',
    text: 'Creemos que cada evento es una oportunidad para crear recuerdos inolvidables.',
  },
]
</script>

<style lang="scss" scoped>
.hero-small {
  background: linear-gradient(160deg, $color-mint-light 0%, $color-mint 60%, $color-mint-mid 140%);
  padding-block: $space-16;
  text-align: center;

  &__pre {
    display: inline-block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-pink;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-5xl;
    font-weight: 900;
    color: $color-green-dark;
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: $color-text-muted;
    max-width: 560px;
    margin-inline: auto;
    line-height: 1.7;
  }
}

.story {
  display: grid;
  grid-template-columns: 1fr;
  gap: $space-12;
  align-items: center;

  @include respond-to(lg) { grid-template-columns: 1fr 1.4fr; }

  &__visual {
    @include flex-center;
  }

  &__photo {
    width: 100%;
    max-width: 380px;
    border-radius: $radius-2xl;
    box-shadow: $shadow-lg;
    object-fit: cover;
  }

  &__pre {
    display: block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-pink;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-3xl;
    font-weight: 900;
    color: $color-green-dark;
    margin-bottom: $space-5;

    @include respond-to(md) { font-size: $text-4xl; }
  }

  &__text p {
    font-size: $text-base;
    color: $color-text-muted;
    line-height: 1.75;
    margin-bottom: $space-4;

    strong { color: $color-green-dark; }
  }

  &__closing {
    font-family: $font-heading;
    font-size: $text-2xl;
    font-weight: 900;
    color: $color-pink;
    margin-top: $space-6;
  }
}

.values {
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
    color: $color-pink;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-green-dark;
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-5;

    @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
    @include respond-to(lg) { grid-template-columns: repeat(3, 1fr); }
  }
}

.value-card {
  padding: $space-8 $space-6;
  background: $color-white;
  border-radius: $radius-xl;
  box-shadow: $shadow-md;
  border-left: 4px solid $color-pink;
  animation: fadeInUp 0.6s ease both;
  animation-delay: var(--delay, 0ms);
  transition: transform $transition-base, box-shadow $transition-base;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;
  }

  &__emoji { font-size: $text-2xl; display: block; margin-bottom: $space-4; }

  &__title {
    font-size: $text-lg;
    font-weight: 700;
    color: $color-green-dark;
    margin-bottom: $space-2;
  }

  &__text {
    font-size: $text-sm;
    color: $color-text-muted;
    line-height: 1.7;
  }
}

.info-band {
  display: grid;
  grid-template-columns: 1fr;
  gap: $space-6;
  background: $color-bg-alt;
  border-radius: $radius-2xl;
  padding: $space-10;

  @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
  @include respond-to(lg) { grid-template-columns: repeat(4, 1fr); }

  &__item {
    display: flex;
    gap: $space-4;
    align-items: flex-start;

    strong {
      display: block;
      font-size: $text-sm;
      font-weight: 700;
      color: $color-green-dark;
      margin-bottom: $space-1;
    }

    p {
      font-size: $text-sm;
      color: $color-text-muted;
      line-height: 1.6;

      a {
        color: $color-pink;
        text-decoration: none;
        &:hover { text-decoration: underline; }
      }
    }
  }

  &__emoji { font-size: $text-2xl; flex-shrink: 0; }
}
</style>
