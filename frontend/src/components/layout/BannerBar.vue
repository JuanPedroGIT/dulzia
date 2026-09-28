<template>
  <div v-if="visible" ref="bar" class="banner-bar" role="region" aria-label="Aviso">
    <div class="banner-bar__inner container">
      <span class="banner-bar__icon" aria-hidden="true">📣</span>
      <div class="banner-bar__text">
        <p class="banner-bar__title">{{ banner.title }}</p>
        <p class="banner-bar__description">{{ banner.description }}</p>
      </div>
      <button class="banner-bar__close" type="button" aria-label="Cerrar aviso" @click="close">
        ✕
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useBanner } from '@/composables/useBanner.js'

const { banner, fetchBanner } = useBanner()

// Un aviso cerrado no reaparece durante la sesión (aunque se navegue o se
// recargue): su id queda en sessionStorage y vuelve a salir en la próxima visita.
const STORAGE_KEY = 'banner-cerrado'
const closedId = ref(sessionStorage.getItem(STORAGE_KEY))

const visible = computed(() => banner.value !== null && closedId.value !== banner.value.id)

const bar = ref(null)
let observer = null

function setBannerHeight(px) {
  document.documentElement.style.setProperty('--banner-h', `${px}px`)
}

// El nav fijo y su espaciador leen --banner-h: la barra mide su altura (cambia
// al partir la descripción o girar el móvil) y la publica ahí. Al ocultarse
// la variable vuelve a 0 y el nav sube a su sitio.
function attachObserver() {
  detachObserver()
  if (!bar.value) return
  setBannerHeight(bar.value.offsetHeight)
  observer = new ResizeObserver(() => setBannerHeight(bar.value.offsetHeight))
  observer.observe(bar.value)
}

function detachObserver() {
  if (observer) {
    observer.disconnect()
    observer = null
  }
}

function close() {
  closedId.value = banner.value.id
  sessionStorage.setItem(STORAGE_KEY, banner.value.id)
}

watch(visible, (isVisible) => {
  // nextTick: la barra se ha montado (o desmontado) antes de medir.
  nextTick(() => {
    if (isVisible) attachObserver()
    else {
      detachObserver()
      setBannerHeight(0)
    }
  })
})

onMounted(() => {
  if (visible.value) attachObserver()
  else setBannerHeight(0)
  // El HTML horneado trae --banner-h a la altura de la barra (el prerender la
  // midió): si la barra no va a verse —banner cerrado en esta sesión, o sin
  // banner—, hay que devolverla a 0 o el nav queda 64 px más abajo con un
  // hueco en blanco encima.
})

onUnmounted(() => {
  detachObserver()
  setBannerHeight(0)
})
</script>

<style lang="scss" scoped>
.banner-bar {
  // Fija, por encima del nav (100): "arriba del todo sobre la web", en todas
  // las páginas. Fucsia oscuro y no puro porque el texto blanco encima necesita
  // 4,5:1 (el mismo criterio del banner de presupuesto).
  position: fixed;
  top: 0;
  inset-inline: 0;
  z-index: 101;
  background: $color-fucsia-dark;
  color: $color-white;
  padding-block: $space-3;

  &__inner {
    display: flex;
    align-items: center;
    gap: $space-3;
  }

  &__icon {
    flex-shrink: 0;
    font-size: $text-lg;
  }

  &__text {
    flex: 1;
    min-width: 0;
  }

  &__title {
    font-size: $text-sm;
    font-weight: 700;
    margin: 0;
    // Una sola línea: si no cabe se corta con puntos (el texto completo vive
    // en la descripción).
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__description {
    font-size: $text-xs;
    margin: 0;
    // Blanco puro, sin rebajar la opacidad: sobre el fucsia oscuro cualquier
    // transparencia lo baja por debajo de AA.
    color: $color-white;
    line-height: 1.5;

    // En pantallas muy estrechas el texto no se parte: se corta con puntos.
    // Así la barra mide una sola línea de texto a cualquier ancho y coincide con
    // la altura horneada en el prerender (64 px): sin esto, a 320 px la barra
    // crecía a 82 px y el nav tapaba la segunda línea hasta que montaba el JS.
    @media (max-width: 400px) {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }

  &__close {
    @include flex-center;
    flex-shrink: 0;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: $radius-full;
    background: rgba($color-white, 0.18);
    color: $color-white;
    font-size: $text-sm;
    cursor: pointer;
    transition: background $transition-fast;

    &:hover { background: rgba($color-white, 0.32); }
  }
}
</style>
