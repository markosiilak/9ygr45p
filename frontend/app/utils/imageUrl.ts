/**
 * Generate an optimized image URL with resizing
 *
 * @param originalUrl - The original image URL from the API
 * @param width - Desired width (will be snapped to: 100, 200, 400, 600, 800, 1200, 1600)
 * @returns Optimized image URL or original if not from our backend
 */
export function getOptimizedImageUrl(originalUrl: string | undefined, width: number = 800): string {
  if (!originalUrl) {
    return ''
  }

  // Check if it's from our backend uploads
  const uploadsMatch = originalUrl.match(/\/uploads\/images\/([a-zA-Z0-9_-]+\.(jpg|jpeg|png|gif|webp))$/i)

  if (uploadsMatch) {
    const filename = uploadsMatch[1]
    // Get the base URL (everything before /uploads)
    const baseUrl = originalUrl.replace(/\/uploads\/images\/.*$/, '')
    return `${baseUrl}/images/${width}/${filename}`
  }

  // Return original URL for external images
  return originalUrl
}

/**
 * Generate srcset for responsive images
 *
 * @param originalUrl - The original image URL from the API
 * @returns srcset string for responsive loading
 */
export function getImageSrcset(originalUrl: string | undefined): string {
  if (!originalUrl) {
    return ''
  }

  const widths = [400, 600, 800, 1200]

  return widths
    .map(w => `${getOptimizedImageUrl(originalUrl, w)} ${w}w`)
    .join(', ')
}

export default getOptimizedImageUrl
