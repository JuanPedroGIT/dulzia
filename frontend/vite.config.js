import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import prerender from 'vite-plugin-prerender'
import path from 'node:path'
import fs from 'node:fs'

const PuppeteerRenderer = prerender.PuppeteerRenderer

// Snapshot del catálogo de servicios para el prerender: las páginas de detalle
// cargan sus datos de /api/services, que no existe durante el build. El
// snapshot se inyecta en cada página renderizada (window.__DULZIA_PRERENDER__)
// y el composable useServices lo usa como fuente en ese contexto.
// Regenerar tras cambiar el catálogo: ver docs/plan-seo.md (etapa 2).
const snapshotPath = path.join(path.dirname(fileURLToPath(import.meta.url)), 'prerender-data', 'snapshot.json')
const prerenderSnapshot = fs.existsSync(snapshotPath)
  ? JSON.parse(fs.readFileSync(snapshotPath, 'utf-8'))
  : null

const serviceRoutes = (prerenderSnapshot?.list ?? []).map(s => `/servicios/${s.id}`)

export default defineConfig({
  plugins: [
    vue(),
    prerender({
      staticDir: path.join(path.dirname(fileURLToPath(import.meta.url)), 'dist'),
      routes: ['/', '/servicios', '/nosotros', '/contacto', ...serviceRoutes],
      renderer: new PuppeteerRenderer({
        renderAfterTime: 2000,
        headless: true,
        inject: prerenderSnapshot,
        injectProperty: '__DULZIA_PRERENDER__',
      }),
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@use "sass:color"; @use "@/styles/mixins" as *;`,
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    allowedHosts: ['dulziasalamanca.es', 'www.dulziasalamanca.es','dulzia-frontend'],
    watch: {
      usePolling: true,   // Required for Windows + Docker file watching
      interval: 300,
    },
    proxy: {
      '/api': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
      '/health': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
  },
})
