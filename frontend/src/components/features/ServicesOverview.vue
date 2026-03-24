<template>
  <section class="section">
    <div class="container">
      <div class="overview__header">
        <span class="overview__pre">Lo que hacemos</span>
        <h2 class="overview__title">Servicios que enamoran</h2>
        <p class="overview__sub">
          Desde el aperitivo hasta el último detalle, tenemos todo lo que necesitas
          para que tu celebración sea perfecta.
        </p>
      </div>

      <div class="overview__grid">
        <ServiceCard
          v-for="(service, i) in featured"
          :key="service.id"
          :service="service"
          :delay="`${i * 80}ms`"
        />
      </div>

      <div class="overview__cta">
        <BaseButton to="/servicios" variant="outline" size="lg">
          Ver todos los servicios ({{ total }})
        </BaseButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import ServiceCard from '@/components/features/ServiceCard.vue'
import { services } from '@/data/services.js'

const featured = computed(() => services.slice(0, 6))
const total = services.length
</script>

<style lang="scss" scoped>
.overview {
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
    max-width: 560px;
    margin-inline: auto;
    line-height: 1.7;
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-6;

    @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
    @include respond-to(lg) { grid-template-columns: repeat(3, 1fr); }
  }

  &__cta {
    text-align: center;
    margin-top: $space-12;
  }
}
</style>
