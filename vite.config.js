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
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/Ajax': 'http://localhost:8008'
    }
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
        register: resolve(__dirname, 'authentication/register.html'),
        forgot_password: resolve(__dirname, 'authentication/forgot_password.html'),
        user_center_index: resolve(__dirname, 'user_center/index.html'),
        user_center_contact: resolve(__dirname, 'user_center/contact.html'),
        user_center_personal_info: resolve(__dirname, 'user_center/personal_info.html'),
      },
    },
  },
})
