<template>
  <div class="field" :class="{ 'field--error': error }">
    <label v-if="label" :for="id" class="field__label">{{ label }}</label>
    <textarea
      :id="id"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :rows="rows"
      class="field__textarea"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <span v-if="error" class="field__error">{{ error }}</span>
  </div>
</template>

<script setup>
defineProps({
  id:         { type: String, required: true },
  label:      { type: String, default: null },
  modelValue: { type: String, default: '' },
  placeholder:{ type: String, default: '' },
  required:   { type: Boolean, default: false },
  rows:       { type: Number, default: 5 },
  error:      { type: String, default: null },
})
defineEmits(['update:modelValue'])
</script>

<style lang="scss" scoped>
.field {
  display: flex;
  flex-direction: column;
  gap: $space-2;

  &__label {
    font-size: $text-sm;
    font-weight: 600;
    color: $color-navy;
  }

  &__textarea {
    width: 100%;
    padding: $space-3 $space-4;
    border: 2px solid $color-border;
    border-radius: $radius-md;
    background: $color-white;
    color: $color-text;
    font-size: $text-base;
    line-height: 1.6;
    resize: vertical;
    transition: border-color $transition-fast, box-shadow $transition-fast;
    outline: none;

    &::placeholder { color: $color-text-muted; }

    &:focus {
      border-color: $color-teal;
      box-shadow: 0 0 0 3px rgba($color-teal, 0.12);
    }
  }

  &__error { font-size: $text-sm; color: $color-error; }

  &--error .field__textarea {
    border-color: $color-error;
  }
}
</style>
