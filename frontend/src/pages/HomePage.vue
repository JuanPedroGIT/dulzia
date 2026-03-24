<template>
  <main>
    <HeroSection />
    <StatsBar />
    <ServicesOverview />
    <CtaBanner />
  </main>
</template>

<script setup>
import HeroSection from '@/components/features/HeroSection.vue'
import StatsBar from '@/components/features/StatsBar.vue'
import ServicesOverview from '@/components/features/ServicesOverview.vue'
import CtaBanner from '@/components/features/CtaBanner.vue'
import { useSeo, localBusinessJsonLd } from '@/composables/useSeo.js'

useSeo({
  title: 'Carrito Hot Dog, Candy Bar, Photocall y más en Salamanca',
  description: 'Dulzia Salamanca Eventos: alquiler de carrito de hot dog, candy bar, fuente de chocolate, photocall, glitter bar y mucho más para bodas, cumpleaños y eventos en Salamanca.',
  path: '/',
  jsonLd: localBusinessJsonLd,
})
</script>
