import { defineEventHandler, createError, getRouterParam, type H3Event } from 'h3'
import { createApiProxy } from '@composables/useApiProxy'

type SupportedLocale = 'en' | 'et'

function getLocaleFromEvent(event: H3Event): SupportedLocale {
  const localeParam = getRouterParam(event, 'locale')
  if (localeParam === 'en' || localeParam === 'et') return localeParam
  return 'en'
}

export default defineEventHandler(async (event: H3Event) => {
  const locale = getLocaleFromEvent(event)

  // Validate locale (should not be necessary due to fallback, but keeping for strictness)
  if (locale !== 'en' && locale !== 'et') {
    throw createError({
      statusCode: 400,
      statusMessage: 'Invalid locale. Supported locales: en, et'
    })
  }

  // Proxy the request to the downstream translations API
  const proxyHandler = createApiProxy(`/api/translations/${locale}`)
  return await proxyHandler(event)
})
