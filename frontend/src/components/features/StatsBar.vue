<template>
  <section class="stats">
    <div class="container">
      <div class="stats__grid">
        <div v-for="stat in stats" :key="stat.label" class="stats__item">
          <span class="stats__emoji">{{ stat.emoji }}</span>
          <div class="stats__value">{{ stat.value }}</div>
          <div class="stats__label">{{ stat.label }}</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const stats = [
  { emoji: '🎉', value: '200+', label: 'Eventos realizados' },
  { emoji: '✨', value: '11',   label: 'Servicios disponibles' },
  { emoji: '😊', value: '100%', label: 'Clientes satisfechos' },
  { emoji: '📍', value: 'Salamanca', label: 'Y alrededores' },
]
</script>

<style lang="scss" scoped>
.stats {
  @include gradient-teal;
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
    color: $color-white;
    line-height: 1;
  }

  &__label {
    font-size: $text-sm;
    color: rgba($color-white, 0.8);
    font-weight: 500;
  }
}
</style>
