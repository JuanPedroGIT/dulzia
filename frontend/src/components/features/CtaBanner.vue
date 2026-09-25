<template>
  <section class="cta">
    <div class="container">
      <div class="cta__inner">
        <div class="cta__text">
          <h2 class="cta__title">¿Tienes un <em>evento</em> en mente?</h2>
          <p class="cta__sub">
            Cuéntanos tu idea y te preparamos un presupuesto sin compromiso.
            Respondemos en menos de 24 horas.
          </p>
        </div>
        <div class="cta__actions">
          <BaseButton to="/contacto" variant="light" size="lg">
            Pide presupuesto gratis
          </BaseButton>
          <a :href="telHref" class="cta__phone">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.66A2 2 0 012 2H5a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
            {{ phone }}
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import BaseButton from '@/components/ui/BaseButton.vue'
import { useContact } from '@/composables/useContact.js'

const { phone, telHref } = useContact()
</script>

<style lang="scss" scoped>
.cta {
  // Crema y no pistacho claro: la sección de reseñas ya es pistacho claro y sin
  // este escalón las dos se fundirían en una sola franja.
  background: $color-cream;
  padding-block: $space-16;

  &__inner {
    // Fucsia sólido en vez del degradado verde: es el cierre de la página y el
    // único bloque de marca a sangre. Fucsia oscuro y no puro porque el texto
    // blanco encima necesita 4,5:1 y con #ff00c1 se queda en 3,5:1.
    background: $color-fucsia-dark;
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
      background: rgba($color-white, 0.12);
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

    // El pistacho entra aquí dentro: el bloque de cierre es lo último que se ve
    // y así los dos colores de marca quedan en el mismo encuadre.
    em {
      font-style: normal;
      color: $color-pistacho-light;
    }
  }

  // Blanco puro, sin rebajar la opacidad: sobre el fucsia oscuro el blanco da
  // 4,50:1 y cualquier transparencia lo baja por debajo de AA.
  &__sub {
    font-size: $text-base;
    color: $color-white;
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
    color: $color-white;
    text-decoration: none;
    transition: opacity $transition-fast;

    // Se subraya en vez de cambiar de color: sobre el fucsia, cualquier tono
    // distinto del blanco puro se queda por debajo de 4,5:1.
    &:hover { text-decoration: underline; }
  }
}
</style>
