import { createApp, nextTick } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import { router } from './router/index.js'
import './styles/main.scss'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')

// Avisa al prerender de que la página está lista (vite.config.js espera este
// evento en vez de un tiempo fijo). Se dispara al siguiente tick, cuando el
// DOM ya refleja los datos del snapshot, y de nuevo a los 15 s como red de
// seguridad: sin esto, un fallo de montaje colgaría el build para siempre.
const prerenderReady = () => document.dispatchEvent(new Event('x-app-rendered'))
nextTick(prerenderReady)
setTimeout(prerenderReady, 15000)
