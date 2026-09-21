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
  variant: { type: String, default: 'primary' }, // primary | secondary | outline | ghost
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
  transition: all $transition-base;
  white-space: nowrap;
  text-decoration: none;

  &:disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }

  // Sizes
  &--sm { font-size: $text-sm; padding: $space-2 $space-5; }
  &--md { font-size: $text-base; padding: $space-3 $space-8; }
  &--lg { font-size: $text-lg; padding: $space-4 $space-10; }

  // Variants
  &--primary {
    background: $color-green-dark;
    color: $color-white;
    box-shadow: 0 4px 16px rgba($color-green-dark, 0.25);

    &:hover:not(:disabled) {
      background: $color-green-deep;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($color-green-dark, 0.35);
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
