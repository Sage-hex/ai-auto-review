import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/backend': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path
      }
    },
    cors: true
  }
})
