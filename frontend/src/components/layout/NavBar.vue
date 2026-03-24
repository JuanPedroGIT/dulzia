<template>
  <header class="nav" :class="{ 'nav--scrolled': scrolled, 'nav--open': menuOpen }">
    <div class="nav__inner container">
      <router-link to="/" class="nav__logo" @click="menuOpen = false">
        <span class="nav__logo-icon">✨</span>
        <span class="nav__logo-text">Dulzia <em>Salamanca</em></span>
      </router-link>

      <nav class="nav__links" aria-label="Navegación principal">
        <router-link to="/" class="nav__link" @click="menuOpen = false">Inicio</router-link>
        <router-link to="/servicios" class="nav__link" @click="menuOpen = false">Servicios</router-link>
        <router-link to="/nosotros" class="nav__link" @click="menuOpen = false">Nosotros</router-link>
        <BaseButton to="/contacto" variant="primary" size="sm" @click="menuOpen = false">
          Pide presupuesto
        </BaseButton>
      </nav>

      <button
        class="nav__burger"
        :aria-expanded="menuOpen"
        aria-label="Abrir menú"
        @click="menuOpen = !menuOpen"
      >
        <span /><span /><span />
      </button>
    </div>

    <!-- Mobile menu -->
    <div class="nav__mobile" :class="{ 'nav__mobile--open': menuOpen }">
      <router-link to="/" class="nav__mobile-link" @click="menuOpen = false">Inicio</router-link>
      <router-link to="/servicios" class="nav__mobile-link" @click="menuOpen = false">Servicios</router-link>
      <router-link to="/nosotros" class="nav__mobile-link" @click="menuOpen = false">Nosotros</router-link>
      <router-link to="/contacto" class="nav__mobile-link nav__mobile-link--cta" @click="menuOpen = false">
        Pide presupuesto
      </router-link>
    </div>
  </header>
  <div class="nav__spacer" />
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const scrolled = ref(false)
const menuOpen = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 60
}

onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style lang="scss" scoped>
.nav {
  position: fixed;
  top: 0;
  inset-inline: 0;
  z-index: 100;
  height: $nav-height;
  background: rgba($color-white, 0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid transparent;
  transition: border-color $transition-base, box-shadow $transition-base;

  &--scrolled {
    border-bottom-color: $color-border;
    box-shadow: $shadow-sm;
  }

  &__inner {
    @include flex-between;
    height: 100%;
  }

  &__logo {
    display: flex;
    align-items: center;
    gap: $space-2;
    text-decoration: none;

    &-icon { font-size: $text-xl; }

    &-text {
      font-family: $font-heading;
      font-size: $text-xl;
      font-weight: 700;
      color: $color-navy;

      em {
        font-style: normal;
        color: $color-teal;
      }
    }
  }

  &__links {
    display: none;
    align-items: center;
    gap: $space-8;

    @include respond-to(lg) { display: flex; }
  }

  &__link {
    font-size: $text-sm;
    font-weight: 500;
    color: $color-text-muted;
    text-decoration: none;
    transition: color $transition-fast;
    padding-block: $space-2;
    border-bottom: 2px solid transparent;

    &:hover,
    &.router-link-active {
      color: $color-teal;
      border-bottom-color: $color-teal;
    }
  }

  &__burger {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: $space-2;

    @include respond-to(lg) { display: none; }

    span {
      display: block;
      width: 24px;
      height: 2px;
      background: $color-navy;
      border-radius: $radius-full;
      transition: all $transition-base;
    }
  }

  &--open .nav__burger {
    span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    span:nth-child(2) { opacity: 0; }
    span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
  }

  &__mobile {
    display: flex;
    flex-direction: column;
    background: $color-white;
    border-top: 1px solid $color-border;
    max-height: 0;
    overflow: hidden;
    transition: max-height $transition-slow, padding $transition-base;

    @include respond-to(lg) { display: none; }

    &--open {
      max-height: 400px;
      padding-block: $space-4;
    }
  }

  &__mobile-link {
    display: block;
    padding: $space-3 $space-6;
    font-size: $text-base;
    font-weight: 500;
    color: $color-text;
    text-decoration: none;
    transition: color $transition-fast, background $transition-fast;

    &:hover, &.router-link-active {
      color: $color-teal;
      background: $color-bg-alt;
    }

    &--cta {
      margin: $space-4 $space-6 $space-2;
      text-align: center;
      background: $color-teal;
      color: $color-white !important;
      border-radius: $radius-full;
      padding: $space-3 $space-6;

      &:hover { background: $color-teal-dark; }
    }
  }

  &__spacer { height: $nav-height; }
}
</style>
