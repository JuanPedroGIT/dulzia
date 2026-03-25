<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { login } = useAuth()

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await login(username.value, password.value)
    router.push('/dulzia-panel')
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-logo">
        <span class="login-logo__icon">✨</span>
        <h1 class="login-logo__title">Dulzia Salamanca</h1>
        <p class="login-logo__subtitle">Panel de administración</p>
      </div>

      <form class="login-form" @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="username">Usuario</label>
          <input
            id="username"
            v-model="username"
            type="text"
            autocomplete="username"
            required
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input
            id="password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            required
            :disabled="loading"
          />
        </div>

        <p v-if="error" class="login-error">{{ error }}</p>

        <button type="submit" class="login-btn" :disabled="loading">
          {{ loading ? 'Entrando…' : 'Entrar' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fdf6f0;
}

.login-card {
  background: white;
  border-radius: 16px;
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
}

.login-logo {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo__icon {
  font-size: 2.5rem;
}

.login-logo__title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0.5rem 0 0.25rem;
}

.login-logo__subtitle {
  font-size: 0.875rem;
  color: #888;
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
}

.form-group input {
  padding: 0.75rem 1rem;
  border: 1.5px solid #e5e5e5;
  border-radius: 8px;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus {
  border-color: #d4a0b0;
}

.login-error {
  color: #c0392b;
  font-size: 0.875rem;
  margin: 0;
}

.login-btn {
  padding: 0.875rem;
  background: #c8748a;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.login-btn:hover:not(:disabled) {
  background: #b5637a;
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
