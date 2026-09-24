<template>
  <div v-if="show" class="cropper-modal" @click.self="cancel">
    <div class="cropper-modal__content">
      <header class="cropper-modal__header">
        <div>
          <h3>Editar imagen</h3>
          <p>Arrastra para mover · Rueda para zoom</p>
        </div>
        <button type="button" class="btn-close" @click="cancel">✕</button>
      </header>

      <div
        class="cropper-modal__body"
        ref="containerRef"
        @mousedown="onMouseDown"
        @mousemove="onMouseMove"
        @mouseup="onMouseUp"
        @mouseleave="onMouseUp"
        @wheel.prevent="onWheel"
        @touchstart.prevent="onTouchStart"
        @touchmove.prevent="onTouchMove"
        @touchend="onTouchEnd"
      >
        <img
          v-if="imageSrc"
          ref="imgRef"
          :src="imageSrc"
          class="cropper-img"
          :style="imgStyle"
          draggable="false"
          @load="onImageLoad"
        />
      </div>

      <div class="cropper-modal__controls">
        <div class="slider-group">
          <label>Zoom</label>
          <input type="range" min="0.1" max="3" step="0.01" v-model.number="scale" />
          <button type="button" class="btn-reset" @click="resetTransform" title="Restablecer">↺</button>
        </div>
      </div>

      <footer class="cropper-modal__footer">
        <button type="button" class="btn-cancel" @click="cancel">Cancelar</button>
        <button type="button" class="btn-confirm" :disabled="exporting" @click="confirm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          {{ exporting ? 'Preparando…' : 'Confirmar recorte' }}
        </button>
      </footer>
    </div>

    <!-- Hidden canvases for export only -->
    <canvas ref="exportCanvas" style="display:none" />
    <canvas ref="thumbCanvas" style="display:none" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  show:     { type: Boolean, default: false },
  imageSrc: { type: String, required: true },
})
const emit = defineEmits(['confirm', 'cancel'])

// Anchos que se suben: la grande es la que se abre en el carrusel, la miniatura
// la que sirven las tarjetas y las listas (mucho más ligera).
const FULL_WIDTH    = 1600
const FULL_QUALITY  = 0.85
const THUMB_WIDTH   = 640
const THUMB_QUALITY = 0.82

const containerRef  = ref(null)
const imgRef        = ref(null)
const exportCanvas  = ref(null)
const thumbCanvas   = ref(null)

const scale  = ref(1)
const offset = ref({ x: 0, y: 0 })
const exporting = ref(false)
let isDragging    = false
let lastPos       = { x: 0, y: 0 }
let lastTouchDist = null
let naturalW = 0
let naturalH = 0

const imgStyle = computed(() => ({
  transform: `translate(${offset.value.x}px, ${offset.value.y}px) scale(${scale.value})`,
  transformOrigin: 'center center',
}))

function onImageLoad(e) {
  naturalW = e.target.naturalWidth
  naturalH = e.target.naturalHeight
  resetTransform()
}

function resetTransform() {
  offset.value = { x: 0, y: 0 }
  if (!containerRef.value || !naturalW) { scale.value = 1; return }
  const cw = containerRef.value.clientWidth  || 800
  const ch = containerRef.value.clientHeight || 600
  scale.value = Math.max(cw / naturalW, ch / naturalH)
}

// ─── Mouse ──────────────────────────────────────────────────
function onMouseDown(e) {
  isDragging = true
  lastPos = { x: e.clientX, y: e.clientY }
}
function onMouseMove(e) {
  if (!isDragging) return
  offset.value.x += e.clientX - lastPos.x
  offset.value.y += e.clientY - lastPos.y
  lastPos = { x: e.clientX, y: e.clientY }
}
function onMouseUp() { isDragging = false }
function onWheel(e) {
  const delta = e.deltaY < 0 ? 0.1 : -0.1
  scale.value = Math.max(0.1, Math.min(3, scale.value + delta))
}

// ─── Touch ──────────────────────────────────────────────────
function onTouchStart(e) {
  if (e.touches.length === 1) {
    isDragging = true
    lastPos = { x: e.touches[0].clientX, y: e.touches[0].clientY }
  } else if (e.touches.length === 2) {
    lastTouchDist = getTouchDist(e.touches)
  }
}
function onTouchMove(e) {
  if (e.touches.length === 1 && isDragging) {
    offset.value.x += e.touches[0].clientX - lastPos.x
    offset.value.y += e.touches[0].clientY - lastPos.y
    lastPos = { x: e.touches[0].clientX, y: e.touches[0].clientY }
  } else if (e.touches.length === 2) {
    const dist = getTouchDist(e.touches)
    if (lastTouchDist) {
      scale.value = Math.max(0.1, Math.min(3, scale.value * (dist / lastTouchDist)))
    }
    lastTouchDist = dist
  }
}
function onTouchEnd() { isDragging = false; lastTouchDist = null }
function getTouchDist(t) {
  const dx = t[0].clientX - t[1].clientX
  const dy = t[0].clientY - t[1].clientY
  return Math.sqrt(dx * dx + dy * dy)
}

// ─── Export ─────────────────────────────────────────────────
function cancel() { emit('cancel') }

/**
 * Dibuja el recorte actual en el canvas dado, escalado al ancho pedido.
 *
 * El factor se limita a la resolución real del recorte (`cw / scale` píxeles de
 * la imagen original): una foto pequeña se sube a su tamaño natural como mucho,
 * nunca ampliada. Se dibuja desde la imagen original en cada pasada, así la
 * miniatura no es una reducción de la grande.
 */
function exportAt(canvas, targetWidth, quality) {
  const cw = containerRef.value.clientWidth
  const ch = containerRef.value.clientHeight
  const factor = Math.min(targetWidth / cw, 1 / scale.value)

  canvas.width  = Math.round(cw * factor)
  canvas.height = Math.round(ch * factor)

  const ctx = canvas.getContext('2d')

  // El canvas arranca transparente y el JPEG no tiene alfa: al alejar el zoom
  // quedan franjas sin pintar que saldrían negras.
  ctx.fillStyle = '#fff'
  ctx.fillRect(0, 0, canvas.width, canvas.height)

  const iw = naturalW * scale.value
  const ih = naturalH * scale.value
  const ix = (cw - iw) / 2 + offset.value.x
  const iy = (ch - ih) / 2 + offset.value.y

  ctx.drawImage(imgRef.value, ix * factor, iy * factor, iw * factor, ih * factor)

  return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality))
}

async function confirm() {
  if (!containerRef.value || !imgRef.value || exporting.value) return

  // Las dos exportaciones son asíncronas: el botón se bloquea mientras tanto.
  exporting.value = true
  try {
    const full      = await exportAt(exportCanvas.value, FULL_WIDTH, FULL_QUALITY)
    const thumbnail = await exportAt(thumbCanvas.value, THUMB_WIDTH, THUMB_QUALITY)

    emit('confirm', { full, thumbnail })
  } finally {
    exporting.value = false
  }
}
</script>

<style lang="scss" scoped>
@use "@/styles/mixins" as *;

.cropper-modal {
  position: fixed;
  inset: 0;
  background: rgba($color-green-deep, 0.62);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: $space-4;

  &__content {
    background: white;
    width: 100%;
    max-width: 860px;
    border-radius: $radius-xl;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: $shadow-xl;
    animation: scaleIn 0.25s ease-out;
  }

  &__header {
    padding: $space-5 $space-8;
    border-bottom: 1px solid $color-bg-alt;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;

    h3 { font-size: $text-xl; color: $color-green-dark; margin-bottom: $space-1; }
    p  { font-size: $text-xs; color: $color-text-muted; margin: 0; }
  }

  &__body {
    background: #111;
    position: relative;
    overflow: hidden;
    cursor: grab;
    display: flex;
    align-items: center;
    justify-content: center;
    // El recorte siempre en 4:3 apaisado, sin depender de la pantalla
    aspect-ratio: 4 / 3;
    height: auto;
    width: min(100%, calc(min(60vh, 420px) * 4 / 3));
    width: min(100%, calc(min(60dvh, 420px) * 4 / 3));
    align-self: center;
    &:active { cursor: grabbing; }
  }

  &__controls {
    padding: $space-3 $space-8;
    background: $color-bg;
    border-top: 1px solid $color-border;

    .slider-group {
      display: flex;
      align-items: center;
      gap: $space-3;

      label {
        font-size: $text-xs;
        font-weight: 700;
        color: $color-text-muted;
        min-width: 40px;
      }
      input[type="range"] {
        flex: 1;
        accent-color: $color-mint-mid;
      }
    }
  }

  &__footer {
    padding: $space-5 $space-8;
    background: $color-bg;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: $space-3;
    border-top: 1px solid $color-border;
  }
}

.cropper-img {
  max-width: none;
  max-height: none;
  display: block;
  pointer-events: none;
  user-select: none;
  transition: transform 0s; // No transition so drag feels instant
}

.btn-close {
  background: none;
  border: none;
  font-size: $text-lg;
  color: $color-text-muted;
  cursor: pointer;
  padding: $space-1;
  &:hover { color: $color-green-dark; }
}

.btn-reset {
  background: none;
  border: 1.5px solid $color-border;
  border-radius: $radius-md;
  padding: $space-1 $space-3;
  font-size: $text-base;
  cursor: pointer;
  color: $color-text-muted;
  &:hover { background: $color-bg-alt; }
}

.btn-cancel {
  padding: $space-3 $space-6;
  background: white;
  border: 1.5px solid $color-border;
  border-radius: $radius-md;
  font-weight: 700;
  font-size: $text-sm;
  color: $color-text-muted;
  transition: all 0.15s;
  &:hover { background: $color-bg-alt; color: $color-green-dark; }
}

.btn-confirm {
  padding: $space-3 $space-8;
  background: $color-mint-mid;
  color: white;
  border: none;
  border-radius: $radius-md;
  font-weight: 800;
  font-size: $text-sm;
  display: flex;
  align-items: center;
  gap: $space-2;
  box-shadow: 0 4px 12px rgba($color-mint-mid, 0.3);
  transition: all 0.15s;
  &:hover { background: $color-green-dark; transform: translateY(-1px); }
  &:disabled {
    opacity: 0.7;
    cursor: wait;
    transform: none;
    background: $color-mint-mid;
  }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.92); }
  to   { opacity: 1; transform: scale(1); }
}
</style>
