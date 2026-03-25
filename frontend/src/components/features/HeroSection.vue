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
          Hacemos tus <em>celebraciones</em><br>
          <span class="hero__title-line2">inolvidables</span>
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
        <div class="hero__card hero__card--1">
          <span class="hero__card-emoji">🌭</span>
          <span>Hot Dog</span>
        </div>
        <div class="hero__card hero__card--2">
          <span class="hero__card-emoji">✨</span>
          <span>Glitter Bar</span>
        </div>
        <div class="hero__card hero__card--3">
          <span class="hero__card-emoji">🍫</span>
          <span>Chocolate</span>
        </div>
        <div class="hero__card hero__card--4">
          <span class="hero__card-emoji">🍬</span>
          <span>Candy Bar</span>
        </div>
        <div class="hero__card hero__card--5">
          <span class="hero__card-emoji">📸</span>
          <span>Photocall</span>
        </div>
        <div class="hero__card hero__card--6">
          <span class="hero__card-emoji">🎡</span>
          <span>Mini Feria</span>
        </div>
      </div>
    </div>

    <div class="hero__scroll">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </div>
  </section>
</template>

<script setup>
import BaseButton from '@/components/ui/BaseButton.vue'
</script>

<style lang="scss" scoped>
@use "@/styles/mixins" as *;
.hero {
  position: relative;
  min-height: calc(100vh - $nav-height);
  @include flex-center;
  overflow: hidden;
  background: linear-gradient(160deg, $color-navy 0%, #1e3a5f 50%, color.adjust($color-teal-dark, $lightness: -10%) 100%);

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
      background: $color-teal;
      top: -200px;
      right: -100px;
    }

    &--2 {
      width: 400px;
      height: 400px;
      background: color.adjust($color-teal, $lightness: 20%);
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
    color: $color-teal;
    background: rgba($color-teal, 0.12);
    border: 1px solid rgba($color-teal, 0.3);
    padding: $space-2 $space-4;
    border-radius: $radius-full;
    margin-bottom: $space-6;
  }

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-white;
    line-height: 1.1;
    margin-bottom: $space-6;

    @include respond-to(md) { font-size: $text-5xl; }
    @include respond-to(lg) { font-size: $text-6xl; }

    em {
      font-style: normal;
      @include text-gradient;
    }

    &-line2 {
      display: block;
    }
  }

  &__subtitle {
    font-size: $text-lg;
    color: rgba($color-white, 0.75);
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
    @include flex-center;
    flex-direction: column;
    gap: $space-2;
    padding: $space-5 $space-4;
    background: rgba($color-white, 0.06);
    border: 1px solid rgba($color-white, 0.1);
    border-radius: $radius-xl;
    backdrop-filter: blur(8px);
    transition: all $transition-base;
    cursor: default;

    span:last-child {
      font-size: $text-xs;
      font-weight: 600;
      color: rgba($color-white, 0.7);
      text-align: center;
    }

    &:hover {
      background: rgba($color-teal, 0.15);
      border-color: rgba($color-teal, 0.3);
      transform: translateY(-4px);
    }

    &--1 { animation: fadeInUp 0.6s 100ms both; }
    &--2 { animation: fadeInUp 0.6s 200ms both; }
    &--3 { animation: fadeInUp 0.6s 300ms both; }
    &--4 { animation: fadeInUp 0.6s 400ms both; }
    &--5 { animation: fadeInUp 0.6s 500ms both; }
    &--6 { animation: fadeInUp 0.6s 600ms both; }
  }

  &__card-emoji { font-size: 2rem; }

  &__scroll {
    position: absolute;
    bottom: $space-8;
    left: 50%;
    transform: translateX(-50%);
    color: rgba($color-white, 0.4);
    animation: bounce 2s infinite;

    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(6px); }
    }
  }
}
</style>
