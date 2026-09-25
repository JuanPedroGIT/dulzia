/**
 * Mide la portada en un viewport de móvil y dice si algo se sale de la pantalla.
 * Se usa el Chromium que ya descarga el prerender. Es un script de comprobación
 * puntual: no forma parte de la app.
 */
import puppeteer from 'puppeteer'

const URL = process.argv[2] ?? 'http://localhost:4173/'
const WIDTH = Number(process.argv[3] ?? 360)

const browser = await puppeteer.launch({ headless: true })
const page = await browser.newPage()
await page.setViewport({ width: WIDTH, height: 800, deviceScaleFactor: 2, isMobile: true })
await page.goto(URL, { waitUntil: 'networkidle0' })
await new Promise(resolve => setTimeout(resolve, 1000))

const report = await page.evaluate(() => {
  const overflowing = []
  const viewport = document.documentElement.clientWidth

  for (const el of document.querySelectorAll('body *')) {
    const box = el.getBoundingClientRect()
    if (box.width > 0 && box.right > viewport + 1) {
      overflowing.push({
        tag: el.tagName.toLowerCase(),
        class: el.className?.toString().slice(0, 70) ?? '',
        right: Math.round(box.right),
        width: Math.round(box.width),
      })
    }
  }

  const cta = document.querySelector('.reviews__cta .btn')
  const ctaBox = cta?.getBoundingClientRect()

  return {
    viewport,
    scrollWidth: document.documentElement.scrollWidth,
    cta: ctaBox ? { left: Math.round(ctaBox.left), right: Math.round(ctaBox.right), width: Math.round(ctaBox.width), height: Math.round(ctaBox.height) } : null,
    overflowing: overflowing.slice(0, 8),
  }
})

console.log(JSON.stringify(report, null, 2))
await browser.close()
