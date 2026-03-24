const SITE_NAME = 'Dulzia Salamanca Eventos'
const SITE_URL  = 'https://www.dulziasalamancaeventos.com'
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

function setJsonLd(data) {
  let el = document.querySelector('script[type="application/ld+json"]')
  if (!el) { el = document.createElement('script'); el.type = 'application/ld+json'; document.head.appendChild(el) }
  el.textContent = JSON.stringify(data)
}

export function useSeo({ title, description, path, jsonLd } = {}) {
  const fullTitle = title ? `${title} | ${SITE_NAME}` : SITE_NAME
  const desc = description ?? DEFAULT_DESC

  document.title = fullTitle

  setMeta('description', desc)
  setProperty('og:title', fullTitle)
  setProperty('og:description', desc)
  setProperty('og:image', DEFAULT_IMAGE)
  setProperty('og:url', `${SITE_URL}${path ?? ''}`)
  setProperty('og:type', 'website')
  setProperty('og:site_name', SITE_NAME)
  setProperty('og:locale', 'es_ES')
  setMeta('twitter:card', 'summary_large_image')
  setMeta('twitter:title', fullTitle)
  setMeta('twitter:description', desc)
  setMeta('twitter:image', DEFAULT_IMAGE)

  if (path) setCanonical(path)
  if (jsonLd) setJsonLd(jsonLd)
}

// JSON-LD reutilizable para el negocio
export const localBusinessJsonLd = {
  '@context': 'https://schema.org',
  '@type': 'LocalBusiness',
  name: SITE_NAME,
  description: DEFAULT_DESC,
  url: SITE_URL,
  telephone: '+34629991659',
  email: 'info@dulziasalamancaeventos.com',
  address: {
    '@type': 'PostalAddress',
    streetAddress: 'C. Martín Alonso Pedraz, 14',
    addressLocality: 'Salamanca',
    addressRegion: 'Castilla y León',
    postalCode: '37001',
    addressCountry: 'ES',
  },
  geo: {
    '@type': 'GeoCoordinates',
    latitude: 40.9639725,
    longitude: -5.6886876,
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
    'https://www.instagram.com/dulziasala',
    'https://www.tiktok.com/@dulziasalamancaeventos',
  ],
  priceRange: '€€',
  image: `${SITE_URL}/og-image.jpg`,
}
