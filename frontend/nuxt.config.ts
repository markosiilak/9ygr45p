// https://nuxt.com/docs/api/configuration/nuxt-config
import { resolve } from 'path'

export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui',
    '@nuxt/hints',
    '@nuxt/image',
    '@nuxt/scripts',
    '@nuxt/test-utils'
  ],

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  // Disable dark mode - light only
  colorMode: {
    preference: 'light',
    fallback: 'light',
    classSuffix: ''
  },

  runtimeConfig: {
    // Server-side API URL (used by Nuxt server routes)
    apiBaseUrl: 'http://backend:8000',
    // Client-side API URL (used by browser)
    public: {
      apiBaseUrl: process.env.NUXT_API_BASE_URL || 'http://localhost:8000'
    }
  },

  alias: {
    '@components': resolve(__dirname, 'app/components'),
    '@composables': resolve(__dirname, 'app/composables'),
    '@assets': resolve(__dirname, 'app/assets'),
    '@pages': resolve(__dirname, 'app/pages'),
    '@layouts': resolve(__dirname, 'app/layouts'),
    '@utils': resolve(__dirname, 'app/utils'),
    '@locales': resolve(__dirname, 'app/locales'),
    '@types': resolve(__dirname, 'app/types')
  },

  routeRules: {
    '/': { prerender: true },
    '/login': { ssr: true },
    '/register': { ssr: true }
  },

  compatibilityDate: '2025-01-15',

  typescript: {
    tsConfig: {
      compilerOptions: {
        baseUrl: '.',
        paths: {
          '@components/*': ['app/components/*'],
          '@composables/*': ['app/composables/*'],
          '@assets/*': ['app/assets/*'],
          '@pages/*': ['app/pages/*'],
          '@layouts/*': ['app/layouts/*'],
          '@utils/*': ['app/utils/*'],
          '@locales/*': ['app/locales/*'],
          '@types': ['app/types'],
          '@types/*': ['app/types/*']
        }
      }
    }
  },

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  }
})
