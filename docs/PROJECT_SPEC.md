# PROJECT SPEC — Dulzia Salamanca Eventos (Symfony + Vue 3)

> Especificación del proyecto: arquitectura, stack y patrones realmente implementados.
> Sirve como referencia para mantener el proyecto y como plantilla para proyectos nuevos.

---

## Concepto General

Web de marketing de Dulzia Salamanca Eventos: catálogo público de servicios con fotos
y formulario de contacto, más un panel de administración autenticado para gestionar
servicios, fotos y mensajes recibidos. Backend API REST en Symfony, frontend SPA en Vue.

---

## Stack Tecnológico

### Backend
| Elemento | Tecnología |
|---|---|
| Framework | Symfony 7 |
| Lenguaje | PHP 8.3 |
| Arquitectura | Hexagonal + CQRS ligero + SOLID |
| ORM | Doctrine ORM 3 + Doctrine Migrations |
| Base de datos | PostgreSQL 16 (contenedor compartido de infra) |
| Email transaccional | Brevo REST API (curl) |
| Almacenamiento de archivos | Cloudflare R2 (S3 API, aws-sdk-php) |
| Autenticación | Token único de admin en BD (64-hex, expiración 8 h) — sin JWT |
| Contraseñas | bcrypt vía `password_hash()` PHP nativo |
| CORS | NelmioCorsBundle |
| Tests | PHPUnit 11 (unit + integración WebTestCase) |

> No hay procesamiento asíncrono: no se usa Messenger ni colas. El stack de infra
> incluye Redis para otros proyectos, pero esta app no lo consume.

### Frontend
| Elemento | Tecnología |
|---|---|
| Framework | Vue 3 (Composition API) |
| Bundler | Vite 5 |
| Routing | Vue Router 4 (guards por `meta.requiresAuth`) |
| Estado | Composable `useAuth` + `localStorage` (sin stores Pinia hoy) |
| Estilos | SCSS con variables y mixins propios |
| Tests | Vitest |
| Servidor | Nginx (con `envsubst` para inyección de variables) |

### Infraestructura
| Elemento | Tecnología |
|---|---|
| Local | Docker Compose (servicios dulzia-backend y dulzia-frontend en la red compartida `shared-network`) |
| Producción | VPS compartido media-tools (`docker-compose.prod.yml` + nginx de infra + túnel cloudflared) |
| Base de datos | `shared-postgres-db` (proyecto infra compartido, BD propia `dulzia`) |
| Reverse proxy | Nginx de infra (routing por `server_name`) + nginx del contenedor frontend (SPA + proxy `/api`) |

---

## Estructura de Directorios

```
dulziasalamanca/
├── .env                        # Secrets raíz — .gitignored
├── .env.example                # Placeholders sin secretos
├── docker-compose.yml          # Dev (2 servicios, red externa shared-network)
├── docker-compose.prod.yml     # Producción (servidor compartido)
├── Makefile                    # Comandos de desarrollo, tests y producción
├── README.md
├── docs/                      # PROJECT_SPEC.md, PRD_MENSAJES_ADMIN.md
├── backend/                    # Symfony 7
│   ├── config/
│   │   ├── packages/           # doctrine.yaml, framework.yaml, nelmio_cors.yaml... + test/
│   │   ├── services.yaml       # Alias puertos→adaptadores
│   │   └── services_test.yaml  # Dobles de test (mailer y storage)
│   ├── migrations/             # Una migration por cambio de esquema
│   ├── public/
│   ├── src/
│   │   ├── Application/        # Commands/Queries + Handlers — una carpeta por caso de uso
│   │   │   ├── AdminAuth/      #   Login, Logout
│   │   │   ├── Contact/        #   SubmitContact, ListMessages, GetMessage,
│   │   │                       #   MarkMessageRead, DeleteMessage
│   │   │   └── Service/        #   CreateService, UpdateService, ActivateService,
│   │   │                       #   AddPhoto, UpdatePhoto, DeletePhoto, ListServices,
│   │   │                       #   GetService, ListCatalogServices, GetCatalogService,
│   │   │                       #   ServiceCommandFactory (parseo body→command)
│   │   ├── Command/            # app:admin:init
│   │   ├── Controller/         # Delgados: ServiceController, ContactController,
│   │   │                       #   AdminAuthController, AdminServiceController,
│   │   │                       #   AdminPhotoController, AdminContactController,
│   │   │                       #   HealthController
│   │   ├── Domain/             # Puertos (interfaces) y excepciones — una carpeta por feature
│   │   │   ├── Admin/          #   AdminUserRepositoryInterface, AdminTokenStoreInterface,
│   │   │   │                   #   InvalidCredentialsException
│   │   │   ├── Contact/        #   ContactRepositoryInterface, MailerInterface
│   │   │   ├── Service/        #   ServiceRepositoryInterface, ServiceExampleRepositoryInterface
│   │   │   ├── Storage/        #   FileStorageInterface, InvalidFileException
│   │   │   └── Shared/         #   NotFoundException, InvalidInputException,
│   │   │                       #   HttpMappableExceptionInterface
│   │   ├── Entity/             # Service, ServiceExample, ContactSubmission, AdminUser, AdminToken
│   │   ├── EventListener/      # ApiExceptionListener + AdminAuthListener
│   │   └── Infrastructure/
│   │       ├── Email/          # BrevoMailer (transporte) + ContactMailRenderer (plantillas)
│   │       ├── Repository/     # DoctrineXxxRepository
│   │       ├── Security/       # DatabaseAdminTokenStore
│   │       └── Storage/        # CloudflareR2Storage (+ LocalFileStorage de rollback)
│   ├── tests/
│   │   ├── Unit/               # Un test por handler, entidades, storage, security, listeners
│   │   ├── Integration/        # WebTestCase por controller sobre BD dulzia_test
│   │   ├── Support/            # TestFactory (fixtures)
│   │   └── TestDoubles/        # NullMailer, FakeFileStorage
│   ├── Dockerfile              # Multi-stage (dev con xdebug, prod con opcache)
│   └── composer.json
│
└── frontend/                   # Vue 3
    ├── src/
    │   ├── pages/              # Home, Servicios, ServicioDetalle, Nosotros, Contacto,
    │   │                       # PoliticaCookies + admin/ (Login, Dashboard, Services,
    │   │                       # ServiceDetail, Categories, EmailSettings,
    │   │                       # ContactSettings, Messages, MessageDetail)
    │   ├── components/
    │   │   ├── ui/             # BaseButton, BaseInput, BaseTextarea, BaseSpinner,
    │   │   │                   # BaseFileUpload, ImageCropperModal
    │   │   ├── layout/         # NavBar, AppFooter
    │   │   └── features/       # HeroSection, StatsBar, ServicesOverview, ServiceCard,
    │   │                       # ReviewsSection, ContactForm, CtaBanner, CookieBanner
    │   ├── composables/        # useAuth, useContactForm, useMessages, useSeo, useServices
    │   ├── services/           # api.js (cliente base) + contactService.js + adminService.js
    │   ├── router/             # index.js con guards de navegación
    │   └── styles/             # variables.scss, mixins.scss, main.scss
    ├── __tests__/              # Vitest (useContactForm.spec.js, useMessages.spec.js)
    ├── Dockerfile
    └── nginx.conf              # SPA fallback + proxy /api + envsubst
```

---

## Arquitectura Backend (Hexagonal + CQRS + SOLID)

### Flujo de una petición
```
HTTP Request
  → Controller (deserializa + valida input básico; construye command/query)
    → Application/CommandHandler (orquesta dominio e infraestructura)
      → Domain (puertos y excepciones)
      → Infrastructure (DB, email, storage, tokens)
  → Controller (serializa respuesta JSON)
```

### Reglas
- **Controllers**: nunca lógica de negocio. Solo deserializar, validar input básico
  y responder. Sin try/catch de excepciones de dominio (las mapea el listener).
  Agrupados por agregado: `AdminServiceController` (Service) y `AdminPhotoController`
  (ServiceExample) — SRP.
- **Factories de comandos**: el parseo del body JSON vive en factories
  (`ServiceCommandFactory`), no duplicado en los controllers.
- **CommandHandlers** (`Application/`): no saben nada de HTTP. Un handler por caso
  de uso, una carpeta por feature. Solo dependen de puertos del Domain (DIP).
- **Domain**: solo puertos (interfaces) y excepciones. Sin dependencias de
  infraestructura.
- **Excepciones de dominio**: implementan `HttpMappableExceptionInterface`
  (`getStatusCode()`). El `ApiExceptionListener` no cambia cuando se añade una
  excepción nueva — principio abierto/cerrado.
- **Infrastructure**: implementa los puertos del dominio; el wiring es por alias
  en `services.yaml`.
- **Validación de entrada**: las reglas viven en los commands (`#[Assert]` en
  `SubmitContactCommand`) — única fuente de verdad. El controller valida y lanza
  `ValidationFailedException` → el listener responde 422 con errores por campo.

### Mapeo de errores (`ApiExceptionListener`)
| Excepción | Respuesta |
|---|---|
| `ValidationFailedException` | 422 `{errors: {campo: [mensajes]}}` |
| `HttpMappableExceptionInterface` (`NotFoundException` 404, `InvalidFileException` 400, `InvalidInputException` 400, `InvalidCredentialsException` 401) | status propio, `{error: mensaje}` |
| `HttpExceptionInterface` | status propio, `{error: mensaje}` |
| Cualquier otra | 500 `{error: 'Error interno del servidor'}` |

### Autenticación admin
- `POST /api/admin/login` → verifica bcrypt contra `admin_user` → genera token
  64-hex almacenado en `admin_token` (expiración 8 h, un solo token válido a la vez).
- `AdminAuthListener` exige `Authorization: Bearer <token>` en todas las rutas
  `/api/admin` excepto el login.
- `POST /api/admin/logout` borra todos los tokens.
- La lógica vive en `Application/AdminAuth/Login` y `Logout` (handlers), no en
  el controller.

### IDs de servicio y de categoría
`ServiceIdGenerator` y `CategoryIdGenerator`: slug del nombre más sufijo numérico
(`time()`) si el ID ya existe. El slug lo hace `Application\Shared\Slug`, compartido por
los dos para que un nombre se convierta igual en los dos sitios: pasa los acentos a ASCII
(`Animación` → `animacion`, no `animaci-n`) y cambia lo que no sea letra o número por un
guion. El identificador no se edita después: los servicios referencian la categoría por él.

---

## Arquitectura Frontend (Vue 3 Composition API)

### Flujo de datos
```
Page Component
  → Composable (useXxx.js)    ← toda la lógica de negocio
    → Service (xxxService.js) ← llamadas HTTP puras
      → api.js                ← cliente base (añade JWT/token, maneja 401)
```

### Convenciones
- **Pages**: orquestan composables; no contienen lógica de negocio directamente.
- **Composables**: devuelven `{ state, actions }`. Son la unidad testeble del frontend.
- **Services**: funciones puras sin estado; solo fetch.
- **Auth**: `useAuth` + `localStorage('admin_token')`; no hay stores Pinia.

### Catálogo compartido (`useServices`)

El catálogo público es el mismo durante toda la sesión, así que su estado vive a
nivel de **módulo** (no dentro de cada componente): portada, listado y fichas de
servicio comparten una única `GET /api/services`.

- `fetchAll()` no repite si ya está cargado (`loaded`), deduplica la petición si
  dos páginas montan a la vez (`pending`) y acepta `{ force: true }` para forzar.
- **La ficha de servicio no pide su detalle**: `GET /api/services` devuelve
  `Service::toArray()`, exactamente lo mismo que `GET /api/services/{id}`, así que
  la página busca el servicio en la lista ya cargada. Navegar entre servicios no
  genera ninguna petición.
- Como contrapartida, una sesión abierta no ve cambios del catálogo hasta que
  recarga (o hasta un `fetchAll({ force: true })`).

### Qué sale en la portada

Lo decide el check **Portada** de la tabla del panel (`service.is_featured`), que se
guarda al instante contra `POST /api/admin/services/{id}/featured`. Marcado, el
servicio aparece en las dos zonas de la portada:

- las tarjetas del **hero** (`HeroSection`, máximo 6, con el nombre del servicio como
  etiqueta, y cada una enlazando a su ficha `/servicios/{id}`), y
- la parrilla de **"Servicios que enamoran"** (`ServicesOverview`, todas las marcadas;
  sin ninguna, la sección no se pinta).

Antes eran dos criterios distintos fijados en el código: una lista de ids escrita a
mano en el hero y los 6 primeros por `sort_order` en la parrilla.

La **barra de cifras** (`StatsBar`, debajo del hero) cuenta los servicios del catálogo
que ya carga la portada: el número sale de `services.length`, no de un literal, y hasta
que llega el catálogo se deja el hueco en vez de un "0". Las demás cifras (eventos,
satisfacción) y la cobertura ("Salamanca y alrededores", "nos movemos por toda la
península") son texto fijo.

La sección de **reseñas** (`ReviewsSection`, entre "Servicios que enamoran" y el banner de
presupuesto) es la prueba social de la portada: la nota media del perfil de Google (4,9 ·
146 reseñas), tarjetas con la inicial del autor, la antigüedad, las estrellas y el texto, y
un enlace al perfil. Las reseñas son reales (una constante en el componente, de momento) y
sus textos van literales. La antigüedad no está escrita: cada reseña guarda su fecha y
`ago()` la convierte en "hace 3 meses" / "hace 1 año" al pintar, así que envejece sola. Con
`rating` en `null` la fila de la nota no se pinta: es un dato público y comprobable. No
emite datos estructurados, por lo mismo que el plan de SEO descartó `aggregateRating`. Ver
`plan-resenas.md`.

### Categorías

Son un dato, no una lista en el código: la tabla `category` (nombre, emoji y orden) se
gestiona en `/dulzia-panel/categorias` y la consumen:

- `AdminServicesPage` (desplegable del modal de secciones y etiqueta de la tabla),
- `ServiciosPage` (las pestañas del catálogo),
- `ServiceCard` y `ServicioDetallePage` (la etiqueta de cada sección),

todas a través de `useCategories` (mismo patrón de caché por sesión que `useServices`,
y con `snapshot.categories` para el prerender).

- **Borrar una categoría en uso se bloquea** (409 con el número de secciones).
- **Sin clave foránea**: el esquema se deriva de los mapeos de Doctrine y una FK puesta a
  mano la borraría la siguiente `migration-diff`. La integridad la garantiza el backend:
  `CategoryResolver` rechaza (400) una categoría inexistente al crear o editar un servicio.

### Datos de contacto publicados (`useContact`)

El teléfono y el email que salen en la web —pie, `/contacto`, `/nosotros`, las tres páginas
legales y el JSON-LD del negocio— los sirve `GET /api/contact` y los comparte `useContact`
(mismo patrón de caché por sesión que el catálogo, y con `snapshot.contact` para el
prerender). Se editan en el panel, en `/dulzia-panel/ajustes-contacto` (claves `contact_email`
y `contact_phone` de la tabla `setting`).

- **No son el destinatario de los avisos del formulario**: aquel es un ajuste interno
  (`contact_recipient_email`, con el valor del `.env` como respaldo) y publicarlo sería
  filtrar a qué dirección llegan los mensajes. Por eso son dos bloques distintos del panel y
  dos recursos de la API.
- **Sin valor por defecto en el servidor**: si el panel no tiene nada, la API devuelve `null`
  y la web usa `CONTACT_DEFAULTS` (`useContact.js`). El respaldo de lo que se publica vive
  donde se pinta; el del mailer sí está en el `.env`, porque un envío sin destinatario no se
  puede hacer y un teléfono que solo se pinta, sí.
- **`tel:` y `wa.me` se derivan del mismo número** (en solo dígitos), así que cambiar el
  teléfono en el panel mueve también el enlace de WhatsApp.
- El JSON-LD del negocio (`localBusinessJsonLd`) es una función de estos datos, no un objeto
  con literales: se reescribe cuando llegan.

### Routing
- Rutas públicas: `/`, `/servicios`, `/servicios/:id`, `/nosotros`, `/contacto`, `/cookies`.
- Rutas admin protegidas (`meta.requiresAuth`): `/dulzia-panel` (índice: la tarjeta del
  buzón de mensajes arriba con su contador y, debajo, las cuatro zonas de edición),
  `/dulzia-panel/login`, `/dulzia-panel/servicios`,
  `/dulzia-panel/servicios/:id` (fotos), `/dulzia-panel/categorias`,
  `/dulzia-panel/ajustes-email` (a dónde llegan los avisos),
  `/dulzia-panel/ajustes-contacto` (lo que se publica), `/dulzia-panel/mensajes` y
  `/dulzia-panel/mensajes/:id`. Cada página tiene su enlace de vuelta: "← Panel" en las
  zonas, y las jerarquías internas se conservan (fotos → "Secciones", detalle del mensaje
  → "Mensajes").
- La lista de mensajes tiene **filtro de leídos / sin leer** en pestañas con sus
  contadores. Filtra el servidor (`?filter=`), no el cliente: la lista está paginada y
  filtrar la página visible daría totales falsos.
- Guard: si no hay token en localStorage → redirect al login.

### Comunicación con el backend
- **Nginx actúa como proxy**: el frontend solo habla con su mismo origen — sin CORS
  en producción (el `CORS_ALLOW_ORIGIN` cubre accesos directos y dev).
- Nginx reenvía `/api/*` al backend (`BACKEND_UPSTREAM`).
- En desarrollo, Vite replica el mismo comportamiento con proxy a `backend:8000`.
- `api.js`: inyecta `Authorization: Bearer <token>` y maneja 401.

---

## Docker Compose (local)

```yaml
services:
  dulzia-backend:   # Symfony, puerto 8000 publicado, alias "backend" en la red
  dulzia-frontend:  # Nginx + Vue (dev), puerto 5173 publicado
networks:
  shared-network:   # externa: la crea el proyecto infra (postgres, redis, nginx, cloudflared)
```

- La BD vive en `shared-postgres-db` (proyecto infra); `DATABASE_URL` la inyecta compose.
- El `.env` raíz se inyecta a los contenedores vía `env_file`.
- Sin volúmenes de uploads: las fotos van a Cloudflare R2.

---

## Variables de Entorno

### Raíz (`.env`, gitignored)
| Variable | Descripción |
|---|---|
| `APP_ENV` | `dev` local / `prod` en producción |
| `APP_SECRET` | String aleatorio de 32+ chars (Symfony) |
| `CORS_ALLOW_ORIGIN` | Regex de orígenes permitidos |
| `APP_URL` | URL base pública de la web |
| `DEFAULT_URI` | URL base del frontend (links en emails) |
| `BREVO_API_KEY` | API key de Brevo (email) |
| `MAILER_TO_EMAIL` / `MAILER_TO_NAME` | Destinatario de la notificación de contacto (obligatoria: sin ella el backend no arranca) |
| `MAILER_FROM_EMAIL` / `MAILER_FROM_NAME` | Remitente de la notificación (obligatoria) |
| `MAILER_CONFIRM_FROM_EMAIL` / `MAILER_CONFIRM_FROM_NAME` | Remitente de la confirmación al usuario (obligatoria) |
| `DULZIA_DB_PASS` | Contraseña del usuario `dulzia` en el postgres compartido |
| `TRUSTED_PROXIES` / `TRUSTED_HEADERS` | Proxy de confianza (cloudflared → nginx → backend) |
| `R2_ACCOUNT_ID` | Account ID de Cloudflare |
| `R2_ACCESS_KEY_ID` | Access Key ID del token R2 (Object Read & Write) |
| `R2_ACCESS_KEY_SECRET` | Secret Access Key del token R2 |
| `R2_BUCKET_NAME` | Nombre del bucket R2 |
| `R2_PUBLIC_URL` | URL pública del bucket (r2.dev o dominio propio) |
| `VITE_API_URL` | Vacío = rutas relativas (proxy por nginx/Vite) |

### Backend (`backend/.env`)
Solo passthrough `${VAR}` sin valores reales (template local, gitignored).
El `.env` raíz inyecta los valores vía `env_file` del compose. **Único `.env`
de configuración**: los valores reales (incluidos los emails `MAILER_*`)
solo se tocan en el `.env` raíz; `backend/.env` no se modifica a mano.

### Gestión de secrets
- `.env` en la raíz contiene los secrets reales → **nunca commitear**.
- `.env.example` lleva los placeholders.
- En el servidor compartido, el `.env` se copia manualmente (ver Despliegue).

---

## Seguridad

| Medida | Implementación |
|---|---|
| Contraseñas | bcrypt (`password_hash` PHP nativo) |
| Token admin | 64-hex con expiración de 8 h en BD |
| Anti-enumeración | Login devuelve siempre el mismo mensaje genérico (`InvalidCredentialsException`) |
| XSS en emails | `htmlspecialchars` en `ContactMailRenderer` |
| Headers HTTP | X-Frame-Options, X-Content-Type-Options, Referrer-Policy vía Nginx |
| CORS | NelmioCorsBundle con allowlist por regex configurable |

---

## Emails Transaccionales

- **Proveedor**: Brevo REST API v3 (`BrevoMailer`, transporte con curl).
- **Plantillas**: `ContactMailRenderer` (HTML con escapado), separadas del transporte.
- **Emails del formulario de contacto**:
  1. Notificación al negocio (mensaje, email, teléfono, tipo de evento).
  2. Confirmación al remitente.
- **Fallo de email no fatal**: el mensaje se guarda igualmente y el handler
  captura el error (el campo `email_sent` registra si se envió).

---

## Base de Datos

### Entidades (Doctrine)
```
admin_user         id, username (unique), password_hash
admin_token        id, token (unique), expires_at
category           id (string slug, PK), name, emoji (para las pestañas del catálogo,
                   NULL = sin emoji), sort_order
service            id (string slug, PK), name, emoji, image_url (foto de la sección en
                   R2, NULL = se usa la 1ª foto de su galería y, si no hay, el emoji),
                   thumbnail_url (miniatura de image_url, NULL = se sirve image_url),
                   description, features (json), category (id de category, sin FK: lo
                   valida el backend), sort_order, is_active,
                   is_featured (destacado en la portada, se marca desde el panel)
service_example    id (string 32-hex), service_id (FK), title, description,
                   image_url (URL completa R2 o externa),
                   thumbnail_url (miniatura, NULL = se sirve image_url), sort_order
contact_submission id (string 32-hex), name, email, phone, event_type, message,
                   ip_address, submitted_at, email_sent, email_sent_at, read_at
setting            key (PK), value, updated_at — ajustes del panel sin esquema propio,
                   para no añadir columnas por cada cosa configurable
```

### Imágenes: dos versiones por foto

El recortador del panel (`ImageCropperModal.vue`) exporta **dos JPEG del mismo recorte**
en la misma petición: `image` (1600 px de ancho, la que se abre en el carrusel) y
`thumbnail` (640 px, la que sirven tarjetas, rejillas y listas). El backend solo los
almacena — no manipula imágenes (no hay GD ni Imagick en la imagen Docker).

- Cada versión es **un objeto independiente en R2**: hay que borrar las dos al
  reemplazar o eliminar una foto.
- La API emite `thumbnail` **resuelta** (`thumbnail_url ?? image_url`), así el
  frontend nunca tiene un hueco: las fotos subidas antes de existir las miniaturas
  siguen sirviéndose a tamaño completo hasta que se resuban.
- El lightbox pide solo la diapositiva actual y sus vecinas: con las fotos grandes,
  montar la galería entera serían varios MB de golpe.

### Convenciones de migración
- Una migration por cambio de esquema.
- Nomenclatura: `VersionYYYYMMDDNNNNNN.php`.
- Nunca modificar una migration ya ejecutada en producción.

---

## API — Endpoints

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| GET | `/health` | — | Health check |
| GET | `/api/categories` | — | Categorías para la web (pestañas del catálogo y etiquetas) |
| GET | `/api/services` | — | Catálogo público (solo activos, con fotos) |
| GET | `/api/services/{id}` | — | Detalle de servicio (404 si no existe o está inactivo). La web **no lo llama**: el catálogo ya trae la ficha completa y el detalle se resuelve en memoria (ver "Catálogo compartido") |
| POST | `/api/contact` | — | Formulario de contacto (422 con errores por campo) |
| GET | `/api/contact` | — | Datos de contacto publicados: `{email, phone}`, `null` = sin configurar |
| GET | `/api/admin/settings/contact-recipient` | token | Destinatario de los avisos + de dónde sale cada campo |
| PUT | `/api/admin/settings/contact-recipient` | token | Guardar destinatario (vaciar = valor por defecto del `.env`) |
| GET | `/api/admin/settings/contact-details` | token | Email y teléfono publicados + de dónde sale cada campo |
| PUT | `/api/admin/settings/contact-details` | token | Guardar los datos publicados (vaciar = valor por defecto de la web) |
| POST | `/api/admin/login` | — | Login → `{token}` |
| POST | `/api/admin/logout` | token | Invalida todos los tokens |
| GET | `/api/admin/services` | token | Lista completa (incluye inactivos) |
| GET | `/api/admin/services/{id}` | token | Detalle con fotos y sort_order |
| POST | `/api/admin/services` | token | Crear servicio (201, id slug). Multipart con `image` (foto de la sección) + `thumbnail`, o JSON |
| PUT · POST | `/api/admin/services/{id}` | token | Actualizar servicio: PUT con JSON, POST con multipart (`image`, `thumbnail`, `features[]`, `removeImage`) |
| DELETE | `/api/admin/services/{id}` | token | Desactivar (soft delete) |
| POST | `/api/admin/services/{id}/activate` | token | Reactivar |
| POST | `/api/admin/services/{id}/featured` | token | Destacar/quitar de la portada (`{"featured": bool}`, idempotente) |
| GET | `/api/admin/categories` | token | Lista con `serviceCount` (cuántas secciones usan cada una) |
| POST | `/api/admin/categories` | token | Crear (201, id slug del nombre) |
| PUT | `/api/admin/categories/{id}` | token | Editar nombre, emoji y orden (el id no se cambia) |
| DELETE | `/api/admin/categories/{id}` | token | Borrar (409 si hay secciones usándola) |
| POST | `/api/admin/services/{serviceId}/photos` | token | Añadir foto (multipart `image` + `thumbnail`, o `imageUrl` externa) |
| POST | `/api/admin/photos/{photoId}` | token | Actualizar foto |
| DELETE | `/api/admin/photos/{photoId}` | token | Borrar foto (y su archivo en R2) |
| GET | `/api/admin/messages` | token | Mensajes paginados: `?page=1&filter=all\|unread\|read` → `{items, page, totalPages, total, filter, counts:{all,unread,read}}`. `total` es el del filtro (lo que pagina la tabla); `counts` son los globales de las pestañas |
| GET | `/api/admin/messages/{id}` | token | Detalle de mensaje (404 si no existe) |
| POST | `/api/admin/messages/{id}/read` | token | Marcar leído |
| POST | `/api/admin/messages/{id}/unread` | token | Marcar no leído |
| DELETE | `/api/admin/messages/{id}` | token | Borrar mensaje |

---

## Despliegue en Producción (servidor compartido media-tools)

1. Subir el código al servidor (`/home/ubuntu/apps/dulzia`).
2. Copiar el `.env` con los secrets reales — debe incluir las variables
   `MAILER_*` de los emails de contacto (ver `.env.example`): sin ellas el
   backend no arranca.
3. Añadir/actualizar el vhost en el nginx de infra (`conf.d/dulzia.conf` → `server_name`).
4. `make prod-up` (build + arranque con `docker-compose.prod.yml`).
5. `make migrate` (o equivalente en el contenedor prod) — las migraciones van en la imagen.
6. Crear/resetear el admin: `app:admin:init`.
7. Los cambios de código requieren redeploy (`prod-up`); el `.env` también se copia manual.

El servidor compartido es el destino activo y el único documentado.

---

## Makefile — Comandos

```bash
make up               # Levantar los contenedores dev
make down             # Parar contenedores
make rebuild          # Rebuild completo (borra volúmenes)
make logs             # Logs del backend
make shell            # Shell en el contenedor backend
make migrate          # Ejecutar migraciones pendientes
make migration-diff   # Generar migration por cambios en entidades
make cache-clear      # Limpiar caché Symfony
make test             # Todos los tests (PHP + frontend)
make test-setup       # Crear la BD de test dulzia_test (una vez)
make test-unit        # Tests unitarios PHPUnit
make test-integration # Tests de integración PHPUnit
make test-frontend    # Tests Vitest
make install          # Instalar dependencias
make composer-require pkg="vendor/nombre"  # Añadir paquete PHP
make sync-vendor      # Sincronizar vendor del contenedor → local
make prod-up          # Deploy en producción (docker-compose.prod.yml)
make prod-down        # Parar producción
make prod-logs        # Logs de producción
```

---

## Tests

### Backend (PHPUnit)
```
tests/Unit/Application/{Feature}/   # Un test por CommandHandler (+ factories, generadores)
tests/Unit/Entity/                  # Comportamiento de entidades
tests/Unit/Infrastructure/          # Storage (R2 con MockHandler, local), Email, Security
tests/Unit/EventListener/           # ApiExceptionListener (mapeos)
tests/Integration/Controller/       # Tests HTTP WebTestCase
tests/Support/TestFactory.php       # Fixtures
tests/TestDoubles/                  # NullMailer, FakeFileStorage
```

- **Unit**: handlers con repositorios mockeados, sin base de datos. El storage R2 se
  testea con `Aws\MockHandler` (sin red real).
- **Integration**: WebTestCase contra la BD dedicada `dulzia_test` (schema creado
  desde los mappings de Doctrine y truncado entre tests; `make test-setup` la crea).
  Los adaptadores reales se sustituyen por dobles (`services_test.yaml`): los tests
  nunca llaman a Brevo ni a R2.

### Frontend (Vitest)
```
__tests__/useContactForm.spec.js   # Un test por composable
```

---

## Patrones y Decisiones de Diseño

1. **Hexagonal + CQRS ligero**: separa HTTP, lógica y persistencia. Permite testear
   handlers sin base de datos real.
2. **SOLID aplicado**:
   - S: un handler por caso de uso, controllers por agregado, factories de comandos,
     plantillas de email separadas del transporte.
   - O: puertos (añadir adaptador sin tocar handlers — así entró R2) y excepciones
     auto-mapeables (añadir excepción sin tocar el listener).
   - L: los adaptadores cumplen el contrato de los puertos (el fake de test sustituye
     a R2 sin tocar producción).
   - I: puertos pequeños y todos los métodos usados.
   - D: Application y Controllers dependen solo de puertos del Domain.
3. **Puertos en el Domain**: las interfaces viven en `Domain/` y las implementaciones
   en `Infrastructure/`; el wiring por alias en `services.yaml`.
4. **Validación en los commands**: `#[Assert]` en el DTO de entrada, única fuente de
   verdad; 422 centralizado vía `ValidationFailedException`.
5. **Token único en BD en vez de JWT**: un solo admin, un solo token válido, sin
   librerías externas. `AdminAuthListener` centraliza la protección de rutas.
6. **Nginx como proxy**: elimina CORS en producción. `envsubst` inyecta variables
   sin rebuilds de imagen.
7. **Anti-enumeración en login**: siempre el mismo mensaje genérico.
8. **Una migration por cambio**: nunca editar migrations existentes; siempre crear
   una nueva (ver ejemplo: recreación de `admin_token`).
9. **Fotos en Cloudflare R2**: `FileStorageInterface::store()` devuelve la URL
   pública completa (la BD guarda la URL); `delete()` es idempotente y protegido
   contra URLs externas (picsum) y legacy. La foto grande y su miniatura son dos
   objetos: el navegador genera ambos y el backend los guarda por separado.
10. **Dobles de test para adaptadores externos**: Brevo y R2 nunca se tocan en los
    tests de integración.
