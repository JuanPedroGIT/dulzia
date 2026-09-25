<template>
  <section v-if="featured.length" class="section">
    <div class="container">
      <div class="overview__header">
        <span class="overview__pre">Lo que hacemos</span>
        <h2 class="overview__title">Servicios que <em>enamoran</em></h2>
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

// El catálogo lo carga y lo reparte HomePage: así la portada hace una sola
// petición a /api/services para el hero, esta parrilla y el JSON-LD.
const props = defineProps({
  services: { type: Array, default: () => [] },
})

// Parrilla de la portada: las secciones marcadas como destacadas en el panel
// (`is_featured`), en el orden del catálogo. Antes eran los 6 primeros por
// sort_order, que no era una decisión editorial. Sin ninguna marcada, la sección
// no se pinta (quedaría el titular con el hueco vacío).
const featured = computed(() => props.services.filter(s => s.featured))
const total = computed(() => props.services.length)
</script>

<style lang="scss" scoped>
// Fondo blanco: alterna con el pistacho del hero y las cifras y con el pistacho
// claro de las reseñas, para que la paleta se reparta por todo el scroll.
.section { background: $color-white; }

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
    // Fucsia de texto y no el de marca: a 14 px sobre blanco, #ff00c1 se queda
    // en 3,49:1 y este llega a 7,15:1.
    color: $color-fucsia-text;
    margin-bottom: $space-3;
  }

  // En pistacho oscuro sobre el blanco: 3,81:1, que pasa AA para titular grande
  // (y para el cuerpo de texto de al lado se sigue usando el neutro, 14,2:1).
  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-pistacho-dark;
    margin-bottom: $space-4;

    em {
      font-style: normal;
      color: $color-fucsia;
    }
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
