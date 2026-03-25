# Plan de Despliegue en Railway

Este plan detalla los pasos necesarios para publicar el proyecto "Dulzia Salamanca" en Railway, configurando el backend (Symfony), el frontend (Vue.js) y la base de datos (PostgreSQL).

## Pasos Previos

1. **Repositorio en GitHub:** Asegúrate de que el código esté subido a un repositorio de GitHub.
2. **Cuenta en Railway:** Ten una cuenta activa en [railway.app](https://railway.app/).

## Configuración en Railway

### 1. Crear el Proyecto y la Base de Datos
- En el dashboard de Railway, haz clic en **"New Project"**.
- Selecciona **"Provision PostgreSQL"**. Esto creará tu base de datos.

### 2. Desplegar el Backend (Symfony)
- Haz clic en **"New"** -> **"GitHub Repo"** y selecciona tu repositorio.
- Designa la carpeta raíz como `backend` (Settings -> root directory).
- Railway detectará automáticamente el `Dockerfile`. Asegúrate de que use el target `prod`.
- **Variables de Entorno (Variables):**
    - `DATABASE_URL`: `${{Postgres.DATABASE_URL}}`
    - `APP_ENV`: `prod`
    - `APP_SECRET`: `hUlFgtrUq6gsB67xyTfP8AEvS9P8VaXIpneAOEvYqPM=`
    - `CORS_ALLOW_ORIGIN`: La URL que Railway asigne al frontend (ej. `https://frontend-production.up.railway.app`).
    - `EMAIL_API_KEY`: Tu clave de Brevo (ver `.env`).

### 3. Desplegar el Frontend (Vue.js)
- Haz clic en **"New"** -> **"GitHub Repo"** y selecciona el mismo repositorio.
- Designa la carpeta raíz como `frontend` (Settings -> root directory).
- Railway detectará el `Dockerfile` y usará la etapa `prod` (Nginx).
- **Variables de Entorno (Variables):**
    - `BACKEND_UPSTREAM`: La URL interna del servicio backend (ej. `http://backend.railway.internal:8000`).
    - `PORT`: `${{PORT}}` (Railway asigna automáticamente el puerto).

## Verificación del Despliegue

### Automatizada
- Revisar los logs de Railway para ambos servicios.
- Verificar que el servicio `POSTGRES` esté saludable.

### Manual
1. Acceder a la URL generada para el frontend.
2. Navegar por la página y verificar que se carguen los datos.

> [!IMPORTANT]
> Ejecutar las migraciones la primera vez desde el terminal de Railway en el servicio backend:
> `php bin/console doctrine:migrations:migrate --no-interaction`
