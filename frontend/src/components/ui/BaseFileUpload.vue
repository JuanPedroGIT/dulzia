<template>
  <div class="upload">
    <label v-if="label" class="upload__label">{{ label }}</label>
    
    <div
      class="upload__dropzone"
      :class="{ 
        'upload__dropzone--active': isDragging,
        'upload__dropzone--has-file': previewUrl || currentImage 
      }"
      @dragover.prevent="onDragOver"
      @dragleave.prevent="onDragLeave"
      @drop.prevent="onDrop"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        class="sr-only"
        accept="image/*"
        @change="onFileChange"
      />

      <div v-if="!previewUrl && !currentImage" class="upload__placeholder">
        <div class="upload__icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <div class="upload__text">
          <span class="upload__highlight">Haz clic para subir</span> o arrastra y suelta
        </div>
        <p class="upload__info">PNG, JPG, WEBP o GIF (máx. 5MB)</p>
      </div>

      <div v-else class="upload__preview">
        <img :src="previewUrl || currentImage" alt="Vista previa" class="upload__img" />
        <div class="upload__overlay">
          <button type="button" class="upload__remove" @click.stop="removeFile">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <div class="upload__overlay-text">Cambiar imagen</div>
        </div>
      </div>
    </div>
    
    <span v-if="error" class="upload__error">{{ error }}</span>

    <Teleport to="body">
      <ImageCropperModal
        v-if="cropper.show"
        :show="true"
        :image-src="cropper.src"
        @confirm="onCropConfirm"
        @cancel="closeCropper"
      />
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue'
import ImageCropperModal from './ImageCropperModal.vue'

const props = defineProps({
  modelValue:   { type: [File, String], default: null },
  label:        { type: String, default: null },
  currentImage: { type: String, default: null },
  error:        { type: String, default: null },
})

const emit = defineEmits(['update:modelValue', 'change'])

const fileInput = ref(null)
const isDragging = ref(false)
const previewUrl = ref(null)

const cropper = ref({ show: false, src: '' })
let originalFileName = 'image.jpg'

function triggerFileInput() {
  fileInput.value.click()
}

function onFileChange(e) {
  const file = e.target.files[0]
  handleFile(file)
}

function onDragOver() {
  isDragging.value = true
}

function onDragLeave() {
  isDragging.value = false
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  handleFile(file)
}

function handleFile(file) {
  if (!file || !file.type.startsWith('image/')) return

  originalFileName = file.name || 'image.jpg'
  
  if (cropper.value.src) {
    URL.revokeObjectURL(cropper.value.src)
  }

  cropper.value.src = URL.createObjectURL(file)
  cropper.value.show = true
}

function onCropConfirm(blob) {
  const croppedFile = new File([blob], originalFileName, { type: 'image/jpeg' })

  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }

  previewUrl.value = URL.createObjectURL(croppedFile)
  emit('update:modelValue', croppedFile)
  emit('change', croppedFile)
  
  closeCropper()
}

function closeCropper() {
  cropper.value.show = false
  if (cropper.value.src) {
    URL.revokeObjectURL(cropper.value.src)
    cropper.value.src = ''
  }
}

function removeFile() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }
  previewUrl.value = null
  fileInput.value.value = ''
  emit('update:modelValue', null)
  emit('change', null)
}

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  if (cropper.value.src) URL.revokeObjectURL(cropper.value.src)
})
</script>

<style lang="scss" scoped>
@use "@/styles/mixins" as *;

.upload {
  display: flex;
  flex-direction: column;
  gap: $space-2;

  &__label {
    font-size: $text-sm;
    font-weight: 700;
    color: $color-navy;
  }

  &__dropzone {
    position: relative;
    border: 2px dashed $color-border;
    border-radius: $radius-lg;
    background: white;
    padding: $space-8;
    text-align: center;
    cursor: pointer;
    transition: all $transition-base;
    overflow: hidden;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      border-color: $color-teal;
      background: $color-teal-light;
    }

    &--active {
      border-color: $color-teal;
      background: $color-teal-light;
      transform: scale(1.01);
    }

    &--has-file {
      padding: 0;
      border-style: solid;
    }
  }

  &__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: $space-3;
  }

  &__icon {
    color: $color-text-muted;
    background: $color-bg-alt;
    padding: $space-4;
    border-radius: $radius-full;
    transition: all $transition-base;

    .upload__dropzone:hover & {
      background: white;
      color: $color-teal;
    }
  }

  &__text {
    font-size: $text-sm;
    color: $color-text;
  }

  &__highlight {
    color: $color-teal;
    font-weight: 700;
  }

  &__info {
    font-size: $text-xs;
    color: $color-text-muted;
  }

  &__preview {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
  }

  &__img {
    width: 100%;
    max-height: 250px;
    object-fit: contain;
    display: block;
    background: #f0f0f0;
  }

  &__overlay {
    position: absolute;
    inset: 0;
    background: rgba($color-navy, 0.4);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity $transition-base;
    color: white;

    .upload__dropzone:hover & {
      opacity: 1;
    }
  }

  &__overlay-text {
    font-weight: 700;
    font-size: $text-sm;
    margin-top: $space-2;
  }

  &__remove {
    position: absolute;
    top: $space-3;
    right: $space-3;
    background: $color-error;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: $radius-full;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform $transition-fast;
    z-index: 10;

    &:hover {
      transform: scale(1.1);
    }
  }

  &__error {
    font-size: $text-xs;
    color: $color-error;
  }
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
