<template>
  <main>
    <HeroSection :services="services" />
    <StatsBar :service-count="services.length" />
    <ServicesOverview :services="services" />
    <ReviewsSection />
    <CtaBanner />
  </main>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import HeroSection from '@/components/features/HeroSection.vue'
import StatsBar from '@/components/features/StatsBar.vue'
import ServicesOverview from '@/components/features/ServicesOverview.vue'
import ReviewsSection from '@/components/features/ReviewsSection.vue'
import CtaBanner from '@/components/features/CtaBanner.vue'
import { useServices } from '@/composables/useServices.js'
import { useContact } from '@/composables/useContact.js'
import { useSeo, localBusinessJsonLd, servicesCatalogJsonLd } from '@/composables/useSeo.js'

useSeo({
  title: 'Carrito Hot Dog, Candy Bar, Photocall y más en Salamanca',
  description: 'Dulzia Salamanca Eventos: alquiler de carrito de hot dog, candy bar, fuente de chocolate, photocall, glitter bar y mucho más para bodas, cumpleaños y eventos en Salamanca.',
  path: '/',
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

// El JSON-LD del negocio lleva el teléfono y el email, que llegan con los datos
// de contacto: se reescribe al recibirlos, y si no hay nada configurado se
// queda con los de siempre.
const { email, phone } = useContact()
watch([email, phone], () => {
  useSeo({ jsonLd: localBusinessJsonLd({ email: email.value, phone: phone.value }) })
}, { immediate: true })
</script>
