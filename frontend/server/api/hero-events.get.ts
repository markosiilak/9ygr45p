import { createApiProxy } from '@composables/useApiProxy'

export default defineEventHandler(async () => {
  const proxyHandler = createApiProxy('/api/events')
  const events = await proxyHandler({} as any)

  // Choose first 3 events for hero slides
  if (Array.isArray(events)) {
    return events.slice(0, 3)
  }
  return []
})
