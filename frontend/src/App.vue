<template>
  <NavBar />
  <RouterView v-slot="{ Component }">
    <Transition name="page" mode="out-in">
      <component :is="Component" />
    </Transition>
  </RouterView>
  <AppFooter />
  <CookieBanner />
</template>

<script setup>
import NavBar from '@/components/layout/NavBar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import CookieBanner from '@/components/features/CookieBanner.vue'
</script>
