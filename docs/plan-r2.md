# Plan: Fotos de servicios en Cloudflare R2 (alcance mínimo)

> Plan de implementación — fichero versionado en `docs/` del repo.
> Fecha: 2026-09-21 · Proyecto: dulziasalamanca

## Contexto

Hoy las fotos de servicios se guardan en disco local (`backend/public/uploads/services/`) servidas por el propio PHP bajo `APP_URL/uploads/services/`, con el volumen montado en Docker y disco persistente en Render. Se quiere pasar a un bucket **Cloudflare R2**, igual que el proyecto cokalbarunning, que ya usa `aws/aws-sdk-php` contra el endpoint S3 de R2.

**Decisiones tomadas:**
1. **Alcance mínimo**: nueva clase `CloudflareR2Storage` que implementa `FileStorageInterface` SIN cambiar el contrato — `store(UploadedFile): string` devuelve la URL pública completa y `delete(string $url)` deriva la key de la URL. La BD sigue guardando la URL completa en `service_example.image_url`. **No se tocan** handlers, controllers, entidad ni frontend.
2. **Migración manual** de las 5 fotos existentes (rclone + UPDATE SQL), sin comando Symfony.

## Cumplimiento con las especificaciones del proyecto (revisado)

Revisado contra `PROJECT_SPEC.md`, `README.md`, `SHARED_SERVER_SETUP.md` y `RENDER_DEPLOY.md`:

- **Arquitectura hexagonal + CQRS**: la nueva clase vive en `Infrastructure/Storage/` implementando `FileStorageInterface`; controllers y handlers no se tocan → flujos HTTP→Handler→Infrastructure intactos.
- **Gestión de secrets**: credenciales R2 solo en `.env` raíz (gitignored); `.env.example` lleva placeholders; `backend/.env` solo passthrough `${R2_*}` sin valores reales.
- **Convenciones de migración**: **no hace falta migration** — no hay cambio de esquema; `image_url VARCHAR(500)` alberga la URL R2 completa (≈98 chars). Las migraciones existentes no se modifican.
- **Variables de entorno**: se añaden 5 `R2_*`; el plan actualiza la documentación de variables en PROJECT_SPEC y README.
- **Estructura de directorios**: `Infrastructure/Storage/` ya existía; se añade una implementación más del mismo puerto.
- **Seguridad**: bucket público de solo lectura vía URL r2.dev = misma exposición que el `public/uploads` actual; escritura solo con credenciales en el backend; `delete()` protegido contra URLs externas (preserva el guard de picsum).
- **Tests**: no existe infraestructura PHPUnit ejecutable en el repo hoy (`tests/Unit` ausente, `phpunit.xml` gitignored). Test unitario opcional del adaptador con `Aws\MockHandler` (paso 9).
- **Docker/Infra**: se retiran los volúmenes de uploads y el disco persistente de Render; el servidor compartido recibe las `R2_*` por el `.env` raíz vía `env_file` (misma estrategia que cokalbarunning).

## Pasos previos en Cloudflare (manual)

1. Copiar el **Account ID** (dash.cloudflare.com → R2).
2. Crear bucket, p. ej. `dulzia-salamanca` (nombres globales; si está ocupado, otro).
3. Bucket → Settings → Public access → **Allow r2.dev subdomain**; anotar la URL pública (`https://pub-<hash>.r2.dev`).
4. Crear API token (R2 → Manage API Tokens): permisos *Object Read & Write* sobre este bucket. Guardar Access Key ID y Secret.
5. Decidir `R2_PUBLIC_URL`: r2.dev (recomendado, igual que cokalba) u opcionalmente un dominio propio vía Custom Domains. **Decidirlo antes del UPDATE SQL** porque la URL base queda grabada en la BD.

## Implementación

### 1. Dependencia `aws/aws-sdk-php` — `backend/composer.json`

Añadir en `require` (primera entrada alfabéticamente): `"aws/aws-sdk-php": "^3.0"`.

Instalar (no hay PHP/composer en el host; el Makefile tiene referencias rotas al servicio `backend` — el real es `dulzia-backend` — así que usar comandos explícitos):

```bash
docker compose up -d
docker compose exec dulzia-backend composer require aws/aws-sdk-php:^3.0 --no-interaction
docker cp dulzia-backend:/var/www/html/vendor ./backend/vendor   # vendor local para el IDE
docker compose restart dulzia-backend
```

Alternativa sin stack levantada: `docker run --rm -v "$(pwd)/backend":/app -w /app composer:2 composer require aws/aws-sdk-php:^3.0 --no-interaction`.

Commitear `backend/composer.json` + `backend/composer.lock`.

### 2. Nueva clase — `backend/src/Infrastructure/Storage/CloudflareR2Storage.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CloudflareR2Storage implements FileStorageInterface
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    private S3Client $client;
    private string $bucket;
    private string $publicUrl;

    public function __construct(
        string $accountId,
        string $accessKeyId,
        string $accessKeySecret,
        string $bucket,
        string $publicUrl,
    ) {
        $this->bucket    = $bucket;
        $this->publicUrl = rtrim($publicUrl, '/');

        $this->client = new S3Client([
            'region'  => 'auto',
            'version' => 'latest',
            'endpoint' => "https://{$accountId}.r2.cloudflarestorage.com",
            'credentials' => [
                'key'    => $accessKeyId,
                'secret' => $accessKeySecret,
            ],
        ]);
    }

    public function store(UploadedFile $file): string
    {
        // Misma validación que LocalFileStorage → el controller ya la convierte en 400
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Archivo inválido o corrupto: ' . $file->getErrorMessage());
        }

        $mime = $file->getMimeType();
        if ($mime === null || !in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new \InvalidArgumentException('Tipo de archivo no permitido: ' . ($mime ?? 'desconocido'));
        }

        $ext      = $file->guessExtension() ?? 'jpg';
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $key      = 'services/' . $filename;

        $realPath = $file->getRealPath();
        if ($realPath === false) {
            throw new \InvalidArgumentException('No se pudo leer el archivo subido');
        }

        $this->client->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $key,
            'Body'        => fopen($realPath, 'rb'),
            'ContentType' => $mime,
        ]);

        // La BD sigue guardando la URL completa (contrato intacto)
        return $this->publicUrl . '/' . $key;
    }

    public function delete(string $url): void
    {
        // Protección equivalente a la de LocalFileStorage: URLs externas (picsum)
        // o legacy (/uploads/services/) no son nuestras → no-op.
        if (!str_starts_with($url, $this->publicUrl . '/')) {
            return;
        }

        $key = substr($url, strlen($this->publicUrl) + 1);
        if ($key === '') {
            return;
        }

        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);
    }
}
```

Puntos clave:
- Patrón cokalba (`region => 'auto'`, endpoint r2). Sin factory de `S3Client`: solo hay un consumidor.
- `\InvalidArgumentException` → 400 vía los `catch` ya existentes en `AdminServiceController` (`addPhoto`/`updatePhoto`). Fallos del SDK → 500 genérico vía `ApiExceptionListener` (igual que un fallo de disco hoy).
- `delete()` idempotente y protegido: `DeletePhotoHandler` llama `delete()` incondicionalmente, incluso para fotos con URL externa — esta guarda es imprescindible.

### 3. `backend/config/services.yaml`

- Línea 19 → `App\Infrastructure\Storage\FileStorageInterface: '@App\Infrastructure\Storage\CloudflareR2Storage'`
- Añadir bloque:

```yaml
App\Infrastructure\Storage\CloudflareR2Storage:
    arguments:
        $accountId: '%env(R2_ACCOUNT_ID)%'
        $accessKeyId: '%env(R2_ACCESS_KEY_ID)%'
        $accessKeySecret: '%env(R2_ACCESS_KEY_SECRET)%'
        $bucket: '%env(R2_BUCKET_NAME)%'
        $publicUrl: '%env(R2_PUBLIC_URL)%'
```

- **Conservar** la clase `LocalFileStorage` y su bloque (líneas 25-28): el autowire de `src/` la registra igualmente y sin argumentos el contenedor falla al compilar; además deja el rollback en una línea. Borrarla en un commit posterior cuando R2 lleve tiempo en producción.

### 4. Variables de entorno

- **`.env` raíz** (gitignored, valores reales — rellenar):
  ```env
  # Cloudflare R2 (fotos de servicios, S3-compatible)
  R2_ACCOUNT_ID=
  R2_ACCESS_KEY_ID=
  R2_ACCESS_KEY_SECRET=
  R2_BUCKET_NAME=dulzia-salamanca
  R2_PUBLIC_URL=https://pub-xxxxxxxxxxxxxxxxxxxxxxxxxxxx.r2.dev
  ```
- **`.env.example` raíz**: mismo bloque con placeholders y comentario.
- **`backend/.env`**: añadir 5 passthrough `${R2_*}` siguiendo el patrón de las 9 líneas existentes.
- `backend/.env.test` y `config/packages/routing.yaml`: sin cambios.
- Flujo: compose inyecta el `.env` raíz al contenedor vía `env_file: .env` (ya configurado) → `%env(R2_*)%` resuelve en runtime. No añadir nada a `environment:` de compose (misma estrategia que cokalba).

### 5. Docker Compose

- `docker-compose.yml` (dev): quitar la línea `- ./backend/public/uploads:/var/www/html/public/uploads`.
- `docker-compose.prod.yml` (prod): quitar las líneas de `volumes:` (quedan vacías al retirar el montaje de uploads).
- Sin cambios en Dockerfiles.

### 6. Limpieza de git (opcional, después de la migración a R2 verificada)

- `git rm -r --cached backend/public/uploads` (conserva los archivos en disco) — o `git rm -r` si ya no hacen falta.
- Añadir a `.gitignore` tras `backend/.env.prod`: `backend/public/uploads/`.
- Commit propio: "chore: deja de versionar uploads locales (migrado a R2)".

### 7. Documentación

- `RENDER_DEPLOY.md`: quitar la subsección de disco persistente (`uploads-disk`) y añadir las 5 `R2_*` a la lista de variables del backend con nota "fotos en Cloudflare R2; sin disco persistente".
- `README.md`: fila "Almacenamiento | Cloudflare R2 (S3 API)" en el stack; las 5 `R2_*` en la tabla de variables de la raíz; añadirlas a la sección de deploy; en el árbol, `Infrastructure/ # Email (Brevo) + Repositorios` → añadir "+ Storage (R2)".
- `PROJECT_SPEC.md`: añadir fila al stack backend "Almacenamiento de archivos | Cloudflare R2 (S3 API, aws-sdk-php)"; añadir las 5 `R2_*` a la tabla "Variables de Entorno → Backend"; anotar `Infrastructure/Storage/` en el árbol de directorios.
- `SHARED_SERVER_SETUP.md`: en la sección 2 (Dulzia) añadir paso de configurar las 5 `R2_*` en el `.env` raíz del servidor y nota de que las fotos ya no dependen de volumen local.

### 8. Este documento en `docs/` (gitignored)

- `docs/plan-r2.md` con este contenido (hecho).
- `.gitignore`: añadir `docs/` al final (sección nueva "DOCS — planes locales").

### 9. Test unitario opcional (alineado con PROJECT_SPEC "Tests")

Cuando se active PHPUnit (hoy no existe infraestructura): test de `CloudflareR2Storage` inyectando un `S3Client` con `Aws\MockHandler` (añadir parámetro opcional `?S3Client $client = null` al constructor) para cubrir: `store()` valida MIME/isValid y devuelve la URL pública; `delete()` deriva la key correcta y hace no-op con URLs externas/legacy. No se hace ahora.

### 10. Opcional (bug preexistente): `Makefile`

Los targets `logs/shell/migrate/migration-diff/cache-clear/test-*/install/composer-require/sync-vendor` referencian el servicio `backend` y el contenedor `dulziasalamanca-backend-1`, pero los nombres reales son `dulzia-backend`. Corregir esas referencias en un commit aparte si se quiere.

## Migración manual de las 5 fotos existentes (cuando exista el bucket)

Archivos: `backend/public/uploads/services/{c7e08d7580c70e92dc6e68c5a82c79f4.jpg, cf08bb5996bb950bc4afc7dc3d009580.jpg, d376489b16551ff653e671f4faeb5244.jpg, e816d69a45061464cc61c3ea58452eaa.png, f8e404e20320127285d58341b4d7fefb.jpg}`

**A — rclone** (config una sola vez):

```bash
rclone config create dulzia-r2 s3 provider "Cloudflare" \
  access_key_id "<R2_ACCESS_KEY_ID>" secret_access_key "<R2_ACCESS_KEY_SECRET>" \
  endpoint "https://<R2_ACCOUNT_ID>.r2.cloudflarestorage.com" acl "private"

rclone copy backend/public/uploads/services dulzia-r2:<R2_BUCKET_NAME>/services --checksum --verbose
rclone lsl dulzia-r2:<R2_BUCKET_NAME>/services   # 5 keys: services/<nombre>
```

El destino debe llevar el sufijo `/services` para que las keys coincidan con lo que genera el código.

**B — SQL UPDATE** (tabla `service_example`, columna `image_url`):

```sql
UPDATE service_example
SET image_url = REPLACE(image_url,
    'https://dulziasalamanca.es/uploads/services/',
    '<R2_PUBLIC_URL>/services/')
WHERE image_url LIKE 'https://dulziasalamanca.es/uploads/services/%';
```

En producción (la BD vive en el contenedor de infra compartido):

```bash
docker exec -i shared-postgres-db psql -U dulzia -d dulzia -c "UPDATE service_example SET image_url = REPLACE(image_url, 'https://dulziasalamanca.es/uploads/services/', '<R2_PUBLIC_URL>/services/') WHERE image_url LIKE 'https://dulziasalamanca.es/uploads/services/%';"
```

Verificar: `SELECT id, image_url FROM service_example ORDER BY id;` — solo URLs R2 y picsum.

**Orden en producción**: (1) rclone upload → (2) UPDATE SQL → (3) `docker compose -f docker-compose.prod.yml up -d --build` con el `.env` del servidor ya actualizado con R2_*. Es seguro: en la ventana SQL→deploy el backend viejo no borra URLs ya migradas (el `delete()` de LocalFileStorage hace no-op si la URL ya no contiene `/uploads/services/`).

## Verificación

Local (con el `.env` local relleno con credenciales reales — igual que cokalba, dev y prod comparten el bucket):

1. `docker compose up -d --build && docker compose restart dulzia-backend`; `docker compose logs dulzia-backend` sin errores de compilación de container (env mal configurada = fallo aquí).
2. **Subir foto** desde `http://localhost:5173/dulzia-panel` → respuesta con `imageUrl = https://pub-….r2.dev/services/<hex>.<ext>`; abrir la URL → 200; objeto visible en el panel de Cloudflare.
3. **Update con archivo nuevo** → el objeto viejo desaparece del bucket y aparece el nuevo.
4. **Delete** → objeto eliminado del bucket y fila eliminada.
5. **URL externa (picsum)**: añadir foto solo con `imageUrl` → funciona; borrarla → no-op en R2, fila eliminada (sin errores).
6. **Caso negativo**: subir un `.txt` → 400 `Tipo de archivo no permitido`.
7. Alternativa curl: `curl -X POST http://localhost:8000/api/admin/services/<id>/photos -H "Authorization: Bearer <token>" -F "title=T" -F "description=D" -F "image=@foto.jpg"`.

Producción: web carga fotos desde R2; panel permite subir/editar/borrar; URLs antiguas `/uploads/services/…` devuelven 404.

## Rollback

1. `services.yaml` línea 19 → alias de vuelta a `LocalFileStorage`.
2. Re-añadir los volúmenes de uploads en ambos compose.
3. SQL inverso: `UPDATE service_example SET image_url = REPLACE(image_url, '<R2_PUBLIC_URL>/services/', 'https://dulziasalamanca.es/uploads/services/') WHERE image_url LIKE '<R2_PUBLIC_URL>/services/%';`
