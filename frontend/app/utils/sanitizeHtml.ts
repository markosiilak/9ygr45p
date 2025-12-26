export default function sanitizeHtml(raw: string): string {
  if (!raw) return ''

  if (typeof window !== 'undefined' && 'DOMParser' in window) {
    const parser = new DOMParser()
    const doc = parser.parseFromString(raw, 'text/html')
    doc.querySelectorAll('script,style').forEach(el => el.remove())

    const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_ELEMENT, null)
    while (true) {
      const n = walker.nextNode() as Element | null
      if (!n) break
      Array.from(n.attributes).forEach((attr) => {
        const name = attr.name.toLowerCase()
        const val = attr.value
        if (name.startsWith('on')) n.removeAttribute(attr.name)
        if ((name === 'href' || name === 'src') && /^\s*javascript:\s*/i.test(val)) n.removeAttribute(attr.name)
      })
    }

    const allowed = new Set(['b', 'i', 'em', 'strong', 'p', 'br', 'ul', 'ol', 'li', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div'])
    // Only walk and modify nodes inside the document body to avoid
    // replacing top-level nodes (html/head) whose parent is the Document.
    doc.body.querySelectorAll('*').forEach((el) => {
      const tag = el.tagName.toLowerCase()
      if (!allowed.has(tag)) {
        const text = doc.createTextNode(el.textContent || '')
        const parent = el.parentNode
        // Only replace when parent is an Element (not the Document)
        if (parent && parent.nodeType === Node.ELEMENT_NODE) {
          parent.replaceChild(text, el)
        }
      } else {
        Array.from(el.attributes).forEach((attr) => {
          const an = attr.name.toLowerCase()
          if (!['href', 'alt', 'src', 'title', 'target', 'rel'].includes(an)) el.removeAttribute(attr.name)
          if ((an === 'href' || an === 'src') && /^\s*javascript:\s*/i.test(attr.value)) el.removeAttribute(attr.name)
        })
      }
    })

    return doc.body.innerHTML
  }

  let s = raw.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '').replace(/<style[\s\S]*?>[\s\S]*?<\/style>/gi, '')
  s = s.replace(/\son\w+=("|')(.*?)\1/gi, '')
  s = s.replace(/href\s*=\s*("|')\s*javascript:[^"']*\1/gi, 'href="#"')

  const allowedTags = ['b', 'i', 'em', 'strong', 'p', 'br', 'ul', 'ol', 'li', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div']
  s = s.replace(/<\/?([a-z][a-z0-9]*)\b([^>]*)>/gi, (match, tag) => {
    tag = (tag || '').toLowerCase()
    if (allowedTags.includes(tag)) {
      if (match.startsWith('</')) return `</${tag}>`
      if (tag === 'a') {
        const href = (match.match(/href=("|')(.*?)\1/i) || [])[2] || '#'
        return `<a href="${href}">`
      }
      if (tag === 'img') {
        const src = (match.match(/src=("|')(.*?)\1/i) || [])[2] || ''
        const alt = (match.match(/alt=("|')(.*?)\1/i) || [])[2] || ''
        return `<img src="${src}" alt="${alt}">`
      }
      return `<${tag}>`
    }
    return ''
  })

  return s
}
