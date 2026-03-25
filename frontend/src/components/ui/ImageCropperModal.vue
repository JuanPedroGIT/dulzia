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
        <button type="button" class="btn-confirm" @click="confirm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Confirmar recorte
        </button>
      </footer>
    </div>

    <!-- Hidden canvas for export only -->
    <canvas ref="exportCanvas" style="display:none" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  show:     { type: Boolean, default: false },
  imageSrc: { type: String, required: true },
})
const emit = defineEmits(['confirm', 'cancel'])

const containerRef  = ref(null)
const imgRef        = ref(null)
const exportCanvas  = ref(null)

const scale  = ref(1)
const offset = ref({ x: 0, y: 0 })
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
  const ch = containerRef.value.clientHeight || 400
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

function confirm() {
  const container = containerRef.value
  const image     = imgRef.value
  if (!container || !image) return

  const cw = container.clientWidth
  const ch = container.clientHeight

  // Render the current view to a canvas
  const canvas = exportCanvas.value
  canvas.width  = cw
  canvas.height = ch
  const ctx = canvas.getContext('2d')

  // Calculate image position/size accounting for transform
  const iw = naturalW * scale.value
  const ih = naturalH * scale.value
  const ix = (cw - iw) / 2 + offset.value.x
  const iy = (ch - ih) / 2 + offset.value.y

  ctx.drawImage(image, ix, iy, iw, ih)

  canvas.toBlob((blob) => { emit('confirm', blob) }, 'image/jpeg', 0.92)
}
</script>

<style lang="scss" scoped>
@use "@/styles/mixins" as *;

.cropper-modal {
  position: fixed;
  inset: 0;
  background: rgba($color-navy, 0.88);
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

    h3 { font-size: $text-xl; color: $color-navy; margin-bottom: $space-1; }
    p  { font-size: $text-xs; color: $color-text-muted; margin: 0; }
  }

  &__body {
    background: #111;
    height: 400px;
    position: relative;
    overflow: hidden;
    cursor: grab;
    display: flex;
    align-items: center;
    justify-content: center;
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
        accent-color: $color-teal;
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
  &:hover { color: $color-navy; }
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
  &:hover { background: $color-bg-alt; color: $color-navy; }
}

.btn-confirm {
  padding: $space-3 $space-8;
  background: $color-teal;
  color: white;
  border: none;
  border-radius: $radius-md;
  font-weight: 800;
  font-size: $text-sm;
  display: flex;
  align-items: center;
  gap: $space-2;
  box-shadow: 0 4px 12px rgba($color-teal, 0.3);
  transition: all 0.15s;
  &:hover { background: $color-teal-dark; transform: translateY(-1px); }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.92); }
  to   { opacity: 1; transform: scale(1); }
}
</style>
