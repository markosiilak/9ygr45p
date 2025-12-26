export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  return {
    serverApiUrl: config.apiBaseUrl,
    clientApiUrl: config.public.apiBaseUrl,
    envVars: {
      NUXT_API_BASE_URL_SERVER: process.env.NUXT_API_BASE_URL_SERVER,
      NUXT_API_BASE_URL: process.env.NUXT_API_BASE_URL
    }
  }
})
