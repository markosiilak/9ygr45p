import { ref, computed } from 'vue'
import type { Translations } from '@types'

export type Locale = 'en' | 'et'

// In-memory cache for translations
const translationsCache: Record<Locale, Translations | null> = {
  en: null,
  et: null
}

// Loading state
const loadingTranslations = ref<Set<Locale>>(new Set())

// Persist locale in localStorage
const getInitialLocale = (): Locale => {
  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('locale') as Locale
    if (saved && (saved === 'en' || saved === 'et')) {
      return saved
    }
    // Check browser language
    const browserLang = navigator.language.split('-')[0]
    if (browserLang === 'et') {
      return 'et'
    }
  }
  return 'en'
}

const currentLocale = ref<Locale>(getInitialLocale())

/**
 * Load translations from backend API
 */
async function loadTranslations(locale: Locale): Promise<Translations> {
  // Check cache first
  if (translationsCache[locale]) {
    return translationsCache[locale]!
  }

  // Version for cache invalidation (increment when translations structure changes)
  const CACHE_VERSION = 2

  // Check localStorage cache (with 1 hour expiration)
  if (typeof window !== 'undefined') {
    const cached = localStorage.getItem(`translations_${locale}`)
    if (cached) {
      try {
        const cacheData = JSON.parse(cached)
        // Check cache version and expiration (1 hour = 3600000ms)
        const cacheAge = Date.now() - (cacheData.timestamp || 0)
        if (
          cacheData.version === CACHE_VERSION &&
          cacheAge < 3600000 &&
          cacheData.data
        ) {
          translationsCache[locale] = cacheData.data
          return cacheData.data
        } else {
          // Cache expired or version mismatch, remove it
          localStorage.removeItem(`translations_${locale}`)
        }
      } catch {
        // Invalid cache, remove it
        localStorage.removeItem(`translations_${locale}`)
      }
    }
  }

  // Prevent duplicate requests
  if (loadingTranslations.value.has(locale)) {
    // Wait for existing request (simplified - in production, use a proper promise cache)
    await new Promise(resolve => setTimeout(resolve, 100))
    if (translationsCache[locale]) {
      return translationsCache[locale]!
    }
  }

  loadingTranslations.value.add(locale)

  try {
    // Use Nuxt server route for SSR compatibility, or direct backend URL on client
    // Use $fetch and useRuntimeConfig via Nuxt composables for SSR/client compatibility
    const { $fetch, useRuntimeConfig } = (globalThis as { useNuxtApp?: () => { $fetch?: typeof fetch, useRuntimeConfig?: () => { public?: { apiBaseUrl?: string } } } }).useNuxtApp?.() || {}
    let url: string
    if (typeof window === 'undefined') {
      // Nuxt server-side route proxy for SSR
      url = `/api/translations/${locale}`
    } else {
      // Use configured API base URL (from publicRuntimeConfig)
      const config
        = typeof useRuntimeConfig === 'function'
          ? useRuntimeConfig()
          : (window as { __NUXT__?: { config?: { public?: { apiBaseUrl?: string } } } })?.__NUXT__?.config?.public
      const apiBaseUrl = (config && typeof config === 'object' && 'public' in config ? config.public?.apiBaseUrl : (config && typeof config === 'object' && 'apiBaseUrl' in config ? config.apiBaseUrl : undefined)) || 'http://localhost:8000'
      url = `${apiBaseUrl}/api/translations/${locale}`
    }

    // Prefer Nuxt's $fetch, fallback to native fetch if unavailable (for tests)
    const fetchImpl = $fetch || fetch
    const response = await fetchImpl(url, {
      headers: {
        Accept: 'application/json'
      }
    })
    // If using native fetch, parse .json()
    const data: Translations
      = $fetch
        ? (response as unknown as Translations)
        : (await (response as Response).json())

    // Cache in memory
    translationsCache[locale] = data

    // Cache in localStorage (with timestamp and version for expiration)
    if (typeof window !== 'undefined') {
      const cacheData = {
        data,
        timestamp: Date.now(),
        version: CACHE_VERSION
      }
      localStorage.setItem(`translations_${locale}`, JSON.stringify(cacheData))
    }

    return data
  } catch (error) {
    console.error(`Failed to load translations from backend for locale ${locale}:`, error)
    // Return empty translations object if backend fails
    const emptyTranslations: Translations = {}
    translationsCache[locale] = emptyTranslations
    return emptyTranslations
  } finally {
    loadingTranslations.value.delete(locale)
  }
}

/**
 * Get translations for current locale
 */
function getTranslations(locale: Locale): Translations {
  return translationsCache[locale] || {}
}

// Initialize translations on client side
if (typeof window !== 'undefined') {
  // Load initial locale translations
  loadTranslations(getInitialLocale()).catch(console.error)

  // Preload other locale
  const otherLocale: Locale = getInitialLocale() === 'en' ? 'et' : 'en'
  loadTranslations(otherLocale).catch(console.error)
}

export function useI18n() {
  const locale = computed({
    get: () => currentLocale.value,
    set: async (value: Locale) => {
      currentLocale.value = value
      if (typeof window !== 'undefined') {
        localStorage.setItem('locale', value)
      }
      // Ensure translations are loaded for the new locale
      await loadTranslations(value)
    }
  })

  const availableLocales: { code: Locale, name: string, flag: string }[] = [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'et', name: 'Eesti', flag: '🇪🇪' }
  ]

  /**
   * Get a nested translation value using dot notation
   * @param key - Dot notation key like "events.title" or "auth.errors.invalidCredentials"
   * @param params - Optional parameters for interpolation like { count: 5 }
   */
  const t = (key: string, params?: Record<string, string | number>): string => {
    const keys = key.split('.')
    const currentTranslations = getTranslations(currentLocale.value)
    let value: unknown = currentTranslations

    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = (value as Record<string, unknown>)[k]
      } else {
        // Key not found in current locale, try English as fallback
        const englishTranslations = getTranslations('en')
        if (englishTranslations && Object.keys(englishTranslations).length > 0) {
          value = englishTranslations
          for (const fallbackKey of keys) {
            if (value && typeof value === 'object' && fallbackKey in value) {
              value = (value as Record<string, unknown>)[fallbackKey]
            } else {
              return key
            }
          }
        } else {
          return key
        }
        break
      }
    }

    if (typeof value !== 'string') {
      return key
    }

    // Interpolate parameters
    if (params) {
      return value.replace(/\{(\w+)\}/g, (_, paramKey) => {
        return params[paramKey]?.toString() ?? `{${paramKey}}`
      })
    }

    return value
  }

  /**
   * Set the current locale
   */
  const setLocale = async (newLocale: Locale) => {
    currentLocale.value = newLocale
    if (typeof window !== 'undefined') {
      localStorage.setItem('locale', newLocale)
    }
    await loadTranslations(newLocale)
  }

  /**
   * Toggle between available locales
   */
  const toggleLocale = async () => {
    const currentIndex = availableLocales.findIndex(l => l.code === locale.value)
    const nextIndex = (currentIndex + 1) % availableLocales.length
    const nextLocale = availableLocales[nextIndex]
    if (nextLocale) {
      await setLocale(nextLocale.code)
    }
  }

  /**
   * Refresh translations from backend
   */
  const refreshTranslations = async (localeToRefresh?: Locale) => {
    const targetLocale = localeToRefresh || currentLocale.value
    // Clear cache
    translationsCache[targetLocale] = null
    if (typeof window !== 'undefined') {
      localStorage.removeItem(`translations_${targetLocale}`)
    }
    // Reload
    await loadTranslations(targetLocale)
  }

  return {
    locale,
    availableLocales,
    t,
    setLocale,
    toggleLocale,
    refreshTranslations
  }
}

export default useI18n
