<template>
  <router-link :to="`/servicios/${service.id}`" class="card" :style="{ '--delay': delay }">
    <div class="card__media">
      <img v-if="service.image" :src="service.thumbnail || service.image" :alt="service.name" class="card__img" loading="lazy" />
      <span v-else class="card__emoji">{{ service.emoji }}</span>
    </div>
    <div class="card__body">
      <span class="card__category">{{ categoryLabel }}</span>
      <h3 class="card__name">{{ service.name }}</h3>
      <p class="card__desc">{{ service.description }}</p>
      <ul v-if="showFeatures" class="card__features">
        <li v-for="f in service.features" :key="f">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
          {{ f }}
        </li>
      </ul>
    </div>
    <div class="card__footer">
      <span class="card__link">Ver ejemplos →</span>
    </div>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useCategories } from '@/composables/useCategories.js'

const props = defineProps({
  service:      { type: Object, required: true },
  delay:        { type: String, default: '0ms' },
  showFeatures: { type: Boolean, default: false },
  showCta:      { type: Boolean, default: false },
})

const { categoryName } = useCategories()

const categoryLabel = computed(() => categoryName(props.service.category))
</script>

<style lang="scss" scoped>
.card {
  @include card;
  display: flex;
  flex-direction: column;
  animation: fadeInUp 0.6s ease both;
  animation-delay: var(--delay, 0ms);

  &__media {
    @include flex-center;
    background: $color-bg-alt;
    overflow: hidden;
    transition: background $transition-base;
  }

  // Sin foto se muestra el emoji de la sección (o el hueco neutro si tampoco hay).
  &__emoji {
    @include flex-center;
    width: 100%;
    font-size: 2.5rem;
    padding: $space-8;
    min-height: 100px;
    transition: background $transition-base;
  }

  &__img {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    transition: transform $transition-base;
  }

  &:hover &__emoji {
    background: linear-gradient(135deg, rgba($color-pink, 0.1), rgba($color-pink-dark, 0.06));
  }

  &:hover &__img {
    transform: scale(1.04);
  }

  &__body {
    padding: $space-5 $space-6;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: $space-2;
  }

  &__category {
    font-size: $text-xs;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    // Fucsia de texto: el de marca a 12 px sobre blanco no pasa AA (3,49:1).
    color: $color-fucsia-text;
  }

  &__name {
    font-size: $text-lg;
    font-weight: 700;
    color: $color-green-dark;
  }

  &__desc {
    font-size: $text-sm;
    color: $color-text-muted;
    line-height: 1.65;
    flex: 1;
  }

  &__features {
    margin-top: $space-3;
    display: flex;
    flex-direction: column;
    gap: $space-2;

    li {
      display: flex;
      align-items: center;
      gap: $space-2;
      font-size: $text-sm;
      color: $color-text;

      svg { color: $color-pink; flex-shrink: 0; }
    }
  }

  &__footer {
    padding: $space-4 $space-6 $space-5;
    border-top: 1px solid $color-border;
  }

  &__link {
    font-size: $text-sm;
    font-weight: 700;
    // El "Ver ejemplos →" es el otro punto donde asoma la marca dentro del cuerpo
    // de la página, no solo en el header y el hero.
    color: $color-fucsia-dark;
    transition: gap $transition-fast;
  }
}
</style>
