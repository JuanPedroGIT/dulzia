<template>
  <component
    :is="tag"
    :href="href"
    :to="to"
    :type="tag === 'button' ? type : undefined"
    :disabled="disabled || loading"
    class="btn"
    :class="[`btn--${variant}`, `btn--${size}`, { 'btn--loading': loading }]"
  >
    <span v-if="loading" class="btn__spinner" aria-hidden="true" />
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | light | outline | ghost
  size:    { type: String, default: 'md' },       // sm | md | lg
  type:    { type: String, default: 'button' },
  href:    { type: String, default: null },
  to:      { type: [String, Object], default: null },
  loading: { type: Boolean, default: false },
  disabled:{ type: Boolean, default: false },
})

const tag = computed(() => {
  if (props.to) return 'router-link'
  if (props.href) return 'a'
  return 'button'
})
</script>

<style lang="scss" scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: $space-2;
  font-family: $font-body;
  font-weight: 600;
  border-radius: $radius-full;
  // El botón puede ser <a>, <router-link> o <button> según el uso; se fija el
  // dedo explícitamente para que el cursor no dependa del elemento que salga.
  cursor: pointer;
  transition: all $transition-base;
  white-space: nowrap;
  text-decoration: none;

  &:disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }

  // Sizes
  &--sm { font-size: $text-sm; padding: $space-2 $space-5; }
  &--md { font-size: $text-base; padding: $space-3 $space-8; }
  &--lg { font-size: $text-lg; padding: $space-4 $space-10; }

  // Variants
  // Botón de conversión. Va en fucsia oscuro y no en fucsia puro porque el texto
  // blanco encima necesita 4,5:1 para cumplir AA: con #ff00c1 se queda en 3,5:1.
  &--primary {
    background: $color-fucsia-dark;
    color: $color-white;
    box-shadow: 0 4px 16px rgba($color-fucsia-dark, 0.3);

    &:hover:not(:disabled) {
      background: $color-fucsia;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($color-fucsia-dark, 0.4);
    }
  }

  // Invertido: para botones que van encima de un fondo de marca (el bloque CTA,
  // que ahora es fucsia). Blanco sólido con el texto en fucsia oscuro → 4,50:1.
  &--light {
    background: $color-white;
    color: $color-fucsia-dark;

    &:hover:not(:disabled) {
      background: $color-cream;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($color-white, 0.25);
    }
  }

  &--secondary {
    @include gradient-pink;
    color: $color-white;
    box-shadow: 0 4px 16px rgba($color-pink, 0.3);

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($color-pink, 0.4);
    }
  }

  &--outline {
    background: transparent;
    color: $color-green-dark;
    border: 2px solid $color-green-dark;

    &:hover:not(:disabled) {
      background: $color-green-dark;
      color: $color-white;
    }
  }

  &--ghost {
    background: transparent;
    color: $color-green-dark;
    border: 2px solid transparent;

    &:hover:not(:disabled) {
      background: rgba($color-green-dark, 0.06);
    }
  }

  &--loading { pointer-events: none; }
}

.btn__spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;

  @keyframes spin { to { transform: rotate(360deg); } }
}
</style>
