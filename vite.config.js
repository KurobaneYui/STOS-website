import { fileURLToPath, URL } from 'node:url'
import { resolve } from 'path'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    rollupOptions: {
      input: {
        404: resolve(__dirname, '404.html'),
        405: resolve(__dirname, '405.html'),
        500: resolve(__dirname, '500.html'),
        main: resolve(__dirname, 'index.html'),
        login: resolve(__dirname, 'authentication/login.html'),
        logout: resolve(__dirname, 'authentication/logout.html'),
        forgot_password: resolve(__dirname, 'authentication/forgot_password.html'),
        register: resolve(__dirname, 'authentication/register.html'),
      },
    },
  },
})
