<template>
  <section class="cta">
    <div class="container">
      <div class="cta__inner">
        <div class="cta__text">
          <h2 class="cta__title">¿Tienes un evento en mente?</h2>
          <p class="cta__sub">
            Cuéntanos tu idea y te preparamos un presupuesto sin compromiso.
            Respondemos en menos de 24 horas.
          </p>
        </div>
        <div class="cta__actions">
          <BaseButton to="/contacto" variant="primary" size="lg">
            Pide presupuesto gratis
          </BaseButton>
          <a href="tel:+34629991659" class="cta__phone">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.66A2 2 0 012 2H5a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
            +34 629 991 659
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import BaseButton from '@/components/ui/BaseButton.vue'
</script>

<style lang="scss" scoped>
.cta {
  background: $color-bg-alt;
  padding-block: $space-16;

  &__inner {
    background: $color-navy;
    border-radius: $radius-2xl;
    padding: $space-12 $space-8;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: $space-8;
    position: relative;
    overflow: hidden;

    @include respond-to(lg) {
      flex-direction: row;
      text-align: left;
      justify-content: space-between;
    }

    &::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba($color-teal, 0.1);
      pointer-events: none;
    }
  }

  &__text { flex: 1; }

  &__title {
    font-size: $text-3xl;
    font-weight: 900;
    color: $color-white;
    margin-bottom: $space-3;

    @include respond-to(md) { font-size: $text-4xl; }
  }

  &__sub {
    font-size: $text-base;
    color: rgba($color-white, 0.7);
    max-width: 460px;
    line-height: 1.7;
  }

  &__actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: $space-4;
    flex-shrink: 0;

    @include respond-to(md) { flex-direction: row; }
  }

  &__phone {
    display: flex;
    align-items: center;
    gap: $space-2;
    font-size: $text-base;
    font-weight: 600;
    color: rgba($color-white, 0.7);
    text-decoration: none;
    transition: color $transition-fast;

    &:hover { color: $color-teal; }
  }
}
</style>
