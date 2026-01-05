// Nuxt 3 custom import.meta properties
interface ImportMeta {
  client: boolean
  server: boolean
  dev: boolean
  env: Record<string, string | undefined>
}
