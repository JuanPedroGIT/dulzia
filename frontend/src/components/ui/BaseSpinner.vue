<template>
  <span class="spinner" :style="{ width: size, height: size }" role="status" aria-label="Cargando..." />
</template>

<script setup>
defineProps({ size: { type: String, default: '24px' } })
</script>

<style lang="scss" scoped>
.spinner {
  display: inline-block;
  border: 3px solid rgba($color-teal, 0.2);
  border-top-color: $color-teal;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;

  @keyframes spin { to { transform: rotate(360deg); } }
}
</style>
