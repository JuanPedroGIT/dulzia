<template>
  <main v-if="service && !loading">
    <!-- Hero -->
    <section class="hero">
      <div class="container hero__inner">
        <router-link to="/servicios" class="hero__back">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
          Todos los servicios
        </router-link>
        <div class="hero__content">
          <span class="hero__emoji">{{ service.emoji }}</span>
          <div>
            <span class="hero__category">{{ categoryLabel }}</span>
            <h1 class="hero__title">{{ service.name }}</h1>
            <p class="hero__desc">{{ service.description }}</p>
            <ul class="hero__features">
              <li v-for="f in service.features" :key="f">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                {{ f }}
              </li>
            </ul>
          </div>
        </div>
        <BaseButton to="/contacto" variant="primary" size="lg">
          Pedir presupuesto para este servicio
        </BaseButton>
      </div>
    </section>

    <!-- Examples gallery -->
    <section class="section">
      <div class="container">
        <div class="gallery__header">
          <span class="gallery__pre">Ejemplos reales</span>
          <h2 class="gallery__title">Así lo hacemos</h2>
          <p class="gallery__sub">
            Cada evento es único. Aquí tienes algunos ejemplos de cómo hemos
            llevado este servicio a distintas celebraciones.
          </p>
        </div>

        <div class="gallery__grid">
          <article
            v-for="(example, i) in service.examples"
            :key="i"
            class="example-card"
            :style="{ '--delay': `${i * 100}ms` }"
            @click="openLightbox(i)"
          >
            <div class="example-card__img-wrap">
              <img
                :src="example.image"
                :alt="example.title"
                loading="lazy"
                class="example-card__img"
              />
              <div class="example-card__overlay">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
              </div>
            </div>
            <div class="example-card__body">
              <h3 class="example-card__title">{{ example.title }}</h3>
              <p class="example-card__desc">{{ example.description }}</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- Lightbox -->
    <Transition name="lightbox">
      <div v-if="lightboxIndex !== null" class="lightbox" @click.self="closeLightbox">
        <button class="lightbox__close" @click="closeLightbox" aria-label="Cerrar">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <button class="lightbox__nav lightbox__nav--prev" @click="prevExample" aria-label="Anterior">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <div class="lightbox__content">
          <img
            :src="service.examples[lightboxIndex].image"
            :alt="service.examples[lightboxIndex].title"
            class="lightbox__img"
          />
          <div class="lightbox__info">
            <h3>{{ service.examples[lightboxIndex].title }}</h3>
            <p>{{ service.examples[lightboxIndex].description }}</p>
          </div>
        </div>
        <button class="lightbox__nav lightbox__nav--next" @click="nextExample" aria-label="Siguiente">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </Transition>

    <!-- Related services -->
    <section class="section section--alt">
      <div class="container">
        <div class="related__header">
          <h2 class="related__title">Otros servicios que te pueden interesar</h2>
        </div>
        <div class="related__grid">
          <ServiceCard
            v-for="(s, i) in related"
            :key="s.id"
            :service="s"
            :delay="`${i * 80}ms`"
            :show-cta="true"
          />
        </div>
      </div>
    </section>

    <CtaBanner />
  </main>

  <!-- Cargando -->
  <main v-else-if="loading" class="not-found">
    <div class="container"><span style="font-size:2rem;color:#6b7280">Cargando…</span></div>
  </main>

  <!-- 404 si no existe el servicio -->
  <main v-else class="not-found">
    <div class="container">
      <span style="font-size:4rem">🤔</span>
      <h1>Servicio no encontrado</h1>
      <BaseButton to="/servicios" variant="primary" size="lg">Ver todos los servicios</BaseButton>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import ServiceCard from '@/components/features/ServiceCard.vue'
import CtaBanner from '@/components/features/CtaBanner.vue'
import { useService, useServices } from '@/composables/useServices.js'
import { useSeo } from '@/composables/useSeo.js'

const route = useRoute()

const { service, loading, fetchOne } = useService(route.params.id)

const { services: allServices, fetchAll } = useServices()

onMounted(async () => {
  await fetchOne()
  if (service.value) {
    useSeo({
      title: service.value.name,
      description: service.value.description,
      path: `/servicios/${service.value.id}`,
    })
    fetchAll()
  }
})

const categories = { food: 'Gastronomía', decoration: 'Decoración', experience: 'Experiencias' }
const categoryLabel = computed(() => categories[service.value?.category] ?? '')

const related = computed(() =>
  allServices.value
    .filter(s => s.id !== service.value?.id && s.category === service.value?.category)
    .slice(0, 3)
)

// Lightbox
const lightboxIndex = ref(null)

function openLightbox(i) { lightboxIndex.value = i }
function closeLightbox() { lightboxIndex.value = null }
function nextExample() {
  lightboxIndex.value = (lightboxIndex.value + 1) % service.value.examples.length
}
function prevExample() {
  lightboxIndex.value = (lightboxIndex.value - 1 + service.value.examples.length) % service.value.examples.length
}

function onKey(e) {
  if (lightboxIndex.value === null) return
  if (e.key === 'Escape') closeLightbox()
  if (e.key === 'ArrowRight') nextExample()
  if (e.key === 'ArrowLeft') prevExample()
}
onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))
</script>

<style lang="scss" scoped>
.hero {
  background: linear-gradient(135deg, $color-navy 0%, $color-navy-light 100%);
  padding-block: $space-12 $space-16;

  &__inner {
    display: flex;
    flex-direction: column;
    gap: $space-8;
  }

  &__back {
    display: inline-flex;
    align-items: center;
    gap: $space-2;
    font-size: $text-sm;
    font-weight: 600;
    color: rgba($color-white, 0.6);
    text-decoration: none;
    transition: color $transition-fast;
    width: fit-content;

    &:hover { color: $color-teal; }
  }

  &__content {
    display: flex;
    flex-direction: column;
    gap: $space-6;
    align-items: flex-start;

    @include respond-to(md) {
      flex-direction: row;
      align-items: flex-start;
      gap: $space-8;
    }
  }

  &__emoji {
    font-size: 4rem;
    flex-shrink: 0;
    background: rgba($color-white, 0.06);
    border: 1px solid rgba($color-white, 0.1);
    border-radius: $radius-xl;
    padding: $space-5;
    display: block;
  }

  &__category {
    display: block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-teal;
    margin-bottom: $space-2;
  }

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-white;
    margin-bottom: $space-4;

    @include respond-to(md) { font-size: $text-5xl; }
  }

  &__desc {
    font-size: $text-lg;
    color: rgba($color-white, 0.75);
    line-height: 1.7;
    max-width: 600px;
    margin-bottom: $space-5;
  }

  &__features {
    display: flex;
    flex-wrap: wrap;
    gap: $space-3;

    li {
      display: flex;
      align-items: center;
      gap: $space-2;
      font-size: $text-sm;
      font-weight: 600;
      color: rgba($color-white, 0.8);
      background: rgba($color-white, 0.06);
      border: 1px solid rgba($color-white, 0.1);
      padding: $space-2 $space-3;
      border-radius: $radius-full;

      svg { color: $color-teal; flex-shrink: 0; }
    }
  }
}

.gallery {
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
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: $color-text-muted;
    max-width: 520px;
    margin-inline: auto;
    line-height: 1.7;
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-6;

    @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
    @include respond-to(lg) { grid-template-columns: repeat(2, 1fr); }
  }
}

.example-card {
  background: $color-white;
  border-radius: $radius-xl;
  overflow: hidden;
  box-shadow: $shadow-md;
  cursor: pointer;
  animation: fadeInUp 0.6s ease both;
  animation-delay: var(--delay, 0ms);
  transition: transform $transition-base, box-shadow $transition-base;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;

    .example-card__overlay { opacity: 1; }
    .example-card__img { transform: scale(1.04); }
  }

  &__img-wrap {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
  }

  &__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
  }

  &__overlay {
    position: absolute;
    inset: 0;
    background: rgba($color-navy, 0.45);
    @include flex-center;
    opacity: 0;
    transition: opacity $transition-base;
    color: $color-white;
  }

  &__body {
    padding: $space-6;
  }

  &__title {
    font-size: $text-lg;
    font-weight: 700;
    color: $color-navy;
    margin-bottom: $space-3;
  }

  &__desc {
    font-size: $text-sm;
    color: $color-text-muted;
    line-height: 1.7;
  }
}

// Lightbox
.lightbox {
  position: fixed;
  inset: 0;
  z-index: 300;
  background: rgba($color-navy, 0.95);
  @include flex-center;
  padding: $space-4;

  &__close {
    position: absolute;
    top: $space-5;
    right: $space-5;
    @include flex-center;
    width: 44px;
    height: 44px;
    border-radius: $radius-full;
    background: rgba($color-white, 0.1);
    color: $color-white;
    transition: background $transition-fast;

    &:hover { background: rgba($color-white, 0.2); }
  }

  &__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    @include flex-center;
    width: 48px;
    height: 48px;
    border-radius: $radius-full;
    background: rgba($color-white, 0.1);
    color: $color-white;
    transition: background $transition-fast;
    z-index: 1;

    &:hover { background: rgba($color-white, 0.2); }
    &--prev { left: $space-4; }
    &--next { right: $space-4; }
  }

  &__content {
    max-width: 860px;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: $space-5;
  }

  &__img {
    width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: $radius-lg;
  }

  &__info {
    text-align: center;

    h3 {
      font-size: $text-xl;
      color: $color-white;
      margin-bottom: $space-2;
    }

    p {
      font-size: $text-sm;
      color: rgba($color-white, 0.7);
      max-width: 600px;
      margin-inline: auto;
      line-height: 1.7;
    }
  }
}

.lightbox-enter-active, .lightbox-leave-active { transition: opacity 0.25s ease; }
.lightbox-enter-from, .lightbox-leave-to { opacity: 0; }

// Related
.related {
  &__header { margin-bottom: $space-10; }

  &__title {
    font-size: $text-2xl;
    font-weight: 900;
    color: $color-navy;
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-6;

    @include respond-to(md) { grid-template-columns: repeat(3, 1fr); }
  }
}

.not-found {
  min-height: 60vh;
  @include flex-center;
  text-align: center;

  .container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: $space-6;

    h1 { font-size: $text-3xl; color: $color-navy; }
  }
}
</style>
