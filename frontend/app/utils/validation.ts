/**
 * Validation utility functions
 */

/**
 * Check if a string is a valid URL
 */
export const isValidUrl = (url: string): boolean => {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

/**
 * Handle image load errors by hiding the image element
 */
export const handleImageError = (event: Event): void => {
  const target = event.target as HTMLImageElement
  target.style.display = 'none'
}

/**
 * Validate email format
 */
export const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

/**
 * Validate password strength (at least 8 characters)
 */
export const isValidPassword = (password: string): boolean => {
  return password.length >= 8
}
