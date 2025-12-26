/**
 * Module path declarations for TypeScript
 * This helps TypeScript resolve the @ aliases defined in nuxt.config.ts
 */

declare module '@utils/formatDateTime' {
  export default function formatDateTime(value?: string): string
}

interface PriceInput {
  price?: number
  amount?: number
  currency?: string
  currency_code?: string
}

declare module '@utils/formatPrice' {
  export default function formatPrice(t: PriceInput | null | undefined): string
}

declare module '@utils/imageUrl' {
  export function getOptimizedImageUrl(originalUrl: string | undefined, width: number): string
  export function getImageSrcset(originalUrl: string | undefined): string
}

declare module '@utils/sanitizeHtml' {
  export default function sanitizeHtml(html: string): string
}

declare module '@utils/validation' {
  export function isValidUrl(url: string): boolean
  export function handleImageError(event: Event): void
  export function isValidEmail(email: string): boolean
  export function isValidPassword(password: string): boolean
}

declare module '@components/*' {
  import type { DefineComponent } from 'vue'

  const component: DefineComponent
  export default component
}

declare module '@composables/*' {
  // Composables are auto-imported by Nuxt
}
