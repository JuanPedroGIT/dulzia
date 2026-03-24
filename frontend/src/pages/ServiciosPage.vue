<template>
  <main>
    <section class="hero-small">
      <div class="container">
        <span class="hero-small__pre">Todo lo que necesitas</span>
        <h1 class="hero-small__title">Nuestros Servicios</h1>
        <p class="hero-small__sub">
          11 servicios únicos para que cada celebración sea perfecta,
          desde la gastronomía hasta la decoración y las experiencias.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <!-- Filter tabs -->
        <div class="filter">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="filter__tab"
            :class="{ 'filter__tab--active': activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- Services grid -->
        <div class="services-grid">
          <ServiceCard
            v-for="(service, i) in filtered"
            :key="service.id"
            :service="service"
            :delay="`${i * 60}ms`"
            :show-features="true"
            :show-cta="true"
          />
        </div>
      </div>
    </section>

    <CtaBanner />
  </main>
</template>

<script setup>
import { ref, computed } from 'vue'
import ServiceCard from '@/components/features/ServiceCard.vue'
import CtaBanner from '@/components/features/CtaBanner.vue'
import { services } from '@/data/services.js'

import { useSeo } from '@/composables/useSeo.js'
useSeo({
  title: 'Servicios para eventos en Salamanca',
  description: 'Carrito hot dog, candy bar, fuente de chocolate, photocall, glitter bar, algodón de azúcar, palomitero, picnic & tipis y más. Servicios para bodas, cumpleaños y eventos en Salamanca.',
  path: '/servicios',
})

const activeTab = ref('all')

const tabs = [
  { id: 'all',        label: 'Todos' },
  { id: 'food',       label: '🍴 Gastronomía' },
  { id: 'decoration', label: '🎨 Decoración' },
  { id: 'experience', label: '✨ Experiencias' },
]

const filtered = computed(() =>
  activeTab.value === 'all'
    ? services
    : services.filter(s => s.category === activeTab.value)
)
</script>

<style lang="scss" scoped>
.hero-small {
  background: linear-gradient(135deg, $color-navy 0%, $color-navy-light 100%);
  padding-block: $space-16;
  text-align: center;

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
    font-size: $text-5xl;
    font-weight: 900;
    color: $color-white;
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: rgba($color-white, 0.75);
    max-width: 560px;
    margin-inline: auto;
    line-height: 1.7;
  }
}

.filter {
  display: flex;
  flex-wrap: wrap;
  gap: $space-2;
  margin-bottom: $space-10;

  &__tab {
    padding: $space-2 $space-5;
    border: 2px solid $color-border;
    border-radius: $radius-full;
    font-size: $text-sm;
    font-weight: 600;
    color: $color-text-muted;
    background: $color-white;
    transition: all $transition-base;
    cursor: pointer;

    &:hover { border-color: $color-teal; color: $color-teal; }

    &--active {
      background: $color-teal;
      border-color: $color-teal;
      color: $color-white;
    }
  }
}

.services-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: $space-6;

  @include respond-to(md) { grid-template-columns: repeat(2, 1fr); }
  @include respond-to(lg) { grid-template-columns: repeat(3, 1fr); }
}
</style>
