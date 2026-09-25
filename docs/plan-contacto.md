# Plan: teléfono y email de contacto editables desde el panel

> Plan de implementación — Fecha: 2026-09-25 · Proyecto: dulziasalamanca
> Estado: **ejecutado** (queda la prueba manual desde el panel)

## Contexto

El panel tiene "Ajustes de email" (`/dulzia-panel/ajustes-email`), que hoy gobierna solo el
**destinatario de los avisos** del formulario: el email de destino y el nombre, con respaldo
en el `.env` (`MAILER_TO_EMAIL`, que hoy es `salumvi@gmail.com`).

Los datos que sí ve el visitante están **escritos a mano** en siete sitios: el teléfono
`+34 629 991 659` y el email `info@dulziasalamancaeventos.com` aparecen en el pie, el banner
de CTA, la página de contacto, la de "nosotros", la política de privacidad, la de cookies y
el JSON-LD del negocio (`useSeo.js`). Cambiar de teléfono obliga a tocar siete ficheros, y en
las dos políticas el dato de contacto del titular tiene que ser el real: publicar uno falso no
es solo un descuido, es una cláusula incorrecta.

**Decisiones tomadas:**

1. **Dos claves nuevas en `settings`**: `contact_email` y `contact_phone`. La tabla es
   clave/valor (`Entity/Setting`), así que **no hay migración**: un campo más es una fila más,
   no una columna.
2. **El email que se publica NO es el destinatario de los avisos.** Hoy el destinatario es
   `salumvi@gmail.com`; reutilizarlo habría publicado un Gmail en el pie de la web. Son dos
   ajustes distintos y van en dos bloques distintos del panel: *avisos del formulario*
   (interno) y *datos publicados en la web* (público).
3. **El respaldo de lo público vive en el frontend** (`useContact.js`), no en el `.env`. El
   mailer necesita un destinatario sí o sí (de ahí su valor por defecto), pero el email y el
   teléfono públicos solo se pintan: su "valor de siempre" se queda donde se pinta. Sin
   variables de entorno nuevas (y por tanto sin riesgo de que falte una al desplegar).
4. **`GET /api/contact`** (público, junto al `POST` del formulario) devuelve lo configurado, con
   `null` cuando no hay nada; la web usa entonces sus valores por defecto.
5. **Los enlaces se derivan del teléfono**: `tel:` y `wa.me` salen del mismo número, que hoy
   está escrito a mano en tres sitios y podría desincronizarse del teléfono visible.
6. La página del panel pasa a `/dulzia-panel/ajustes-contacto`, pero **mantiene la URL antigua
   como alias**: un marcador guardado de `/dulzia-panel/ajustes-email` seguiría entrando (y sin
   el alias caería en el comodín que redirige a la portada).

**Bug encontrado de paso (etapa 6):** `useSeo` con solo `jsonLd` pisaba el título y la
descripción. En la portada prerenderizada eso deja `<title>Dulzia Salamanca Eventos</title>` y
la descripción genérica en la página más importante del sitio.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Dominio: claves `contact_email`/`contact_phone`, `ContactDetails` y su resolver | ✅ Hecha |
| 2 | Casos de uso: leer y guardar (con validación del teléfono) | ✅ Hecha |
| 3 | API: `GET/PUT /api/admin/settings/contact-details` y `GET /api/contact` público | ✅ Hecha |
| 4 | Panel: bloque "datos publicados en la web" con email y teléfono | ✅ Hecha |
| 5 | Web pública: `useContact` + los 7 sitios que los pintan + JSON-LD | ✅ Hecha |
| 6 | `useSeo`: una llamada parcial (solo JSON-LD) ya no pisa título ni descripción | ✅ Hecha |
| 7 | Snapshot de prerender + `PROJECT_SPEC.md` + comando de `plan-seo.md` | ✅ Hechos |
| 8 | Tests (PHPUnit + Vitest) | ✅ Hechos (195 + 78 + 90 en verde) |
| 9 | Prueba manual desde el panel | ⬜ Pendiente (requiere sesión de admin) |

## Detalle por etapa

### 1. Dominio

- `Domain/Settings/SettingKey`: `CONTACT_EMAIL = 'contact_email'`, `CONTACT_PHONE = 'contact_phone'`.
- `Domain/Settings/ContactDetails`: `email`, `phone` y de dónde sale cada uno (`*FromSettings`),
  igual que `ContactRecipient` pero para lo que se publica.
- `Domain/Settings/ContactDetailsResolver`: lee las dos claves y, si la tabla falla o no existe
  (migración pendiente), registra el error y devuelve "sin configurar" en vez de romper la web.
  **No hay valor por defecto en el servidor**: el que se pinta vive en el frontend.

### 2. Casos de uso

- `Application/Settings/GetContactDetails/`: query + handler → `{email, phone, email_source,
  phone_source}` para el panel.
- `Application/Settings/UpdateContactDetails/`: command + handler. El command recorta antes de
  validar y acepta vacío (borra la clave = vuelve al valor por defecto de la web). El teléfono se
  valida con `Assert\Regex('/^\+?\d(?:[\s.\-()]?\d){8,14}$/')`: de 9 a 15 dígitos, con prefijo
  opcional, y admite espacios, puntos, guiones y paréntesis.

### 3. API

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| GET | `/api/contact` | — | Datos de contacto publicados (email y teléfono; `null` = sin configurar) |
| GET | `/api/admin/settings/contact-details` | token | Lo configurado + de dónde sale cada campo |
| PUT | `/api/admin/settings/contact-details` | token | Guardar (vaciar = volver al valor por defecto de la web) |

- El `GET` público vive en `ContactController`, junto al `POST` del formulario: es el mismo
  recurso (`/api/contact`), uno se lee y otro se envía. El endpoint público **no** devuelve el
  nombre del destinatario, que es información interna.

### 4. Panel

- `AdminEmailSettingsPage`: dos bloques. *Avisos del formulario* (lo de antes) y *Datos
  publicados en la web* (email y teléfono), con el aviso de que lo que se escriba ahí sale en la
  web y con el valor que se muestra mientras el campo esté vacío.
- `composables/useContactDetails.js` + `apiGetContactDetails`/`apiUpdateContactDetails`.
- Etiquetas "Ajustes de email" → "Ajustes de contacto" en la cabecera de mensajes y en el panel.

### 5. Web pública

- `composables/useContact.js`: estado a nivel de módulo, una petición por sesión y snapshot para
  el prerender (mismo patrón que `useServices`/`useCategories`). Expone `email`, `phone`,
  `telHref` y `whatsappHref` derivados del mismo número.
- Lo usan: `AppFooter`, `CtaBanner`, `ContactoPage`, `NosotrosPage`, `PoliticaPrivacidadPage`,
  `PoliticaCookiesPage` y el JSON-LD de la portada (`localBusinessJsonLd`, que pasa a ser una
  función de los datos de contacto). La meta descripción de `/contacto`, que lleva el teléfono,
  se reescribe cuando llegan los datos.

### 6. Arreglo de `useSeo`

`useSeo({ jsonLd })` (la llamada que hacen los JSON-LD que dependen de la API) ponía el título
genérico y la descripción por defecto, porque el resto de campos iban `undefined`. Ahora una
llamada parcial solo toca los datos estructurados, y el título/descripción/canónica de la página
se quedan como los puso su página.

### 7. Snapshot y documentación

- `frontend/prerender-data/snapshot.json`: clave `contact` (el pie y las legales también se
  prerenderizan). Comando de regeneración actualizado en `docs/plan-seo.md`.
- `docs/PROJECT_SPEC.md`: las dos claves nuevas, los tres endpoints y los sitios que los pintan.

### 8. Tests

- PHPUnit: resolver (con y sin ajustes, y con la tabla caída), command (teléfono válido/ inválido
  y vacío), handler (guarda y borra), y las rutas (admin con token, público sin token).
- Vitest: `useContact` (una petición por sesión, valores por defecto, `tel:`/`wa.me` derivados),
  `useContactDetails` (leer y guardar) y el panel (los dos bloques).

## Verificación

1. `make test-unit`, `make test-integration` y `npm --prefix frontend run test` en verde.
2. `GET /api/contact` devuelve lo configurado (o `null`).
3. `npm run build`: el HTML prerenderizado sale con el teléfono y el email reales, y la portada
   recupera su `<title>` propio.
4. Manual (requiere sesión de admin): cambiar el teléfono en el panel y verlo en el pie, en
   `/contacto`, en las legales y en el botón de WhatsApp; vaciarlo y comprobar que vuelve el de
   siempre.

## Verificación hecha

- `make test-unit` (**195**), `make test-integration` (**78**) y `npm --prefix frontend run test`
  (**90**) en verde.
- `GET /api/contact` en local responde `{"email":null,"phone":null}` (en desarrollo no hay nada
  configurado), que es lo que hace que la web caiga a sus valores de siempre.
- `npm run build`: las 15 rutas prerenderizadas. En el HTML de la portada el `<title>` ya es
  «Carrito Hot Dog, Candy Bar, Photocall y más en Salamanca | Dulzia Salamanca Eventos» (antes
  el genérico, por el bug de la etapa 6), el JSON-LD lleva `"telephone":"+34629991659"` y
  `"email":"info@dulziasalamancaeventos.com"` derivados de `useContact`, y el pie y las páginas
  pintan `tel:+34629991659`, `wa.me/34629991659` y el email.
- Snapshot regenerado con la clave `contact` (comando actualizado en `docs/plan-seo.md`).

## Pendiente

- **Prueba manual desde el panel** (`/dulzia-panel/ajustes-email`): guardar teléfono y email
  públicos y verlos en la web; vaciarlos y ver que vuelven los valores por defecto.
- **Producción**: no hay migración que ejecutar (la tabla es clave/valor). Sí conviene rellenar
  los dos campos desde el panel y regenerar el snapshot de prerender para que el HTML estático
  salga con los datos nuevos.
