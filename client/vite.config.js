import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// In development, forward API calls, uploaded images and the Socket.io connection to the Express server
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api': 'http://localhost:5000',
      '/uploads': 'http://localhost:5000',
      '/socket.io': { target: 'http://localhost:5000', ws: true },
    },
  },
});
