# Guía: Configuración del Servidor Compartido

Este documento te guía en cómo desplegar la infraestructura compartida y conectar los 3 proyectos en tu servidor.

## Arquitectura de Carpetas Recomendada

En tu servidor, crea una carpeta principal (por ejemplo `mis-proyectos`) donde alojarás todo. Debería quedar así:

```text
/mis-proyectos
  ├── infra                   <-- (Contiene la BD compartida PostgreSQL)
  ├── dulziasalamanca         <-- (Proyecto principal)
  ├── mediatools              <-- (Segundo Proyecto)
  └── n8n                     <-- (Tercer Proyecto)
```

---

## 1. Arrancar la Base de Datos Central (`infra`)

Antes de iniciar cualquier aplicación, debes arrancar la base de datos compartida porque proporciona la red y la conexión que todos necesitan:

1. Entra a la carpeta `infra`:
   ```bash
   cd /mis-proyectos/infra
   ```
2. Configura las contraseñas copiando el ejemplo y editándolo:
   ```bash
   cp .env.example .env
   nano .env
   ```
3. Levanta la infraestructura:
   ```bash
   docker compose up -d
   ```
> [!NOTE]
> Al arrancar por primera vez, el script `init.sh` **creará automáticamente** las bases de datos y usuarios (`dulzia`, `mediatools`, `n8n`) utilizando las contraseñas que hayas colocado en `.env`.

---

## 2. Iniciar Dulzia Salamanca

El archivo `docker-compose.yml` de este repositorio ya está modificado. Sólo tienes que asegurarte de configurar las variables de entorno para que el proyecto sepa cómo conectarse a la nueva base de datos.

1. Ve al directorio del proyecto `dulziasalamanca`:
   ```bash
   cd /mis-proyectos/dulziasalamanca
   ```
2. En tu archivo `.env` del **backend**, asegúrate de que la variable `DATABASE_URL` utiliza el nuevo host y contraseña:
   * **Host:** `shared-postgres-db`
   * **Contraseña:** Debe ser la misma que pusiste en `DULZIA_DB_PASS` en el `.env` de la carpeta `infra`.
   ```env
   # Ejemplo en backend/.env o raíz:
   POSTGRES_PASSWORD=tu_contraseña_de_dulzia
   ```
3. Inicia el servidor mediante `make`:
   ```bash
   make up
   ```

---

## 3. Conectar los otros Proyectos (Mediatools y N8N)

Para añadir los próximos proyectos, sus archivos `docker-compose.yml` deben cumplir con 2 condiciones muy similares a las de Dulzia:

### Condición A: Unirse a la Red Compartida
Añadir este bloque al final del archivo `docker-compose.yml` de cada nuevo proyecto:
```yaml
networks:
  app-network:      # Puedes llamarla como quieras dentro del proyecto, pero debe ser external
    name: shared-network
    external: true
```

Y luego asegúrate de que sus servicios (backend de mediatools, servicio de n8n, etc.) se conecten a la red, añadiendo en esa parte del config:
```yaml
    networks:
      - app-network
```

### Condición B: Apuntar al Host Central de Base de Datos
Indicar a cada aplicación la URL de conexión usando el nombre `shared-postgres-db`.

**Para Mediatools:**
```env
DATABASE_URL=postgresql://mediatools:<CONTRASEÑA>@shared-postgres-db:5432/mediatools
```

**Para n8n:**
```env
# n8n suele usar variables separadas o DB_URL, dependiendo de la configuración:
DB_TYPE=postgresdb
DB_POSTGRESDB_DATABASE=n8n
DB_POSTGRESDB_HOST=shared-postgres-db
DB_POSTGRESDB_PORT=5432
DB_POSTGRESDB_USER=n8n
DB_POSTGRESDB_PASSWORD=<CONTRASEÑA>
```

---

## ¿Cómo migrar base de datos local previa? (Opcional)

Si en tu proyecto anterior de Dulzia tuvieras datos que quisieras preservar, puedes dumpearlos y luego subirlos al nuevo host:
1. Con PostgreSQL antiguo levantado: `docker exec -t dulziasalamanca-postgres-1 pg_dumpall -c -U dulzia > dump.sql`
2. Copias el archivo al servidor mediante SFTP y en el nuevo sistema lo restauras: `cat dump.sql | docker exec -i shared-postgres-db psql -U postgres`
