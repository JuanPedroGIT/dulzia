# Dulzia Salamanca Eventos — Web

Web de marketing para [Dulzia Salamanca Eventos](https://www.dulziasalamancaeventos.com), empresa especializada en servicios de entretenimiento y catering para bodas, cumpleaños y eventos en Salamanca.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Symfony 7 · PHP 8.3 · Arquitectura Hexagonal + CQRS |
| Base de datos | PostgreSQL 16 |
| Email | Brevo REST API v3 |
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
│   │   ├── Application/      # Command Handlers (CQRS)
│   │   ├── Controller/       # HTTP Controllers
│   │   ├── Domain/           # Interfaces de repositorio
│   │   ├── Entity/           # Entidades Doctrine
│   │   ├── EventListener/    # ApiExceptionListener
│   │   └── Infrastructure/   # Email (Brevo) + Repositorios
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
make test-unit       # Tests unitarios PHPUnit
make test-integration# Tests de integración PHPUnit
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

### Backend (`backend/.env`)

Contiene los valores por defecto para desarrollo. Los valores reales se inyectan desde el `.env` raíz a través de Docker Compose. **No contiene secretos reales.**

---

## Tests

```bash
# PHPUnit (backend)
make test-unit
make test-integration

# Vitest (frontend)
make test-frontend
```

Los tests del frontend cubren el composable `useContactForm` (lógica del formulario de contacto, manejo de errores 422 y errores de red).

---

## Deploy en Railway

1. Crea un proyecto en [Railway](https://railway.app)
2. Añade los servicios: **PostgreSQL** (gestionado por Railway)
3. Crea un servicio para el **backend** apuntando a `/backend`
4. Crea un servicio para el **frontend** apuntando a `/frontend`
5. Configura las variables de entorno en el dashboard de Railway:
   - `DATABASE_URL` (Railway lo genera automáticamente al enlazar PostgreSQL)
   - `EMAIL_API_KEY`
   - `APP_SECRET`
   - `CORS_ALLOW_ORIGIN` (dominio de producción)
   - `BACKEND_UPSTREAM` (URL interna del backend para Nginx)
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
- Dirección: C. Martín Alonso Pedraz, 14 · Salamanca
- Horario: Lunes a viernes, 7:00–18:00
