/**
 * Generate a URL-friendly slug from a string
 * @param text - The text to convert to a slug
 * @returns A URL-friendly slug
 */
export function generateSlug(text: string | undefined | null): string {
  if (!text) {
    return ''
  }

  return text
    .toString()
    .toLowerCase()
    .trim()
    // Replace spaces and underscores with hyphens
    .replace(/[\s_]+/g, '-')
    // Remove all non-word characters except hyphens
    .replace(/[^\w-]+/g, '')
    // Replace multiple hyphens with a single hyphen
    .replace(/--+/g, '-')
    // Remove leading and trailing hyphens
    .replace(/^-+/, '')
    .replace(/-+$/, '')
}

/**
 * Generate a full event URL with slug
 * @param eventId - The event ID
 * @param title - The event title (optional, will generate slug if provided)
 * @returns A URL like /events/33-event-title-slug
 */
export function getEventUrl(eventId: number | string, title?: string | null): string {
  const id = String(eventId)
  if (title) {
    const slug = generateSlug(title)
    return slug
      ? `/events/${id}-${slug}`
      : `/events/${id}`
  }
  return `/events/${id}`
}

/**
 * Extract event ID from a URL path
 * @param path - The URL path (e.g., "/events/33-event-title-slug" or "/events/33")
 * @returns The event ID or empty string if not found
 */
export function extractEventId(path: string): string {
  // Match pattern: /events/123 or /events/123-slug-here
  const match = path.match(/\/events\/(\d+)(?:-.*)?$/)
  return match?.[1] ?? ''
}

export default generateSlug
