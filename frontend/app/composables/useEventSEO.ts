import { computed, type ComputedRef } from 'vue'
import { getEventUrl } from '@utils/slug'
import type { Event } from '@types'

interface EventSEOOptions {
  event: ComputedRef<Event | null>
  eventId: string | number
  eventStatus?: ComputedRef<string | null>
  siteName?: string
}

/**
 * Composable for generating comprehensive SEO meta tags for events
 *
 * @example
 * ```ts
 * const { setupEventSEO } = useEventSEO({
 *   event: computed(() => myEvent.value),
 *   eventId: eventId,
 *   eventStatus: computed(() => status.value)
 * })
 *
 * setupEventSEO()
 * ```
 */
export function useEventSEO(options: EventSEOOptions) {
  const { event, eventId, eventStatus, siteName = 'Ticket' } = options

  // Generate full event URL
  const eventUrl = computed(() => {
    if (!event.value) return ''
    return `${window.location.origin}${getEventUrl(eventId, event.value.title || event.value.name)}`
  })

  // Generate optimized image URL
  const optimizedImageUrl = computed(() => {
    if (!event.value?.image_url) return ''
    const baseUrl = event.value.image_url.includes('http')
      ? event.value.image_url
      : `${window.location.origin}${event.value.image_url}`
    return baseUrl
  })

  // Generate Schema.org structured data for rich snippets
  const structuredData = computed(() => {
    if (!event.value) return null

    const data: Record<string, unknown> = {
      '@context': 'https://schema.org',
      '@type': 'Event',
      name: event.value.title || event.value.name,
      description: (event.value.description || '').replace(/<[^>]+>/g, '').slice(0, 300),
      startDate: event.value.start_time || event.value.date,
      eventStatus: 'https://schema.org/' + (eventStatus?.value === 'sold-out' ? 'EventSoldOut' : 'EventScheduled'),
      eventAttendanceMode: 'https://schema.org/OfflineEventAttendanceMode',
      location: event.value.location ? {
        '@type': 'Place',
        name: event.value.location,
        address: event.value.location
      } : undefined,
      image: optimizedImageUrl.value ? [optimizedImageUrl.value] : undefined,
      organizer: event.value.organizer ? {
        '@type': 'Organization',
        name: event.value.organizer
      } : undefined
    }

    // Add offers if tickets available
    if (event.value.ticket_types && event.value.ticket_types.length > 0) {
      const offers = event.value.ticket_types.map(ticket => ({
        '@type': 'Offer',
        name: ticket.name,
        price: ticket.price,
        priceCurrency: ticket.currency || 'EUR',
        availability: ticket.available_quantity > 0
          ? 'https://schema.org/InStock'
          : 'https://schema.org/SoldOut',
        url: eventUrl.value,
        validFrom: event.value.start_time || event.value.date
      }))

      data.offers = offers
    }

    return data
  })

  // Generate optimized meta description (155-160 chars)
  const metaDescription = computed(() => {
    if (!event.value) return ''

    const rawDesc = (event.value.description || '').replace(/<[^>]+>/g, '').trim()
    let desc = rawDesc.slice(0, 157)
    if (rawDesc.length > 157) desc += '...'

    // Add event date context if available and description is short
    const dateInfo = event.value.start_time || event.value.date
    if (dateInfo && desc.length < 100) {
      const formattedDate = new Date(String(dateInfo)).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
      desc = `${formattedDate} - ${desc}`.slice(0, 160)
    }

    return desc
  })

  // Generate SEO keywords
  const metaKeywords = computed(() => {
    if (!event.value) return ''

    const title = event.value.title || event.value.name
    return [
      title,
      event.value.location || '',
      'event tickets',
      'buy tickets',
      ...(event.value.categories || [])
    ].filter(Boolean).join(', ')
  })

  // Setup comprehensive SEO meta tags
  const setupEventSEO = () => {
    useHead(() => {
      if (!event.value) return {}

      const title = event.value.title || event.value.name || 'Event'
      const fullTitle = `${title} | ${siteName}`

      return {
        title: fullTitle,
        meta: [
          // Primary Meta Tags
          { name: 'title', content: fullTitle },
          { name: 'description', content: metaDescription.value },
          { name: 'keywords', content: metaKeywords.value },
          { name: 'author', content: event.value.organizer || siteName },

          // Open Graph / Facebook
          { property: 'og:type', content: 'website' },
          { property: 'og:url', content: eventUrl.value },
          { property: 'og:site_name', content: siteName },
          { property: 'og:title', content: fullTitle },
          { property: 'og:description', content: metaDescription.value },
          { property: 'og:image', content: optimizedImageUrl.value },
          { property: 'og:image:width', content: '1200' },
          { property: 'og:image:height', content: '630' },
          { property: 'og:image:alt', content: title },

          // Twitter Card
          { name: 'twitter:card', content: 'summary_large_image' },
          { name: 'twitter:url', content: eventUrl.value },
          { name: 'twitter:title', content: fullTitle },
          { name: 'twitter:description', content: metaDescription.value },
          { name: 'twitter:image', content: optimizedImageUrl.value },
          { name: 'twitter:image:alt', content: title },

          // Additional SEO
          { name: 'robots', content: 'index, follow' },
          { name: 'googlebot', content: 'index, follow' }
        ],
        link: [
          // Canonical URL
          { rel: 'canonical', href: eventUrl.value }
        ],
        script: structuredData.value ? [
          {
            type: 'application/ld+json',
            children: JSON.stringify(structuredData.value)
          }
        ] : []
      }
    })
  }

  return {
    eventUrl,
    optimizedImageUrl,
    structuredData,
    metaDescription,
    metaKeywords,
    setupEventSEO
  }
}
