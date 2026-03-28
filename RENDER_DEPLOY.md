# Guía de Despliegue en Render (Dulzia Salamanca)

Esta guía detalla cómo desplegar el proyecto completo en [Render](https://render.com).

## 1. Base de Datos (PostgreSQL)

1. En el Dashboard de Render, haz clic en **New +** y selecciona **PostgreSQL**.
2. Configura los datos:
   - **Name**: `dulzia-db`
   - **Database**: `dulzia`
   - **User**: `dulzia_admin`
3. Haz clic en **Create Database**.
4. Una vez creada, copia la **Internal Database URL** (la usaremos para el Backend).

## 2. Backend (API - PHP/Symfony)

1. Haz clic en **New +** y selecciona **Web Service**.
2. Conecta tu repositorio de GitHub.
3. Configura el servicio:
   - **Name**: `dulzia-backend`
   - **Root Directory**: `backend`
   - **Runtime**: `Docker`
   - **Dockerfile Path**: `Dockerfile` (ya que estamos en el directorio `backend`)
4. **Environment Variables**:
   - `DATABASE_URL`: (La URL que copiaste de PostgreSQL)
   - `APP_ENV`: `prod`
   - `APP_SECRET`: (Genera una cadena aleatoria larga)
   - `CORS_ALLOW_ORIGIN`: `https://tu-frontend.onrender.com` (Actualízalo después de crear el front)
5. **Persistent Disk** (Crítico para las fotos):
   - Ve a la pestaña **Disks**.
   - Haz clic en **Add Disk**.
   - **Name**: `uploads-disk`
   - **Mount Path**: `/app/public/uploads`
   - **Size**: `1GB` (suficiente para empezar)

## 3. Frontend (Vue.js)

1. Haz clic en **New +** y selecciona **Static Site**.
2. Conecta el mismo repositorio.
3. Configura el sitio:
   - **Name**: `dulzia-frontend`
   - **Build Command**: `cd frontend && npm install && npm run build`
   - **Publish Directory**: `frontend/dist`
4. **Environment Variables**:
   - `VITE_API_URL`: `https://dulzia-backend.onrender.com` (La URL que te asigne Render para el backend)

## 4. Configuración Final

1. Una vez tengas la URL del frontend, vuelve a la configuración del **Backend** y actualiza la variable `CORS_ALLOW_ORIGIN`.
2. Symfony necesita ejecutar las migraciones. Como Render usa Docker, puedes entrar a la **Dashboard Shell** del servicio backend y ejecutar:
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

> [!IMPORTANT]
> Render pone los servicios gratuitos a "dormir" tras 15 min de inactividad. La primera carga después de un tiempo puede tardar ~30s. Si necesitas disponibilidad inmediata para un cliente, considera el plan "Starter" ($7/mes).
