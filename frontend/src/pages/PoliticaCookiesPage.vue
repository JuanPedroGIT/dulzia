<template>
  <main>
    <section class="hero-small">
      <div class="container">
        <h1 class="hero-small__title">Política de Cookies</h1>
        <p class="hero-small__sub">Última actualización: {{ lastUpdated }}</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="legal">

          <div class="legal__block">
            <h2>1. ¿Qué son las cookies?</h2>
            <p>
              Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo (ordenador, tablet o móvil)
              cuando visitas un sitio web. Permiten que el sitio recuerde tus acciones y preferencias durante un período
              de tiempo, para que no tengas que volver a introducirlas cada vez que visites el sitio o navegues de una
              página a otra.
            </p>
          </div>

          <div class="legal__block">
            <h2>2. ¿Qué cookies utilizamos?</h2>
            <p>Esta web utiliza únicamente cookies técnicas estrictamente necesarias para su funcionamiento:</p>
            <table class="legal__table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Tipo</th>
                  <th>Finalidad</th>
                  <th>Duración</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>session</code></td>
                  <td>Técnica</td>
                  <td>Mantener la sesión de navegación activa</td>
                  <td>Sesión</td>
                </tr>
                <tr>
                  <td><code>cookie_consent</code></td>
                  <td>Técnica</td>
                  <td>Guardar tu preferencia sobre cookies</td>
                  <td>1 año</td>
                </tr>
              </tbody>
            </table>
            <p>
              Esta web <strong>no utiliza cookies de seguimiento, publicitarias ni de terceros</strong> (Google Analytics,
              Facebook Pixel, etc.). No se recoge ningún dato de navegación con fines comerciales.
            </p>
          </div>

          <div class="legal__block">
            <h2>3. ¿Cómo puedo gestionar las cookies?</h2>
            <p>
              Puedes permitir, bloquear o eliminar las cookies instaladas en tu dispositivo mediante la configuración
              de las opciones de tu navegador. A continuación te indicamos cómo hacerlo en los navegadores más comunes:
            </p>
            <ul>
              <li><strong>Chrome:</strong> Configuración → Privacidad y seguridad → Cookies y otros datos de sitios</li>
              <li><strong>Firefox:</strong> Opciones → Privacidad y seguridad → Cookies y datos del sitio</li>
              <li><strong>Safari:</strong> Preferencias → Privacidad → Gestionar datos de sitios web</li>
              <li><strong>Edge:</strong> Configuración → Privacidad, búsqueda y servicios → Cookies</li>
            </ul>
            <p>
              Ten en cuenta que deshabilitar las cookies técnicas puede afectar al correcto funcionamiento del sitio web.
            </p>
          </div>

          <div class="legal__block">
            <h2>4. Responsable del tratamiento</h2>
            <p>
              <strong>Dulzia Salamanca Eventos</strong><br>
              C. Martín Alonso Pedraz, 14, Salamanca<br>
              Email: <a href="mailto:info@dulziasalamancaeventos.com">info@dulziasalamancaeventos.com</a><br>
              Teléfono: <a href="tel:+34629991659">+34 629 991 659</a>
            </p>
          </div>

          <div class="legal__block">
            <h2>5. Actualizaciones de esta política</h2>
            <p>
              Podemos actualizar esta Política de Cookies para reflejar cambios en las cookies que utilizamos o
              por otras razones operativas, legales o reglamentarias. Te recomendamos revisar esta página
              periódicamente para estar informado sobre nuestro uso de cookies.
            </p>
          </div>

          <div class="legal__back">
            <BaseButton to="/" variant="outline" size="md">← Volver al inicio</BaseButton>
          </div>

        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import BaseButton from '@/components/ui/BaseButton.vue'

import { useSeo } from '@/composables/useSeo.js'
useSeo({ title: 'Política de Cookies', path: '/politica-cookies' })

const lastUpdated = new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
</script>

<style lang="scss" scoped>
.hero-small {
  background: linear-gradient(135deg, $color-navy 0%, $color-navy-light 100%);
  padding-block: $space-12;
  text-align: center;

  &__title {
    font-size: $text-4xl;
    font-weight: 900;
    color: $color-white;
    margin-bottom: $space-2;
  }

  &__sub {
    font-size: $text-sm;
    color: rgba($color-white, 0.55);
  }
}

.legal {
  max-width: 780px;
  margin-inline: auto;

  &__block {
    margin-bottom: $space-10;

    h2 {
      font-size: $text-xl;
      font-weight: 700;
      color: $color-navy;
      margin-bottom: $space-4;
      padding-bottom: $space-3;
      border-bottom: 2px solid $color-bg-alt;
    }

    p {
      font-size: $text-base;
      color: $color-text-muted;
      line-height: 1.8;
      margin-bottom: $space-4;

      strong { color: $color-navy; }

      a {
        color: $color-teal;
        &:hover { text-decoration: underline; }
      }
    }

    ul {
      list-style: disc;
      padding-left: $space-6;
      display: flex;
      flex-direction: column;
      gap: $space-2;
      margin-bottom: $space-4;

      li {
        font-size: $text-base;
        color: $color-text-muted;
        line-height: 1.7;

        strong { color: $color-navy; }
      }
    }
  }

  &__table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: $space-6;
    font-size: $text-sm;
    overflow-x: auto;
    display: block;

    @include respond-to(md) { display: table; }

    th {
      background: $color-navy;
      color: $color-white;
      padding: $space-3 $space-4;
      text-align: left;
      font-weight: 600;
    }

    td {
      padding: $space-3 $space-4;
      border-bottom: 1px solid $color-border;
      color: $color-text-muted;
      vertical-align: top;

      code {
        font-family: monospace;
        background: $color-bg-alt;
        padding: 2px 6px;
        border-radius: $radius-sm;
        font-size: $text-xs;
        color: $color-teal-dark;
      }
    }

    tr:last-child td { border-bottom: none; }
    tr:nth-child(even) td { background: $color-bg; }
  }

  &__back {
    margin-top: $space-12;
    padding-top: $space-8;
    border-top: 1px solid $color-border;
  }
}
</style>
