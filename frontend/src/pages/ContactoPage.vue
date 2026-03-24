<template>
  <main>
    <section class="hero-small">
      <div class="container">
        <span class="hero-small__pre">Hablemos</span>
        <h1 class="hero-small__title">Contacta con nosotros</h1>
        <p class="hero-small__sub">
          Cuéntanos tu evento y te preparamos un presupuesto personalizado
          sin compromiso. ¡Estamos encantados de ayudarte!
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="contact-layout">
          <!-- Sidebar info -->
          <aside class="contact-info animate-fadeInUp">
            <h2 class="contact-info__title">Información de contacto</h2>

            <div class="contact-info__items">
              <a href="tel:+34629991659" class="contact-info__item">
                <div class="contact-info__icon">📞</div>
                <div>
                  <strong>Teléfono / WhatsApp</strong>
                  <span>+34 629 991 659</span>
                </div>
              </a>
              <a href="mailto:info@dulziasalamancaeventos.com" class="contact-info__item">
                <div class="contact-info__icon">✉️</div>
                <div>
                  <strong>Email</strong>
                  <span>info@dulziasalamancaeventos.com</span>
                </div>
              </a>
              <div class="contact-info__item">
                <div class="contact-info__icon">📍</div>
                <div>
                  <strong>Dirección</strong>
                  <span>C. Martín Alonso Pedraz, 14<br>Salamanca</span>
                </div>
              </div>
              <div class="contact-info__item">
                <div class="contact-info__icon">🕐</div>
                <div>
                  <strong>Horario de atención</strong>
                  <span>Lunes a viernes, 7:00–18:00</span>
                </div>
              </div>
            </div>

            <div class="contact-info__social">
              <p>Síguenos en redes</p>
              <div class="contact-info__social-links">
                <a href="https://www.instagram.com/dulziasala" target="_blank" rel="noopener">
                  Instagram
                </a>
                <a href="https://www.tiktok.com/@dulziasalamancaeventos" target="_blank" rel="noopener">
                  TikTok
                </a>
                <a href="https://wa.me/34629991659" target="_blank" rel="noopener">
                  WhatsApp
                </a>
              </div>
            </div>
          </aside>

          <!-- Form -->
          <div class="contact-form-wrapper">
            <h2 class="contact-form-wrapper__title">Envíanos un mensaje</h2>
            <p class="contact-form-wrapper__sub">
              Respondemos en menos de 24 horas en días laborables.
            </p>
            <ContactForm />
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import ContactForm from '@/components/features/ContactForm.vue'

import { useSeo } from '@/composables/useSeo.js'
useSeo({
  title: 'Contacto — Pide tu presupuesto gratis',
  description: 'Contacta con Dulzia Salamanca Eventos para pedir presupuesto sin compromiso. Respondemos en menos de 24 horas. Tel: +34 629 991 659.',
  path: '/contacto',
})
</script>

<style lang="scss" scoped>
.hero-small {
  background: linear-gradient(135deg, $color-navy 0%, $color-navy-light 100%);
  padding-block: $space-16;
  text-align: center;

  &__pre {
    display: inline-block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-teal;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-5xl;
    font-weight: 900;
    color: $color-white;
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: rgba($color-white, 0.75);
    max-width: 560px;
    margin-inline: auto;
    line-height: 1.7;
  }
}

.contact-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: $space-10;

  @include respond-to(lg) { grid-template-columns: 380px 1fr; gap: $space-12; }
}

.contact-info {
  background: $color-navy;
  border-radius: $radius-2xl;
  padding: $space-10 $space-8;
  color: $color-white;
  height: fit-content;

  &__title {
    font-size: $text-xl;
    font-weight: 700;
    color: $color-white;
    margin-bottom: $space-8;
  }

  &__items {
    display: flex;
    flex-direction: column;
    gap: $space-6;
    margin-bottom: $space-8;
  }

  &__item {
    display: flex;
    gap: $space-4;
    align-items: flex-start;
    text-decoration: none;
    color: inherit;
    transition: opacity $transition-fast;

    &:hover { opacity: 0.8; }

    strong {
      display: block;
      font-size: $text-xs;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: $color-teal;
      margin-bottom: $space-1;
    }

    span {
      font-size: $text-sm;
      color: rgba($color-white, 0.8);
      line-height: 1.6;
    }
  }

  &__icon {
    @include flex-center;
    width: 40px;
    height: 40px;
    border-radius: $radius-md;
    background: rgba($color-teal, 0.15);
    font-size: $text-lg;
    flex-shrink: 0;
  }

  &__social {
    border-top: 1px solid rgba($color-white, 0.1);
    padding-top: $space-6;

    p {
      font-size: $text-sm;
      color: rgba($color-white, 0.5);
      margin-bottom: $space-4;
    }
  }

  &__social-links {
    display: flex;
    flex-wrap: wrap;
    gap: $space-2;

    a {
      padding: $space-2 $space-4;
      border-radius: $radius-full;
      border: 1px solid rgba($color-white, 0.2);
      font-size: $text-sm;
      font-weight: 600;
      color: rgba($color-white, 0.8);
      text-decoration: none;
      transition: all $transition-fast;

      &:hover {
        background: $color-teal;
        border-color: $color-teal;
        color: $color-white;
      }
    }
  }
}

.contact-form-wrapper {
  &__title {
    font-size: $text-2xl;
    font-weight: 900;
    color: $color-navy;
    margin-bottom: $space-2;
  }

  &__sub {
    font-size: $text-sm;
    color: $color-text-muted;
    margin-bottom: $space-8;
  }
}
</style>
