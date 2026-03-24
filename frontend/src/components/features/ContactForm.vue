<template>
  <div class="contact-form">
    <Transition name="fade" mode="out-in">
      <div v-if="isSuccess" class="contact-form__success">
        <div class="contact-form__success-icon">🎉</div>
        <h3>¡Mensaje enviado!</h3>
        <p>Nos pondremos en contacto contigo en menos de 24 horas. ¡Gracias!</p>
      </div>

      <form v-else class="contact-form__form" @submit.prevent="submitForm" novalidate>
        <div class="contact-form__row">
          <BaseInput
            id="name"
            v-model="form.name"
            label="Nombre *"
            placeholder="Tu nombre completo"
            required
            :error="errors.name?.[0]"
          />
          <BaseInput
            id="email"
            v-model="form.email"
            type="email"
            label="Email *"
            placeholder="tu@email.com"
            required
            :error="errors.email?.[0]"
          />
        </div>

        <div class="contact-form__row">
          <BaseInput
            id="phone"
            v-model="form.phone"
            type="tel"
            label="Teléfono"
            placeholder="+34 600 000 000"
          />
          <div class="field">
            <label for="eventType" class="field__label">Tipo de evento</label>
            <select id="eventType" v-model="form.eventType" class="field__select">
              <option value="">Selecciona un tipo...</option>
              <option value="Boda">Boda</option>
              <option value="Cumpleaños">Cumpleaños</option>
              <option value="Comunión">Comunión / Bautizo</option>
              <option value="Corporativo">Evento corporativo</option>
              <option value="Graduación">Graduación</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
        </div>

        <BaseTextarea
          id="message"
          v-model="form.message"
          label="Mensaje *"
          placeholder="Cuéntanos sobre tu evento: fecha aproximada, número de personas, servicios que te interesan..."
          required
          :rows="5"
          :error="errors.message?.[0]"
        />

        <div v-if="serverError" class="contact-form__error">
          {{ serverError }}
        </div>

        <BaseButton
          type="submit"
          variant="primary"
          size="lg"
          :loading="isLoading"
          class="contact-form__submit"
        >
          Enviar mensaje
        </BaseButton>
      </form>
    </Transition>
  </div>
</template>

<script setup>
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseTextarea from '@/components/ui/BaseTextarea.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import { useContactForm } from '@/composables/useContactForm.js'

const { form, errors, isLoading, isSuccess, serverError, submitForm } = useContactForm()
</script>

<style lang="scss" scoped>
.contact-form {
  &__form {
    display: flex;
    flex-direction: column;
    gap: $space-5;
  }

  &__row {
    display: grid;
    grid-template-columns: 1fr;
    gap: $space-5;

    @include respond-to(md) { grid-template-columns: 1fr 1fr; }
  }

  &__submit { align-self: flex-start; }

  &__error {
    padding: $space-3 $space-4;
    background: rgba($color-error, 0.08);
    border: 1px solid rgba($color-error, 0.3);
    border-radius: $radius-md;
    font-size: $text-sm;
    color: $color-error;
  }

  &__success {
    text-align: center;
    padding: $space-12;

    &-icon { font-size: 3rem; margin-bottom: $space-4; }

    h3 {
      font-size: $text-2xl;
      color: $color-navy;
      margin-bottom: $space-2;
    }

    p { color: $color-text-muted; }
  }
}

.field {
  display: flex;
  flex-direction: column;
  gap: $space-2;

  &__label {
    font-size: $text-sm;
    font-weight: 600;
    color: $color-navy;
  }

  &__select {
    width: 100%;
    padding: $space-3 $space-4;
    border: 2px solid $color-border;
    border-radius: $radius-md;
    background: $color-white;
    color: $color-text;
    font-size: $text-base;
    font-family: $font-body;
    outline: none;
    cursor: pointer;
    transition: border-color $transition-fast, box-shadow $transition-fast;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%235a6e7f' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: $space-10;

    &:focus {
      border-color: $color-teal;
      box-shadow: 0 0 0 3px rgba($color-teal, 0.12);
    }
  }
}

.fade-enter-active, .fade-leave-active { transition: opacity $transition-base; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
