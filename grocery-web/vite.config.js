import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiUrl = process.env.VITE_API_URL || env.VITE_API_URL

  if (mode === 'production') {
    if (!apiUrl) {
      throw new Error('VITE_API_URL must be configured for a production build.')
    }

    const protocol = new URL(apiUrl).protocol

    if (!['http:', 'https:'].includes(protocol)) {
      throw new Error('VITE_API_URL must be an HTTP or HTTPS URL.')
    }
  }

  return {
    plugins: [vue()],
  }
})
