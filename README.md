# Dulzia Salamanca Eventos — Web

Web de marketing para [Dulzia Salamanca Eventos](https://www.dulziasalamancaeventos.com), empresa especializada en servicios de entretenimiento y catering para bodas, cumpleaños y eventos en Salamanca.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Symfony 7 · PHP 8.3 · Arquitectura Hexagonal + CQRS |
| Base de datos | PostgreSQL 16 |
| Email | Brevo REST API v3 |
| Almacenamiento | Cloudflare R2 (S3 API) |
| Frontend | Vue 3 (Composition API) · Vite 5 · SCSS |
| Routing | Vue Router 4 |
| Estado global | Pinia |
| Tests | PHPUnit 11 · Vitest |
| Infra local | Docker Compose |
| Deploy | Railway + Nixpacks |

---

## Estructura del proyecto

```
dulziasalamanca/
├── backend/                  # API Symfony 7
│   ├── src/
│   │   ├── Application/      # Command/Query Handlers (CQRS) — una carpeta por feature
│   │   ├── Controller/       # HTTP Controllers (delgados)
│   │   ├── Domain/           # Puertos (interfaces) y excepciones por feature
│   │   ├── Entity/           # Entidades Doctrine
│   │   ├── EventListener/    # ApiExceptionListener + AdminAuthListener
│   │   └── Infrastructure/   # Email (Brevo) + Repositorios + Storage (R2) + Security
│   ├── migrations/           # Migraciones de base de datos
│   ├── config/               # Configuración Symfony
│   └── tests/                # PHPUnit
│
├── frontend/                 # SPA Vue 3
│   └── src/
│       ├── pages/            # Home · Servicios · Nosotros · Contacto · Cookies
│       ├── components/       # ui/ · layout/ · features/
│       ├── composables/      # useContactForm · useSeo
│       ├── services/         # api.js · contactService.js
│       ├── data/             # services.js (catálogo de 11 servicios)
│       └── styles/           # SCSS: variables · mixins · main
│
├── docker-compose.yml
├── Makefile
└── .env.example
```

---

## Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (con Docker Compose v2)
- [Node.js](https://nodejs.org/) ≥ 20 (solo para el editor/IDE — el runtime es Docker)
- Cuenta en [Brevo](https://www.brevo.com/) con una API key

---

## Puesta en marcha local

### 1. Clonar y configurar variables de entorno

```bash
git clone <url-del-repo>
cd dulziasalamanca
cp .env.example .env
```

Edita `.env` con tus valores:

```env
POSTGRES_PASSWORD=tu_contraseña_segura
BREVO_API_KEY=tu_api_key_de_brevo
APP_SECRET=string_aleatorio_de_32_caracteres
```

> Genera un `APP_SECRET` seguro con: `openssl rand -hex 32`

### 2. Levantar los contenedores

```bash
make up
```

Esto levanta tres servicios:
- `postgres` → PostgreSQL 16 en el puerto 5432
- `backend` → API Symfony en http://localhost:8000
- `frontend` → Vite dev server en http://localhost:5173

### 3. Ejecutar migraciones

```bash
make migrate
```

### 4. Instalar dependencias locales (para el editor)

```bash
make install
```

> Esto instala `vendor/` y `node_modules/` localmente para que el IDE tenga autocompletado. Los contenedores usan sus propias copias internas.

La web ya está disponible en **http://localhost:5173**

---

## Comandos disponibles

```bash
# Contenedores
make up              # Levantar todos los servicios
make down            # Parar los servicios
make rebuild         # Rebuild completo (borra volúmenes)
make logs            # Logs del backend en tiempo real
make shell           # Shell dentro del contenedor backend

# Base de datos
make migrate         # Ejecutar migraciones pendientes
make migration-diff  # Generar migración a partir de cambios en entidades
make cache-clear     # Limpiar caché de Symfony

# Tests
make test            # Todos los tests (PHP + frontend)
make test-setup      # Crea la BD de test (dulzia_test) — solo la primera vez
make test-unit       # Tests unitarios PHPUnit
make test-integration# Tests de integración PHPUnit (requiere test-setup)
make test-frontend   # Tests Vitest

# Dependencias
make install         # Instalar todas las dependencias en local
make composer-require pkg="vendor/nombre"  # Añadir paquete PHP
make npm-install pkg="nombre"              # Añadir paquete npm
make sync-vendor     # Sincronizar vendor del contenedor → local
make sync-npm        # Sincronizar node_modules del contenedor → local
```

---

## API

| Método | Ruta | Descripción | Auth |
|---|---|---|---|
| `GET` | `/health` | Health check | No |
| `POST` | `/api/contact` | Enviar formulario de contacto | No |

### POST /api/contact

**Body:**
```json
{
  "name": "Juan García",
  "email": "juan@ejemplo.com",
  "phone": "+34 600 000 000",
  "eventType": "Boda",
  "message": "Me gustaría información sobre..."
}
```

**Respuestas:**
- `201 Created` → mensaje recibido y email enviado
- `422 Unprocessable Entity` → errores de validación por campo

---

## Páginas

| Ruta | Descripción |
|---|---|
| `/` | Home — Hero, servicios destacados, estadísticas, CTA |
| `/servicios` | Catálogo completo de 11 servicios con filtro por categoría |
| `/nosotros` | Historia de la empresa, valores, información de contacto |
| `/contacto` | Formulario de contacto + datos de la empresa |
| `/politica-cookies` | Política de cookies |

---

## Variables de entorno

### Raíz (`.env`)

| Variable | Descripción |
|---|---|
| `POSTGRES_PASSWORD` | Contraseña de PostgreSQL |
| `BREVO_API_KEY` | API key de Brevo para envío de emails |
| `APP_SECRET` | Clave secreta de Symfony (mínimo 32 caracteres) |
| `CORS_ALLOW_ORIGIN` | Regex de orígenes permitidos para CORS |
| `R2_ACCOUNT_ID` | Account ID de Cloudflare (dash → R2) |
| `R2_ACCESS_KEY_ID` | Access Key ID del API token de R2 (Object Read & Write) |
| `R2_ACCESS_KEY_SECRET` | Secret Access Key del API token de R2 |
| `R2_BUCKET_NAME` | Nombre del bucket R2 de fotos |
| `R2_PUBLIC_URL` | URL pública del bucket (r2.dev o dominio propio) |
| `MAILER_TO_EMAIL` | Email de destino de los mensajes del formulario de contacto (ver "Ajustes desde el panel") |
| `MAILER_TO_NAME` | Nombre del destinatario de esos mensajes |
| `MAILER_FROM_EMAIL` / `MAILER_FROM_NAME` | Remitente de la notificación al negocio |
| `MAILER_CONFIRM_FROM_EMAIL` / `MAILER_CONFIRM_FROM_NAME` | Remitente de la confirmación al visitante |

### Ajustes desde el panel

El destinatario de los mensajes (`MAILER_TO_EMAIL` / `MAILER_TO_NAME`) se puede cambiar sin tocar
el `.env`, desde **Panel → Mensajes → ⚙️ Ajustes de email** (tabla `settings` en la BD). Reglas:

- Mientras no guardes nada en el panel, mandan las variables del `.env`.
- En cuanto guardas un valor, **manda la BD** y el `.env` deja de aplicarse a ese campo.
- Para volver al valor del `.env`, vacía el campo en el panel y guarda (o pulsa "Restablecer").
- Un email vacío o mal formado no se puede guardar: la validación lo bloquea.
- En producción, cambiar el `.env` exige `make cache-clear` **y reiniciar el contenedor**: los
  valores de entorno se resuelven al compilar el contenedor de Symfony (`opcache` no revalida).

### Backend (`backend/.env`)

Contiene los valores por defecto para desarrollo. Los valores reales se inyectan desde el `.env` raíz a través de Docker Compose. **No contiene secretos reales.**

---

## Tests

```bash
# PHPUnit (backend)
make test-setup      # Una sola vez: crea la BD de test en el postgres compartido
make test-unit       # 55 tests unitarios (handlers, entidades, storage, security)
make test-integration# 25 tests HTTP sobre BD dulzia_test (WebTestCase)
make test          # Todo lo anterior + frontend

# Vitest (frontend)
make test-frontend
```

### Backend (PHPUnit)

- **Unit** (`backend/tests/Unit/`): un test por CommandHandler/QueryHandler (CQRS)
  con repositorios mockeados — sin base de datos. Cubre también entidades,
  `CloudflareR2Storage` (con `Aws\MockHandler`, sin red real), `LocalFileStorage`
  y `AdminTokenService`.
- **Integration** (`backend/tests/Integration/`): tests HTTP (WebTestCase) contra
  la BD dedicada `dulzia_test` (schema creado desde los mappings de Doctrine y
  truncado entre tests). Los adaptadores reales se sustituyen por dobles de test
  (`services_test.yaml`): el mailer nunca llama a Brevo y el storage nunca llama a R2.

Los tests del frontend cubren el composable `useContactForm` (lógica del formulario de contacto, manejo de errores 422 y errores de red).

---

## Deploy en Railway

1. Crea un proyecto en [Railway](https://railway.app)
2. Añade los servicios: **PostgreSQL** (gestionado por Railway)
3. Crea un servicio para el **backend** apuntando a `/backend`
4. Crea un servicio para el **frontend** apuntando a `/frontend`
5. Configura las variables de entorno en el dashboard de Railway:
   - `DATABASE_URL` (Railway lo genera automáticamente al enlazar PostgreSQL)
   - `BREVO_API_KEY`
   - `APP_SECRET`
   - `CORS_ALLOW_ORIGIN` (dominio de producción)
   - `BACKEND_UPSTREAM` (URL interna del backend para Nginx)
   - `R2_ACCOUNT_ID`, `R2_ACCESS_KEY_ID`, `R2_ACCESS_KEY_SECRET`, `R2_BUCKET_NAME`, `R2_PUBLIC_URL` (fotos en Cloudflare R2 — el bucket debe tener r2.dev público habilitado)
6. Ejecuta la migración inicial:
   ```bash
   railway run php bin/console doctrine:migrations:migrate --no-interaction
   ```

---

## SEO

- Meta tags, Open Graph y Twitter Card configurados por página via composable `useSeo`
- JSON-LD `LocalBusiness` en la página de inicio
- `sitemap.xml` en `/public/sitemap.xml`
- `robots.txt` en `/public/robots.txt`
- Prerendering de las 4 rutas principales en el build de producción (`vite-plugin-prerender`)

Tras el deploy, registra el sitemap en **Google Search Console**:
```
https://www.dulziasalamancaeventos.com/sitemap.xml
```

---

## Contacto del negocio

**Dulzia Salamanca Eventos**
- Web: [dulziasalamancaeventos.com](https://www.dulziasalamancaeventos.com)
- Email: info@dulziasalamancaeventos.com
- Teléfono: +34 629 991 659
- Horario: Lunes a viernes, 7:00–18:00

## [!IMPORTANT]
> Ejecutar las migraciones la primera vez desde el terminal de Railway en el servicio backend:
> `php bin/console doctrine:migrations:migrate --no-interaction`

## Admin, para crear un admin
docker-compose exec -it backend php bin/console app:admin:init
Usuario: [EMAIL_ADDRESS]
Contraseña: [PASSWORD]
