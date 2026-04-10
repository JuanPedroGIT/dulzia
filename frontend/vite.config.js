import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import prerender from 'vite-plugin-prerender'
import path from 'node:path'

const PuppeteerRenderer = prerender.PuppeteerRenderer

export default defineConfig({
  plugins: [
    vue(),
    prerender({
      staticDir: path.join(path.dirname(fileURLToPath(import.meta.url)), 'dist'),
      routes: ['/', '/servicios', '/nosotros', '/contacto'],
      renderer: new PuppeteerRenderer({
        renderAfterTime: 2000,
        headless: true,
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
