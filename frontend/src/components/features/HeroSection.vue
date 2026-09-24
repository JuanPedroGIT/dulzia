<template>
  <section class="hero">
    <div class="hero__bg">
      <div class="hero__circle hero__circle--1" />
      <div class="hero__circle hero__circle--2" />
    </div>

    <div class="container hero__content">
      <div class="hero__text animate-fadeInUp">
        <span class="hero__badge">✨ Especialistas en eventos únicos · Salamanca</span>
        <h1 class="hero__title">
          Eventos y <em>celebraciones</em> inolvidables<br>
          <span class="hero__title-line2">en Salamanca</span>
        </h1>
        <p class="hero__subtitle">
          Carrito de Hot Dog, Candy Bar, Fuente de Chocolate, Photocall y mucho más.
          Convierte cada momento en un recuerdo eterno.
        </p>
        <div class="hero__actions">
          <BaseButton to="/contacto" variant="primary" size="lg">
            Pide tu presupuesto gratis
          </BaseButton>
          <BaseButton to="/servicios" variant="ghost" size="lg">
            Ver servicios
          </BaseButton>
        </div>
      </div>

      <div class="hero__cards animate-fadeIn">
        <router-link
          v-for="(card, i) in cards"
          :key="card.id"
          :to="`/servicios/${card.id}`"
          :class="['hero__card', `hero__card--${i + 1}`, { 'hero__card--ready': loaded.includes(card.id) }]"
        >
          <img
            v-if="card.image"
            :ref="el => checkAlreadyLoaded(el, card.id)"
            :src="card.thumbnail"
            :alt="card.label"
            class="hero__card-img"
            @load="markLoaded(card.id)"
          />
          <span class="hero__card-label">{{ card.label }}</span>
        </router-link>
      </div>
    </div>

    <div class="hero__scroll">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const props = defineProps({
  // Catálogo (lo carga HomePage): las tarjetas muestran la foto de su servicio.
  services: { type: Array, default: () => [] },
})

// Accesos rápidos del hero: las secciones marcadas como destacadas en el panel
// (`is_featured`), en el orden del catálogo. La rejilla es de 3 columnas y se
// adapta a cualquier número; se cortan en 6 para no alargar el hero.
const MAX_CARDS = 6

const cards = computed(() => props.services
  .filter(s => s.featured)
  .slice(0, MAX_CARDS)
  .map(s => ({
    id: s.id,
    label: s.name,
    image: s.image ?? null,
    // Las tarjetas son pequeñas (~200 px): se sirve la miniatura, no la grande.
    thumbnail: s.thumbnail || s.image || null,
  })))

// La tarjeta solo se muestra cuando su foto está disponible: hasta entonces el
// hueco (cuadrado) queda reservado y vacío, sin placeholder.
const loaded = ref([])
function markLoaded(id) {
  if (!loaded.value.includes(id)) loaded.value.push(id)
}
// En el HTML prerenderizado la imagen puede estar ya cargada al hidratar, y
// entonces no vuelve a emitir `load`: se mira el estado real del elemento.
function checkAlreadyLoaded(el, id) {
  if (el?.complete && el.naturalWidth > 0) markLoaded(id)
}
</script>

<style lang="scss" scoped>
@use "@/styles/mixins" as *;
.hero {
  position: relative;
  min-height: calc(100vh - $nav-height);
  @include flex-center;
  overflow: hidden;
  background: linear-gradient(160deg, $color-mint-light 0%, $color-mint 55%, $color-mint-mid 140%);

  &__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }

  &__circle {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;

    &--1 {
      width: 600px;
      height: 600px;
      background: $color-mint;
      top: -200px;
      right: -100px;
    }

    &--2 {
      width: 400px;
      height: 400px;
      background: $color-pink;
      opacity: 0.12;
      bottom: -100px;
      left: -50px;
    }
  }

  &__content {
    position: relative;
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-12;
    padding-block: $space-16;
    align-items: center;

    @include respond-to(lg) {
      grid-template-columns: 1fr 1fr;
      gap: $space-16;
    }
  }

  &__badge {
    display: inline-block;
    font-size: $text-sm;
    font-weight: 600;
    color: $color-green-dark;
    background: rgba($color-pink, 0.08);
    border: 1px solid rgba($color-pink, 0.35);
    padding: $space-2 $space-4;
    border-radius: $radius-full;
    margin-bottom: $space-6;
  }

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-green-dark;
    line-height: 1.1;
    margin-bottom: $space-6;

    @include respond-to(md) { font-size: $text-5xl; }
    @include respond-to(lg) { font-size: $text-6xl; }

    em {
      font-style: normal;
      @include text-gradient-pink;
    }

    &-line2 {
      display: block;
    }
  }

  &__subtitle {
    font-size: $text-lg;
    color: $color-text-muted;
    line-height: 1.7;
    max-width: 520px;
    margin-bottom: $space-10;

    @include respond-to(lg) { font-size: $text-xl; }
  }

  &__actions {
    display: flex;
    flex-wrap: wrap;
    gap: $space-4;
  }

  &__cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: $space-3;
    animation-delay: 200ms;

    @include respond-to(lg) {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  &__card {
    position: relative;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    // La tarjeta no se pinta hasta que su foto está cargada (`--ready`): el
    // hueco sigue reservado para que la rejilla no salte, pero no se ve nada.
    visibility: hidden;
    background: rgba($color-white, 0.78);
    border: 1px solid rgba($color-mint-mid, 0.3);
    border-radius: $radius-xl;
    backdrop-filter: blur(8px);
    transition: all $transition-base;

    // Cada tarjeta es un enlace a su servicio: el realce del hover se replica
    // en el foco de teclado para que se vea por dónde se va.
    &:hover,
    &:focus-visible {
      border-color: rgba($color-pink, 0.45);
      transform: translateY(-4px);
    }

    &--1 { animation: fadeInUp 0.6s 100ms both; }
    &--2 { animation: fadeInUp 0.6s 200ms both; }
    &--3 { animation: fadeInUp 0.6s 300ms both; }
    &--4 { animation: fadeInUp 0.6s 400ms both; }
    &--5 { animation: fadeInUp 0.6s 500ms both; }
    &--6 { animation: fadeInUp 0.6s 600ms both; }
  }

  &__card--ready {
    visibility: visible;
  }

  &__card-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &__card-label {
    position: absolute;
    inset: auto 0 0 0;
    padding: $space-6 $space-2 $space-3;
    font-size: $text-xs;
    font-weight: 700;
    color: $color-white;
    text-align: center;
    background: linear-gradient(to top, rgba($color-green-deep, 0.8), rgba($color-green-deep, 0));
  }

  &__scroll {
    position: absolute;
    bottom: $space-8;
    left: 50%;
    transform: translateX(-50%);
    color: rgba($color-green-dark, 0.45);
    animation: bounce 2s infinite;

    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(6px); }
    }
  }
}
</style>
