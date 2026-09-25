import { CONTACT_DEFAULTS, phoneDigits } from '@/composables/useContact.js'

const SITE_NAME = 'Dulzia Salamanca Eventos'
export const SITE_URL = 'https://www.dulziasalamancaeventos.com'
const DEFAULT_IMAGE = `${SITE_URL}/og-image.jpg`
const DEFAULT_DESC  = 'Carrito de Hot Dog, Candy Bar, Fuente de Chocolate, Photocall, Glitter Bar y mucho más para bodas, cumpleaños y eventos en Salamanca.'

function setMeta(name, content) {
  if (!content) return
  let el = document.querySelector(`meta[name="${name}"]`)
  if (!el) { el = document.createElement('meta'); el.name = name; document.head.appendChild(el) }
  el.content = content
}

function setProperty(property, content) {
  if (!content) return
  let el = document.querySelector(`meta[property="${property}"]`)
  if (!el) { el = document.createElement('meta'); el.setAttribute('property', property); document.head.appendChild(el) }
  el.content = content
}

function setCanonical(path) {
  let el = document.querySelector('link[rel="canonical"]')
  if (!el) { el = document.createElement('link'); el.rel = 'canonical'; document.head.appendChild(el) }
  el.href = `${SITE_URL}${path}`
}

function setJsonLd(data, key = 'main') {
  let el = document.querySelector(`script[data-jsonld="${key}"]`)
  if (!el) {
    el = document.createElement('script')
    el.type = 'application/ld+json'
    el.dataset.jsonld = key
    document.head.appendChild(el)
  }
  el.textContent = JSON.stringify(data)
}

export function useSeo({ title, description, path, jsonLd, jsonLdKey } = {}) {
  // Llamada parcial: solo datos estructurados. Los JSON-LD que dependen de la
  // API llegan cuando la página ya ha fijado su título y su descripción, y no
  // deben pisarlos con los genéricos (era lo que pasaba: la portada acababa con
  // el título "Dulzia Salamanca Eventos" a secas).
  if (title === undefined && description === undefined && path === undefined) {
    if (jsonLd) setJsonLd(jsonLd, jsonLdKey ?? 'main')
    return
  }

  const fullTitle = title ? `${title} | ${SITE_NAME}` : SITE_NAME
  const desc = description ?? DEFAULT_DESC

  document.title = fullTitle

  setMeta('description', desc)
  setProperty('og:title', fullTitle)
  setProperty('og:description', desc)
  setProperty('og:image', `${SITE_URL}${DEFAULT_IMAGE}`)
  setProperty('og:url', `${SITE_URL}${path ?? ''}`)
  setProperty('og:type', 'website')
  setProperty('og:site_name', SITE_NAME)
  setProperty('og:locale', 'es_ES')
  setMeta('twitter:card', 'summary_large_image')
  setMeta('twitter:title', fullTitle)
  setMeta('twitter:description', desc)
  setMeta('twitter:image', `${SITE_URL}${DEFAULT_IMAGE}`)

  if (path) setCanonical(path)
  if (jsonLd) setJsonLd(jsonLd, jsonLdKey ?? 'main')
}

// JSON-LD reutilizable para el negocio. El teléfono y el email salen de los
// datos de contacto configurados en el panel (useContact), no de literales: si
// el negocio cambia de número, el dato estructurado cambia con él.
export function localBusinessJsonLd(contact = CONTACT_DEFAULTS) {
  const email = contact.email || CONTACT_DEFAULTS.email
  const digits = phoneDigits(contact.phone || CONTACT_DEFAULTS.phone)

  return {
    '@context': 'https://schema.org',
    '@type': 'LocalBusiness',
    name: SITE_NAME,
    description: DEFAULT_DESC,
    url: SITE_URL,
    telephone: `+${digits}`,
    email,
    areaServed: {
      '@type': 'City',
      name: 'Salamanca',
    },
    openingHoursSpecification: [
      {
        '@type': 'OpeningHoursSpecification',
        dayOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
        opens: '07:00',
        closes: '18:00',
      },
    ],
    sameAs: [
      'https://www.tiktok.com/@dulziasalamancaeventos',
      'https://www.facebook.com/profile.php?id=61569180747614',
      'https://www.instagram.com/dulziasala',
      `https://wa.me/${digits}`,
    ],
    priceRange: '€€',
    image: `${SITE_URL}/og-image.jpg`,
  }
}

// Catálogo de servicios como ItemList de Service (para /servicios y la home)
export function servicesCatalogJsonLd(services) {
  return {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    name: 'Servicios de Dulzia Salamanca Eventos',
    itemListElement: services.map((s, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      item: {
        '@type': 'Service',
        name: s.name,
        description: s.description,
        url: `${SITE_URL}/servicios/${s.id}`,
        ...(s.image ? { image: s.image } : {}),
        provider: { '@type': 'LocalBusiness', name: SITE_NAME, url: SITE_URL },
      },
    })),
  }
}

// Migas de pan (p. ej. Inicio → Servicios → Nombre del servicio)
export function breadcrumbJsonLd(items) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((it, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: it.name,
      item: it.url,
    })),
  }
}
