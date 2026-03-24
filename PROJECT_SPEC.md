# PROJECT SPEC — Full-Stack Web App (Symfony + Vue 3)

> Plantilla de arquitectura, stack y patrones para proyectos full-stack con backend PHP y frontend SPA.
> Al iniciar un proyecto nuevo con esta spec, leer este archivo para saber cómo estructurarlo.

---

## Concepto General

Aplicación web con múltiples funcionalidades independientes bajo un mismo frontend SPA autenticado.
Cada funcionalidad vive en su propia ruta protegida y consume una API REST en el backend.

---

## Stack Tecnológico

### Backend
| Elemento | Tecnología |
|---|---|
| Framework | Symfony 7 |
| Lenguaje | PHP 8.3 |
| Arquitectura | Hexagonal (DDD) + CQRS ligero |
| ORM | Doctrine ORM 3 + Doctrine Migrations |
| Base de datos | PostgreSQL 16 |
| Cola de mensajes | Symfony Messenger + Redis 7 |
| Email transaccional | Brevo REST API |
| Autenticación | JWT HS256 propio (`hash_hmac`, sin librería externa) |
| Contraseñas | bcrypt vía `password_hash()` PHP nativo |
| CORS | NelmioCorsBundle |

### Frontend
| Elemento | Tecnología |
|---|---|
| Framework | Vue 3 (Composition API) |
| Bundler | Vite 5 |
| Routing | Vue Router 4 |
| Estado global | Pinia + `pinia-plugin-persistedstate` |
| Estilos | SCSS con variables y mixins propios |
| Tests | Vitest |
| PWA | vite-plugin-pwa |
| Servidor estático | Nginx (con `envsubst` para inyección de variables) |

### Infraestructura
| Elemento | Tecnología |
|---|---|
| Local | Docker Compose |
| Producción | Railway + Nixpacks |
| Reverse proxy | Nginx dentro del contenedor frontend |

---

## Estructura de Directorios

```
project-root/
├── .env                        # Secrets raíz — .gitignored
├── docker-compose.yml
├── Makefile                    # Comandos de desarrollo
├── README.md
├── PROJECT_SPEC.md
├── backend/                    # Symfony 7
│   ├── config/packages/        # messenger.yaml, doctrine.yaml, nelmio_cors.yaml, etc.
│   ├── migrations/             # Una migration por cambio de esquema
│   ├── public/
│   ├── src/
│   │   ├── Application/        # Command Handlers (CQRS) — una carpeta por feature
│   │   ├── Controller/         # HTTP Controllers (delgados: solo deserializar y responder)
│   │   ├── Domain/             # Interfaces de repositorio y excepciones — una carpeta por feature
│   │   ├── Entity/             # Entidades Doctrine
│   │   ├── Infrastructure/
│   │   │   ├── Email/          # Implementación del mailer (Brevo)
│   │   │   ├── Repository/     # DoctrineXxxRepository
│   │   │   └── Security/       # JwtService + JwtServiceInterface
│   │   ├── Service/            # Servicios de aplicación complejos
│   │   ├── EventListener/
│   │   │   ├── JwtAuthListener.php      # Valida JWT en rutas protegidas
│   │   │   └── ApiExceptionListener.php # Convierte excepciones de dominio a JSON
│   │   ├── Command/            # Comandos de consola Symfony
│   │   └── Message/            # Mensajes para Symfony Messenger
│   ├── tests/
│   │   ├── Unit/
│   │   └── Integration/
│   ├── Dockerfile              # Multi-stage build
│   └── composer.json
│
└── frontend/                   # Vue 3
    ├── src/
    │   ├── pages/              # Un componente por ruta
    │   ├── components/
    │   │   ├── ui/             # Componentes base: BaseButton, BaseInput, BaseModal, BaseAlert, BaseSpinner
    │   │   ├── layout/         # Wrappers de layout
    │   │   └── features/       # Componentes específicos de cada feature
    │   ├── composables/        # Lógica de negocio (useAuth, useXxx)
    │   ├── services/           # Clientes HTTP (api.js + un service por dominio)
    │   ├── stores/             # Pinia stores (solo estado global persistente)
    │   ├── router/             # index.js con guards de navegación
    │   ├── styles/             # _variables.scss, _mixins.scss, _animations.scss, main.scss
    │   ├── App.vue
    │   └── main.js
    ├── __tests__/              # Vitest
    ├── Dockerfile
    ├── nginx.conf              # Proxy + SPA fallback + headers de seguridad
    └── vite.config.js
```

---

## Arquitectura Backend (Hexagonal + CQRS)

### Flujo de una petición
```
HTTP Request
  → Controller (deserializa + valida input básico)
    → Application/CommandHandler (orquesta dominio e infraestructura)
      → Domain (lógica pura, interfaces)
      → Infrastructure (DB, email, filesystem, etc.)
  → Controller (serializa respuesta JSON)
```

### Reglas
- **Controllers**: nunca lógica de negocio. Solo deserializar input y serializar respuesta.
- **CommandHandlers** (`Application/`): no saben nada de HTTP.
- **Domain**: solo interfaces de repositorio y excepciones. Sin dependencias de infraestructura.
- **Infrastructure**: implementa las interfaces del dominio.
- **ApiExceptionListener**: centraliza la conversión de excepciones de dominio a respuestas JSON con el código HTTP apropiado.

### Autenticación JWT
- Implementación propia con `hash_hmac('SHA256', ...)` — sin librería externa
- `JwtService::generateToken(userId, username, expiresIn)` → JWT firmado HS256
- `JwtAuthListener` intercepta cada request a rutas protegidas y valida firma + expiración
- Expiración: 24 horas
- Token almacenado en `localStorage` del frontend (gestionado por el Pinia store)

### Procesamiento asíncrono (cuando aplique)
- Los jobs pesados se despachan como mensajes Messenger al transporte Redis
- Worker separado en su propio contenedor Docker — mismo Dockerfile que el backend, diferente comando de inicio
- Retry: 3 intentos, delay inicial 1s, multiplicador 2x
- Estado del job en archivos temporales en `/tmp/`; auto-limpiados tras la respuesta

---

## Arquitectura Frontend (Vue 3 Composition API)

### Flujo de datos
```
Page Component
  → Composable (useXxx.js)    ← toda la lógica de negocio
    → Service (xxxService.js) ← llamadas HTTP puras
      → api.js                ← cliente base (añade JWT, maneja 401)
```

### Convenciones
- **Pages**: orquestan composables; no contienen lógica de negocio directamente
- **Composables**: devuelven `{ state, actions }`. Son la unidad testeble del frontend.
- **Services**: funciones puras sin estado; solo fetch
- **Stores (Pinia)**: únicamente para estado global persistente (token JWT, usuario autenticado)
- **Componentes UI base** (`ui/`): reutilizables en toda la app, sin lógica de dominio

### Routing
- Rutas públicas: `/`, `/login`, `/register`, `/verify-email`, `/forgot-password`, `/reset-password`
- Rutas protegidas: cualquier funcionalidad que requiera autenticación
- Guard: si no hay token en el store → redirect a `/login`

### Comunicación con el backend
- **Nginx actúa como proxy**: el frontend solo habla con su propio origen — sin CORS
- Nginx reenvía `/api/*` y cualquier path de la API al backend
- En desarrollo, Vite proxy replica el mismo comportamiento
- `api.js`: inyecta `Authorization: Bearer <token>` y hace auto-logout en 401

---

## Docker Compose (local)

```yaml
services:
  backend:    # Symfony — expone puerto de API
  worker:     # Mismo Dockerfile que backend; comando: messenger:consume async
  redis:      # Redis 7
  postgres:   # PostgreSQL 16
  frontend:   # Nginx + Vue build estático
```

- Red `app-network` bridge para comunicación entre servicios
- Volumen `pgdata` para persistencia de PostgreSQL
- Volúmenes compartidos entre backend y worker para archivos temporales si aplica
- El worker reutiliza la imagen del backend (no hay Dockerfile extra)

---

## Variables de Entorno

### Backend
| Variable | Descripción |
|---|---|
| `APP_ENV` | `prod` en producción |
| `APP_SECRET` | String aleatorio de 32+ chars (Symfony) |
| `JWT_SECRET` | Clave de firma JWT |
| `DATABASE_URL` | DSN PostgreSQL |
| `REDIS_URL` | URL Redis |
| `MESSENGER_TRANSPORT_DSN` | DSN Redis para Messenger |
| `DEFAULT_URI` | URL base del frontend (para links en emails) |
| `EMAIL_API_KEY` | API key del proveedor de email |
| `CORS_ALLOW_ORIGIN` | Regex de orígenes permitidos |

### Frontend (Nginx)
| Variable | Descripción |
|---|---|
| `BACKEND_UPSTREAM` | URL del backend |
| `PORT` | Puerto en el que escucha Nginx |

### Gestión de secrets
- `.env` en la raíz contiene los secrets reales → **nunca commitear**
- `backend/.env` es un template con valores de ejemplo para desarrollo local
- En producción (Railway): variables configuradas en el dashboard

---

## Seguridad

| Medida | Implementación |
|---|---|
| Contraseñas | bcrypt (`password_hash` PHP nativo) |
| JWT | HS256 con `hash_hmac`, expiry 24h |
| Tokens de verificación/reset | 64-char hex; expiry 24h (verificación) / 1h (reset) |
| Anti-enumeración | Reset password y verify email siempre devuelven 200 OK |
| Comandos de sistema | Usar array de argumentos en `proc_open`, nunca strings construidos con input del usuario |
| Archivos temporales | Auto-eliminados tras la respuesta |
| Headers HTTP | X-Frame-Options, X-Content-Type-Options, Referrer-Policy vía Nginx |
| CORS | NelmioCorsBundle con allowlist configurable por entorno |

---

## Emails Transaccionales

- **Proveedor**: Brevo REST API v3
- **Implementación**: `Infrastructure/Email/BrevoMailer.php` implementa una interfaz del dominio
- **Emails estándar**: verificación de cuenta (token 24h) y reset de contraseña (token 1h)
- **Patrón anti-enumeración**: siempre responder 200 OK aunque el email no exista

---

## Base de Datos

### Entidad de usuario (siempre presente)
```
user
  id (PK, auto-increment)
  username (unique)
  password_hash
  email (unique)
  is_verified (bool, default false)
  verification_token (nullable)
  verification_token_expires (nullable)
  reset_token (nullable)
  reset_token_expires (nullable)
  created_at
```

### Convenciones de migración
- Una migration por cambio de esquema
- Nomenclatura: `VersionYYYYMMDDNNNNNN.php`
- Nunca modificar una migration ya ejecutada en producción

---

## API — Endpoints de Auth (siempre presentes)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/health` | Health check |
| POST | `/api/auth/register` | Registro → envía email de verificación |
| POST | `/api/auth/login` | Login → JWT |
| GET | `/api/auth/verify-email?token=` | Verificar email |
| POST | `/api/auth/request-reset` | Solicitar reset de contraseña |
| POST | `/api/auth/reset-password` | Resetear contraseña con token |

Los endpoints de cada feature se añaden bajo `/api/{feature}/` siguiendo la misma convención.
Los endpoints protegidos requieren `Authorization: Bearer <token>`.

---

## Despliegue en Producción (Railway)

1. PostgreSQL y Redis como servicios gestionados de Railway
2. **Backend**: servicio Nixpacks apuntando a `/backend`
3. **Worker** (si hay procesamiento asíncrono): otro servicio Nixpacks, mismo repo, start command override con `messenger:consume async -vv`
4. **Frontend**: servicio Nixpacks apuntando a `/frontend`, build Vite + serve con Nginx
5. Variables de entorno configuradas en el dashboard de Railway por servicio
6. Crear usuario admin manualmente vía `railway run php bin/console app:admin:create`

---

## Makefile — Comandos estándar

```bash
make up               # Levantar todos los contenedores
make down             # Parar contenedores
make rebuild          # Rebuild completo
make logs             # Logs de backend + worker
make shell            # Shell en el contenedor backend
make migrate          # Ejecutar migraciones pendientes
make migration-diff   # Generar migration por cambios en entidades
make cache-clear      # Limpiar caché Symfony
make test             # Todos los tests
make test-unit        # Tests unitarios PHP
make test-integration # Tests de integración PHP
make test-frontend    # Tests Vitest
make test-coverage    # Cobertura (HTML)
make install          # Instalar dependencias
make build-front      # Reconstruir solo el frontend
```

---

## Tests

### Backend (PHPUnit)
```
tests/Unit/Application/{Feature}/   # Un test por CommandHandler
tests/Unit/Infrastructure/Security/ # JwtService
tests/Integration/Controller/       # Tests HTTP de integración
```

### Frontend (Vitest)
```
__tests__/useXxx.spec.js   # Un test por composable
```

---

## Patrones y Decisiones de Diseño

1. **Hexagonal + CQRS ligero**: separa HTTP, lógica y persistencia. Permite testear handlers sin base de datos real.
2. **Worker como contenedor separado**: el backend no bloquea en tareas largas. Reutiliza la misma imagen Docker.
3. **JWT propio sin librería**: reduce dependencias. `hash_hmac` es suficiente para HS256 estándar.
4. **Nginx como proxy en el frontend**: elimina CORS por completo. El frontend habla siempre con su mismo origen.
5. **`envsubst` en Nginx**: variables de entorno inyectadas en `nginx.conf` al arrancar el contenedor, sin rebuilds de imagen.
6. **Pinia + persistedstate**: el token y el usuario autenticado sobreviven recargas sin código adicional.
7. **Composables para lógica de negocio**: los Page components solo orquestan; la lógica testeble vive en composables.
8. **Anti-enumeración en auth**: reset y verificación siempre devuelven 200 OK para no revelar qué emails existen.
9. **`ApiExceptionListener` centralizado**: los handlers lanzan excepciones de dominio; un solo listener las convierte a JSON con el código HTTP correcto. No hay lógica de error duplicada en controllers.
10. **Una migration por cambio**: nunca editar migrations existentes; siempre crear una nueva.
