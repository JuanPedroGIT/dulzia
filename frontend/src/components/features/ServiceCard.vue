<template>
  <article class="card" :style="{ '--delay': delay }">
    <div class="card__emoji">{{ service.emoji }}</div>
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
    <div v-if="showCta" class="card__footer">
      <BaseButton to="/contacto" variant="outline" size="sm">Consultar</BaseButton>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import { categories } from '@/data/services.js'

const props = defineProps({
  service:      { type: Object, required: true },
  delay:        { type: String, default: '0ms' },
  showFeatures: { type: Boolean, default: false },
  showCta:      { type: Boolean, default: false },
})

const categoryLabel = computed(() => categories[props.service.category] ?? '')
</script>

<style lang="scss" scoped>
.card {
  @include card;
  display: flex;
  flex-direction: column;
  animation: fadeInUp 0.6s ease both;
  animation-delay: var(--delay, 0ms);

  &__emoji {
    @include flex-center;
    font-size: 2.5rem;
    background: $color-bg-alt;
    padding: $space-8;
    min-height: 100px;
    transition: background $transition-base;
  }

  &:hover &__emoji {
    background: linear-gradient(135deg, rgba($color-teal, 0.12), rgba($color-teal-dark, 0.08));
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
    color: $color-teal;
  }

  &__name {
    font-size: $text-lg;
    font-weight: 700;
    color: $color-navy;
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

      svg { color: $color-teal; flex-shrink: 0; }
    }
  }

  &__footer {
    padding: $space-4 $space-6 $space-5;
    border-top: 1px solid $color-border;
  }
}
</style>
