import { ref } from 'vue'
import { apiLogin, apiLogout } from '@/services/adminService'

export function useAuth() {
  const isLoggedIn = ref(!!localStorage.getItem('admin_token'))

  async function login(username, password) {
    const data = await apiLogin(username, password)
    localStorage.setItem('admin_token', data.token)
    isLoggedIn.value = true
  }

  async function logout() {
    await apiLogout().catch(() => {})
    localStorage.removeItem('admin_token')
    isLoggedIn.value = false
  }

  return { isLoggedIn, login, logout }
}
