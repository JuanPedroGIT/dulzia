<template>
  <section class="stats">
    <div class="container">
      <div class="stats__grid">
        <div v-for="stat in stats" :key="stat.label" class="stats__item">
          <span class="stats__emoji">{{ stat.emoji }}</span>
          <div class="stats__value">{{ stat.value }}</div>
          <div class="stats__label">{{ stat.label }}</div>
          <p v-if="stat.note" class="stats__note">{{ stat.note }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

// Cuántas secciones hay en el catálogo. Lo cuenta HomePage, que es quien lo carga:
// aquí estaba escrito a mano y se quedaba desfasado en cuanto se añadía una sección
// desde el panel. Hasta que llega el catálogo (0) se deja el hueco, nunca un
// "0 servicios", que es peor que esperar.
const props = defineProps({
  serviceCount: { type: Number, default: 0 },
})

const stats = computed(() => [
  { emoji: '🎉', value: '500+', label: 'Eventos realizados' },
  {
    emoji: '✨',
    value: props.serviceCount > 0 ? String(props.serviceCount) : '…',
    label: 'Servicios disponibles',
  },
  { emoji: '😊', value: '100%', label: 'Clientes satisfechos' },
  {
    emoji: '📍',
    value: 'Salamanca',
    label: 'Y alrededores',
    note: 'Nos movemos por toda la península',
  },
])
</script>

<style lang="scss" scoped>
.stats {
  @include gradient-mint;
  padding-block: $space-12;

  &__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: $space-8;
    text-align: center;

    @include respond-to(md) { grid-template-columns: repeat(4, 1fr); }
  }

  &__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: $space-2;
  }

  &__emoji { font-size: $text-2xl; }

  &__value {
    font-family: $font-heading;
    font-size: $text-3xl;
    font-weight: 900;
    color: $color-green-dark;
    line-height: 1;
  }

  &__label {
    font-size: $text-sm;
    color: rgba($color-green-dark, 0.75);
    font-weight: 500;
  }

  &__note {
    max-width: 22ch;
    font-size: $text-xs;
    font-style: italic;
    line-height: 1.5;
    color: rgba($color-green-dark, 0.6);
  }
}
</style>
