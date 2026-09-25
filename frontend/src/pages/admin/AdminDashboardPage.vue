<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { apiGetMessages } from '@/services/adminService'

const router = useRouter()
const { logout } = useAuth()

// El panel es el índice: cada tarjeta es una zona de edición, y las tablas y los
// formularios viven en su página (así cada una tiene su enlace de vuelta aquí).
const sections = [
  {
    to: '/dulzia-panel/servicios',
    icon: '🍭',
    title: 'Secciones',
    text: 'Los servicios que salen en la web: fotos, textos, categoría, orden y cuáles van a la portada.',
  },
  {
    to: '/dulzia-panel/categorias',
    icon: '🏷️',
    title: 'Categorías',
    text: 'Las pestañas del catálogo y la etiqueta que muestra cada sección.',
  },
  {
    to: '/dulzia-panel/ajustes-email',
    icon: '📩',
    title: 'Avisos por email',
    text: 'A qué dirección llegan los mensajes del formulario de contacto. Es interna: no se publica.',
  },
  {
    to: '/dulzia-panel/ajustes-contacto',
    icon: '📞',
    title: 'Datos de contacto',
    text: 'El teléfono y el email que se publican en la web: pie, contacto, legales y WhatsApp.',
  },
]

// El contador de sin leer alimenta la tarjeta del buzón.
const unreadMessages = ref(0)
async function fetchUnreadCount() {
  try { unreadMessages.value = (await apiGetMessages(1)).counts.unread }
  catch { /* la tarjeta se queda sin contador si la petición falla */ }
}
onMounted(fetchUnreadCount)

async function handleLogout() { await logout(); router.push('/dulzia-panel/login') }
</script>

<template>
  <div class="admin">
    <header class="admin-header">
      <div class="admin-header__inner">
        <h1 class="admin-header__title">✨ Dulzia — Panel de administración</h1>
        <button class="btn-logout" @click="handleLogout">Cerrar sesión</button>
      </div>
    </header>

    <main class="admin-main">
      <router-link to="/dulzia-panel/mensajes" class="card card--inbox">
        <span class="card__icon" aria-hidden="true">📩</span>
        <div class="card__body">
          <h2 class="card__title">
            Mensajes
            <span v-if="unreadMessages > 0" class="msg-badge">{{ unreadMessages }} sin leer</span>
          </h2>
          <p class="card__text">Los mensajes que llegan del formulario de contacto.</p>
        </div>
        <span class="card__go">Ver →</span>
      </router-link>

      <p class="intro">¿Qué quieres editar?</p>

      <div class="cards">
        <router-link v-for="section in sections" :key="section.to" :to="section.to" class="card">
          <span class="card__icon" aria-hidden="true">{{ section.icon }}</span>
          <h2 class="card__title">{{ section.title }}</h2>
          <p class="card__text">{{ section.text }}</p>
          <span class="card__go">Editar →</span>
        </router-link>
      </div>
    </main>
  </div>
</template>

<style scoped>
.admin{min-height:100vh;background:#f7f4f1;font-family:system-ui,sans-serif}
.admin-header{background:white;border-bottom:1px solid #ebe8e4;padding:1rem 1.5rem;position:sticky;top:0;z-index:10}
.admin-header__inner{max-width:1000px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.admin-header__title{font-size:1.1rem;font-weight:700;color:#1a1a1a;margin:0}
.msg-badge{background:#c8748a;color:white;border-radius:20px;padding:.1rem .6rem;font-size:.75rem;font-weight:700;vertical-align:middle}
.btn-logout{padding:.45rem .9rem;background:transparent;border:1.5px solid #d0ccc8;border-radius:8px;cursor:pointer;font-size:.8rem;color:#666;transition:border-color .2s,color .2s}
.btn-logout:hover{border-color:#c8748a;color:#c8748a}
.admin-main{max-width:1000px;margin:0 auto;padding:2rem 1.5rem}
.intro{font-size:1rem;font-weight:700;color:#444;margin:1.75rem 0 1.25rem}
.cards{display:grid;grid-template-columns:1fr;gap:1rem}
@media (min-width:640px){.cards{grid-template-columns:repeat(2,1fr)}}
.card{display:flex;flex-direction:column;gap:.5rem;background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1.5px solid transparent;text-decoration:none;transition:border-color .2s,transform .2s,box-shadow .2s}
.card:hover{border-color:#c8748a;transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.09)}
/* El buzón va arriba, a lo ancho: no es una zona de edición, es lo que pide
   atención (lleva el contador de sin leer). */
.card--inbox{flex-direction:row;align-items:center;gap:1rem;margin-bottom:1rem}
.card--inbox .card__body{flex:1}
.card--inbox .card__go{margin-top:0;padding-top:0}
.card__icon{font-size:1.6rem}
.card__title{font-size:1.05rem;font-weight:800;color:#1a1a1a;margin:0}
.card__text{font-size:.875rem;color:#777;line-height:1.6;margin:0}
.card__go{margin-top:auto;padding-top:.5rem;font-size:.85rem;font-weight:700;color:#c8748a}
</style>
