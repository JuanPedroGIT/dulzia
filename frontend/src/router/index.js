import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/',          component: () => import('@/pages/HomePage.vue') },
  { path: '/servicios', component: () => import('@/pages/ServiciosPage.vue') },
  { path: '/servicios/:id', component: () => import('@/pages/ServicioDetallePage.vue') },
  { path: '/nosotros',  component: () => import('@/pages/NosotrosPage.vue') },
  { path: '/contacto',  component: () => import('@/pages/ContactoPage.vue') },
  { path: '/politica-cookies', component: () => import('@/pages/PoliticaCookiesPage.vue') },
  { path: '/dulzia-panel/login', component: () => import('@/pages/admin/AdminLoginPage.vue') },
  { path: '/dulzia-panel', component: () => import('@/pages/admin/AdminDashboardPage.vue'), meta: { requiresAuth: true } },
  { path: '/dulzia-panel/servicios/:id', component: () => import('@/pages/admin/AdminServiceDetailPage.vue'), meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0, behavior: 'smooth' }),
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !localStorage.getItem('admin_token')) {
    return '/dulzia-panel/login'
  }
})
