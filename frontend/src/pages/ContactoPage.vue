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
              <a :href="telHref" class="contact-info__item">
                <div class="contact-info__icon">📞</div>
                <div>
                  <strong>Teléfono / WhatsApp</strong>
                  <span>{{ phone }}</span>
                </div>
              </a>
              <a :href="`mailto:${email}`" class="contact-info__item">
                <div class="contact-info__icon">✉️</div>
                <div>
                  <strong>Email</strong>
                  <span>{{ email }}</span>
                </div>
              </a>
            </div>

            <div class="contact-info__social">
              <p>Síguenos en redes</p>
              <div class="contact-info__social-links">
                <a href="https://www.tiktok.com/@dulziasalamancaeventos" target="_blank" rel="noopener" aria-label="TikTok">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.79 1.53V6.75a4.85 4.85 0 01-1.02-.06z"/></svg>
                </a>
                <a href="https://www.facebook.com/profile.php?id=61569180747614" target="_blank" rel="noopener" aria-label="Facebook">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                <a href="https://www.instagram.com/dulziasala" target="_blank" rel="noopener" aria-label="Instagram">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                <a :href="whatsappHref" target="_blank" rel="noopener" aria-label="WhatsApp">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
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
import { watch } from 'vue'
import ContactForm from '@/components/features/ContactForm.vue'
import { useContact } from '@/composables/useContact.js'
import { useSeo } from '@/composables/useSeo.js'

const { email, phone, telHref, whatsappHref } = useContact()

// La descripción lleva el teléfono: se reescribe si el panel lo cambia.
function applySeo() {
  useSeo({
    title: 'Contacto — Pide tu presupuesto gratis',
    description: `Contacta con Dulzia Salamanca Eventos para pedir presupuesto sin compromiso. Respondemos en menos de 24 horas. Tel: ${phone.value}.`,
    path: '/contacto',
  })
}

applySeo()
watch(phone, applySeo)
</script>

<style lang="scss" scoped>
.hero-small {
  background: linear-gradient(160deg, $color-mint-light 0%, $color-mint 60%, $color-mint-mid 140%);
  padding-block: $space-16;
  text-align: center;

  &__pre {
    display: inline-block;
    font-size: $text-sm;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: $color-pink;
    margin-bottom: $space-3;
  }

  &__title {
    font-size: $text-5xl;
    font-weight: 900;
    color: $color-green-dark;
    margin-bottom: $space-4;
  }

  &__sub {
    font-size: $text-lg;
    color: $color-text-muted;
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
  @include gradient-mint;
  border-radius: $radius-2xl;
  padding: $space-10 $space-8;
  color: $color-green-dark;
  height: fit-content;

  &__title {
    font-size: $text-xl;
    font-weight: 700;
    color: $color-green-dark;
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

    // El email es una palabra de 233px que no parte: con el icono y el relleno
    // del panel daba un ancho mínimo de 353px, que en un móvil de 360 no cabe,
    // estiraba la columna del grid (los campos del formulario incluidos) y
    // sacaba la página 9px de la pantalla. Con `anywhere` el texto puede partirse
    // y el panel encoge hasta el ancho real de la pantalla.
    strong, span { overflow-wrap: anywhere; }

    strong {
      display: block;
      font-size: $text-xs;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: $color-pink;
      margin-bottom: $space-1;
    }

    span {
      font-size: $text-sm;
      color: rgba($color-green-dark, 0.8);
      line-height: 1.6;
    }
  }

  &__icon {
    @include flex-center;
    width: 40px;
    height: 40px;
    border-radius: $radius-md;
    background: rgba($color-white, 0.55);
    font-size: $text-lg;
    flex-shrink: 0;
  }

  &__social {
    border-top: 1px solid rgba($color-green-dark, 0.15);
    padding-top: $space-6;

    p {
      font-size: $text-sm;
      color: rgba($color-green-dark, 0.7);
      margin-bottom: $space-4;
    }
  }

  &__social-links {
    display: flex;
    gap: $space-3;

    a {
      @include flex-center;
      width: 40px;
      height: 40px;
      border-radius: $radius-full;
      background: rgba($color-white, 0.55);
      color: $color-green-dark;
      transition: all $transition-fast;

      &:hover {
        background: $color-pink;
        color: $color-white;
        transform: translateY(-2px);
      }
    }
  }
}

.contact-form-wrapper {
  &__title {
    font-size: $text-2xl;
    font-weight: 900;
    color: $color-green-dark;
    margin-bottom: $space-2;
  }

  &__sub {
    font-size: $text-sm;
    color: $color-text-muted;
    margin-bottom: $space-8;
  }
}
</style>
