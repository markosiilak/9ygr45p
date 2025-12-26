import { computed, type ComputedRef } from 'vue'
import { getOptimizedImageUrl, getImageSrcset } from '@utils/imageUrl'

/**
 * Composable for image optimization
 * Provides consistent image URL optimization across components
 */
export function useImageOptimization(imageUrl: ComputedRef<string | undefined>, options: { width?: number, sizes?: string } = {}) {
  const { width = 600, sizes = '100vw' } = options

  const optimizedImageUrl = computed(() => getOptimizedImageUrl(imageUrl.value, width))

  const imageSrcset = computed(() => getImageSrcset(imageUrl.value))

  return {
    optimizedImageUrl,
    imageSrcset,
    sizes
  }
}

/**
 * Preset configurations for common image sizes
 */
export const imagePresets = {
  thumbnail: { width: 200, sizes: '200px' },
  card: { width: 400, sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
  hero: { width: 1200, sizes: '100vw' },
  detail: { width: 800, sizes: '100vw' }
} as const

/**
 * Use preset image optimization
 */
export function usePresetImage(imageUrl: ComputedRef<string | undefined>, preset: keyof typeof imagePresets) {
  const presetConfig = imagePresets[preset]
  return useImageOptimization(imageUrl, presetConfig)
}
