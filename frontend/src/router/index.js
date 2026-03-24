import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/',          component: () => import('@/pages/HomePage.vue') },
  { path: '/servicios', component: () => import('@/pages/ServiciosPage.vue') },
  { path: '/nosotros',  component: () => import('@/pages/NosotrosPage.vue') },
  { path: '/contacto',  component: () => import('@/pages/ContactoPage.vue') },
  { path: '/politica-cookies', component: () => import('@/pages/PoliticaCookiesPage.vue') },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0, behavior: 'smooth' }),
})
