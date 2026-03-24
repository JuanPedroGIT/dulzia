<template>
  <Transition name="banner">
    <div v-if="visible" class="cookie-banner" role="dialog" aria-label="Aviso de cookies">
      <div class="cookie-banner__inner">
        <div class="cookie-banner__text">
          <span class="cookie-banner__icon">🍪</span>
          <p>
            Usamos cookies técnicas estrictamente necesarias para el funcionamiento del sitio.
            No utilizamos cookies de seguimiento ni publicitarias.
            <router-link to="/politica-cookies" @click="dismiss">Más información</router-link>
          </p>
        </div>
        <div class="cookie-banner__actions">
          <BaseButton variant="outline" size="sm" @click="dismiss">Solo necesarias</BaseButton>
          <BaseButton variant="primary" size="sm" @click="accept">Aceptar</BaseButton>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const visible = ref(false)

onMounted(() => {
  if (!localStorage.getItem('cookie_consent')) {
    visible.value = true
  }
})

function accept() {
  localStorage.setItem('cookie_consent', 'accepted')
  visible.value = false
}

function dismiss() {
  localStorage.setItem('cookie_consent', 'necessary')
  visible.value = false
}
</script>

<style lang="scss" scoped>
.cookie-banner {
  position: fixed;
  bottom: $space-6;
  left: 50%;
  transform: translateX(-50%);
  z-index: 200;
  width: calc(100% - #{$space-8});
  max-width: 860px;

  &__inner {
    display: flex;
    flex-direction: column;
    gap: $space-4;
    background: $color-navy;
    border-radius: $radius-xl;
    padding: $space-5 $space-6;
    box-shadow: $shadow-xl;
    border: 1px solid rgba($color-white, 0.08);

    @include respond-to(md) {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  &__text {
    display: flex;
    align-items: flex-start;
    gap: $space-3;
    flex: 1;

    p {
      font-size: $text-sm;
      color: rgba($color-white, 0.8);
      line-height: 1.6;
      margin: 0;

      a {
        color: $color-teal;
        text-decoration: underline;
        &:hover { color: lighten($color-teal, 10%); }
      }
    }
  }

  &__icon { font-size: $text-xl; flex-shrink: 0; margin-top: 1px; }

  &__actions {
    display: flex;
    gap: $space-3;
    flex-shrink: 0;
  }
}

.banner-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.banner-leave-active { transition: all 0.25s ease; }
.banner-enter-from  { opacity: 0; transform: translateX(-50%) translateY(20px); }
.banner-leave-to    { opacity: 0; transform: translateX(-50%) translateY(12px); }
</style>
