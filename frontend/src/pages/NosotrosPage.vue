<template>
  <main>
    <section class="hero-small">
      <div class="container">
        <span class="hero-small__pre">Quiénes somos</span>
        <h1 class="hero-small__title">Nuestra historia</h1>
        <p class="hero-small__sub">
          Somos un equipo apasionado por crear experiencias únicas
          y celebraciones que perduran en la memoria.
        </p>
      </div>
    </section>

    <!-- Story -->
    <section class="section">
      <div class="container">
        <div class="story">
          <div class="story__visual">
            <div class="story__blob">
              <span class="story__big-emoji">✨</span>
            </div>
          </div>
          <div class="story__text animate-fadeInUp">
            <span class="story__pre">Nuestra historia</span>
            <h2 class="story__title">Nacimos de la pasión por los eventos</h2>
            <p>
              Dulzia Salamanca Eventos nació de una idea simple: que cada celebración merezca ser especial.
              Fundada por <strong>Sergio García Ramos</strong>, la empresa empezó con el deseo de ofrecer
              experiencias gastronómicas y de entretenimiento únicas para eventos en Salamanca y sus alrededores.
            </p>
            <p>
              Hoy contamos con más de <strong>200 eventos realizados</strong> y un catálogo de 11 servicios
              que cubren desde la gastronomía más divertida hasta los detalles decorativos más especiales.
            </p>
            <p>
              Cada evento es único para nosotros. Nos implicamos desde el primer contacto hasta el último
              momento de tu celebración, asegurándonos de que todo salga perfecto.
            </p>
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
            <span class="info-band__emoji">📍</span>
            <div>
              <strong>Ubicación</strong>
              <p>C. Martín Alonso Pedraz, 14<br>Salamanca</p>
            </div>
          </div>
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
              <p><a href="tel:+34629991659">+34 629 991 659</a></p>
            </div>
          </div>
          <div class="info-band__item">
            <span class="info-band__emoji">✉️</span>
            <div>
              <strong>Email</strong>
              <p><a href="mailto:info@dulziasalamancaeventos.com">info@dulziasalamancaeventos.com</a></p>
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

import { useSeo } from '@/composables/useSeo.js'
useSeo({
  title: 'Quiénes somos',
  description: 'Conoce a Dulzia Salamanca Eventos, empresa especializada en hacer tus celebraciones únicas e inolvidables en Salamanca y alrededores.',
  path: '/nosotros',
})

const values = [
  {
    emoji: '🎯',
    title: 'Atención personalizada',
    text: 'Cada evento es diferente. Escuchamos tus necesidades y adaptamos nuestros servicios para que todo sea exactamente como lo imaginas.',
  },
  {
    emoji: '⭐',
    title: 'Calidad garantizada',
    text: 'Trabajamos solo con materiales y productos de primera calidad. Porque los detalles marcan la diferencia en cada celebración.',
  },
  {
    emoji: '⏰',
    title: 'Puntualidad y profesionalidad',
    text: 'Llegamos siempre antes de tiempo, montamos con cuidado y dejamos todo impecable. Tu tranquilidad es nuestra prioridad.',
  },
  {
    emoji: '💡',
    title: 'Creatividad sin límites',
    text: 'Nos encanta innovar y sorprender. Combinamos tendencias actuales con ideas propias para crear experiencias únicas.',
  },
  {
    emoji: '🤝',
    title: 'Compromiso real',
    text: 'Nos implicamos de verdad en tu evento. No somos solo proveedores, somos parte de tu celebración.',
  },
  {
    emoji: '💰',
    title: 'Precio justo',
    text: 'Ofrecemos presupuestos transparentes y adaptados. Sin sorpresas ni costes ocultos, siempre lo que acordamos.',
  },
]
</script>

<style lang="scss" scoped>
.hero-small {
  background: linear-gradient(135deg, $color-navy 0%, $color-navy-light 100%);
  padding-block: $space-16;
  text-align: center;

  &__pre {
    display: inline-block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-teal;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-5xl;
    font-weight: 900;
    color: $color-white;
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: rgba($color-white, 0.75);
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

  &__blob {
    @include flex-center;
    width: 300px;
    height: 300px;
    border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%;
    background: linear-gradient(135deg, rgba($color-teal, 0.15), rgba($color-navy, 0.08));
    border: 2px solid rgba($color-teal, 0.2);
  }

  &__big-emoji { font-size: 5rem; }

  &__pre {
    display: block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-teal;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-3xl;
    font-weight: 900;
    color: $color-navy;
    margin-bottom: $space-5;

    @include respond-to(md) { font-size: $text-4xl; }
  }

  &__text p {
    font-size: $text-base;
    color: $color-text-muted;
    line-height: 1.75;
    margin-bottom: $space-4;

    strong { color: $color-navy; }
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
    color: $color-teal;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-navy;
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
  border-left: 4px solid $color-teal;
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
    color: $color-navy;
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
      color: $color-navy;
      margin-bottom: $space-1;
    }

    p {
      font-size: $text-sm;
      color: $color-text-muted;
      line-height: 1.6;

      a {
        color: $color-teal;
        text-decoration: none;
        &:hover { text-decoration: underline; }
      }
    }
  }

  &__emoji { font-size: $text-2xl; flex-shrink: 0; }
}
</style>
