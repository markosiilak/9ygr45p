import { defineEventHandler, createError } from 'h3'
import { useRuntimeConfig } from '#imports'

/**
 * Reusable API proxy logic for Nuxt server routes
 * Handles configuration, error handling, and base URL setup
 */
export function createApiProxy(endpoint: string) {
  return defineEventHandler(async (event) => {
    // Use hardcoded backend URL for Docker container communication
    const base = 'http://backend:8000'

    console.log('Using API base URL:', base, 'for endpoint:', endpoint)

    try {
      const url = `${String(base).replace(/\/$/, '')}${endpoint}`
      const res = await $fetch(url)
      return res
    } catch (err: unknown) {
      const error = err as { statusCode?: number, message?: string }
      console.error('API request failed:', error)
      throw createError({
        statusCode: error?.statusCode || 502,
        statusMessage: error?.message || 'Bad Gateway'
      })
    }
  })
}

/**
 * Create API proxy with ID parameter extraction
 * Handles slug-based URLs for events
 */
export function createApiProxyWithId(endpointTemplate: string, idParam: string = 'id') {
  return defineEventHandler(async (event) => {
    // Use hardcoded backend URL for Docker container communication
    const base = 'http://backend:8000'

    console.log('Using API base URL:', base, 'for endpoint template:', endpointTemplate)

    const idParamValue = event.context?.params?.[idParam] as string | undefined

    if (!idParamValue) {
      throw createError({ statusCode: 400, statusMessage: `Missing ${idParam} parameter` })
    }

    // Extract numeric ID from slug-based URL (handles both /events/33 and /events/33-slug)
    const id = idParamValue.split('-')[0]

    try {
      const endpoint = endpointTemplate.replace(`{${idParam}}`, encodeURIComponent(String(id)))
      const url = `${String(base).replace(/\/$/, '')}${endpoint}`
      const res = await $fetch(url)
      return res
    } catch (err: unknown) {
      const error = err as { statusCode?: number, message?: string }
      throw createError({
        statusCode: error?.statusCode || 502,
        statusMessage: error?.message || 'Bad Gateway'
      })
    }
  })
}
