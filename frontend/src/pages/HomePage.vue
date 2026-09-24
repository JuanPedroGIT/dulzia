<template>
  <main>
    <HeroSection :services="services" />
    <StatsBar />
    <ServicesOverview :services="services" />
    <CtaBanner />
  </main>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import HeroSection from '@/components/features/HeroSection.vue'
import StatsBar from '@/components/features/StatsBar.vue'
import ServicesOverview from '@/components/features/ServicesOverview.vue'
import CtaBanner from '@/components/features/CtaBanner.vue'
import { useServices } from '@/composables/useServices.js'
import { useSeo, localBusinessJsonLd, servicesCatalogJsonLd } from '@/composables/useSeo.js'

useSeo({
  title: 'Carrito Hot Dog, Candy Bar, Photocall y más en Salamanca',
  description: 'Dulzia Salamanca Eventos: alquiler de carrito de hot dog, candy bar, fuente de chocolate, photocall, glitter bar y mucho más para bodas, cumpleaños y eventos en Salamanca.',
  path: '/',
  jsonLd: localBusinessJsonLd,
})

// El catálogo se carga una sola vez y se reparte a las tarjetas del hero, la
// parrilla de servicios y los datos estructurados.
const { services, fetchAll } = useServices()
onMounted(fetchAll)
watch(services, () => {
  if (services.value.length) {
    useSeo({ jsonLd: servicesCatalogJsonLd(services.value), jsonLdKey: 'catalog' })
  }
})
</script>
