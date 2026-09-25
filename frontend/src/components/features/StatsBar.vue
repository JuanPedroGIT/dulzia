<template>
  <section class="stats">
    <div class="container">
      <div class="stats__grid">
        <div v-for="stat in stats" :key="stat.label" class="stats__item">
          <span class="stats__icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path v-for="(d, i) in stat.icon" :key="i" :d="d" />
            </svg>
          </span>
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

// Iconos de línea propios (trazos SVG) en vez de emojis: el emoji lo pinta la
// fuente del sistema y cambiaba de estilo según el dispositivo, así que la barra
// no se veía igual en todas partes. Cada `icon` es la lista de trazos del svg.
const ICONS = {
  calendario: [
    'M3 5.5A2.5 2.5 0 0 1 5.5 3h13A2.5 2.5 0 0 1 21 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5z',
    'M8 2v4M16 2v4M3 10h18M9 16l2 2 4-4',
  ],
  rejilla: [
    'M3.5 3.5h6v6h-6zM14.5 3.5h6v6h-6zM3.5 14.5h6v6h-6zM14.5 14.5h6v6h-6z',
  ],
  corazon: [
    'M20.8 5.6a5.4 5.4 0 0 0-7.7 0L12 6.7l-1.1-1.1a5.4 5.4 0 1 0-7.7 7.7L12 22l8.8-8.7a5.4 5.4 0 0 0 0-7.7z',
  ],
  pin: [
    'M20 10.4c0 6.2-8 11.6-8 11.6s-8-5.4-8-11.6a8 8 0 1 1 16 0z',
    'M12 13.2a2.8 2.8 0 1 0 0-5.6 2.8 2.8 0 0 0 0 5.6z',
  ],
}

const stats = computed(() => [
  { icon: ICONS.calendario, value: '500+', label: 'Eventos realizados' },
  {
    icon: ICONS.rejilla,
    value: props.serviceCount > 0 ? String(props.serviceCount) : '…',
    label: 'Servicios disponibles',
  },
  { icon: ICONS.corazon, value: '100%', label: 'Clientes satisfechos' },
  {
    icon: ICONS.pin,
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

  // El icono va en un disco blanco con el trazo en fucsia: sobre el pistacho, el
  // fucsia suelto no tendría contraste suficiente, y así además ata los dos
  // colores de marca en la misma sección.
  &__icon {
    @include flex-center;
    width: 48px;
    height: 48px;
    border-radius: $radius-full;
    background: $color-white;
    color: $color-fucsia-dark;
    box-shadow: 0 2px 8px rgba($color-text, 0.12);
    margin-bottom: $space-1;
  }

  &__value {
    font-family: $font-heading;
    font-size: $text-3xl;
    font-weight: 900;
    color: $color-text;
    line-height: 1;
  }

  // Etiquetas y nota al 85% del neutro: sobre pistacho quedan en 4,96:1, que pasa
  // AA. Con el 0,75/0,6 que había antes se quedaban en 4,0:1.
  &__label {
    font-size: $text-sm;
    color: rgba($color-text, 0.85);
    font-weight: 500;
  }

  &__note {
    max-width: 22ch;
    font-size: $text-xs;
    font-style: italic;
    line-height: 1.5;
    color: rgba($color-text, 0.85);
  }
}
</style>
