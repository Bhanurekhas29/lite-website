import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// In development, requests to /api and /media are forwarded to the PHP backend.
// Start the backend with: php -S localhost:8000 -t backend backend/router.php
const backend = 'http://localhost:8000'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api': backend,
      '/media': backend,
    },
  },
})
