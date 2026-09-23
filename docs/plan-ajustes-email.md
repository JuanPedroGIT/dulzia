# Plan: configurar el email de contacto desde el panel de admin

> Plan de implementación — fichero versionado en `docs/` del repo.
> Fecha: 2026-09-23 · Proyecto: dulziasalamanca · Estado: **en ejecución**

## Contexto

Hoy el destinatario de los emails del formulario de contacto vive en variables de entorno
(`MAILER_TO_EMAIL` / `MAILER_TO_NAME` en el `.env` raíz), así que cambiar algo tan cotidiano como
"quiero que los mensajes lleguen a esta otra dirección" obliga a editar ficheros y reiniciar
contenedores.

**Objetivo:** configurarlo desde el panel, dentro del apartado de mensajes, con una tabla nueva en
base de datos. El `.env` se queda como **respaldo**: el valor efectivo es
`BD si no está vacío, si no el del .env`. Vaciar un campo en el panel devuelve el control al `.env`.

**Decisiones tomadas:**

1. Tabla **clave/valor genérica** (`settings`), no una tabla dedicada de contacto — un ajuste futuro
   es una fila más, sin migración.
2. El panel edita **dos campos**: email destinatario y nombre del destinatario.
3. **No se crea ningún `.env` nuevo.** El proyecto ya tiene dos y se quedan como están: el raíz
   (gitignored, único sitio con los `MAILER_*`) y `backend/.env` (gitignored, lleva
   `DATABASE_URL`/`APP_SECRET` para el contenedor, sin `MAILER_*`). El respaldo se lee del mecanismo
   de env que ya existe hoy.

---

## Estado de ejecución

> Leyenda: ⬜ Pendiente · 🔄 En curso · ✅ Hecha

| Etapa | Estado | Fecha |
|---|---|---|
| 0 — Guardar este plan en `docs/plan-ajustes-email.md` | ✅ Hecha | 2026-09-23 |
| 1 — Tabla `settings` y capa de datos | ✅ Hecha | 2026-09-23 |
| 2 — Resolución BD → `.env` y cableado al mailer | ✅ Hecha | 2026-09-23 |
| 3 — API de admin (`GET`/`PUT`) | ✅ Hecha | 2026-09-23 |
| 4 — Panel de administración | ✅ Hecha | 2026-09-23 |
| 5 — Verificación end-to-end y documentación | 🔄 En curso (falta la prueba manual en navegador) | |

---

## Diseño transversal

### Dónde se lee la configuración (la decisión clave)

`BrevoMailer` es un servicio singleton cuyos valores vienen de parámetros `%env()%` resueltos **al
compilar el contenedor**, así que la BD hay que leerla **en cada envío**.

El propio `BrevoMailer` ya marca el patrón a seguir: delega el renderizado en `ContactMailRenderer`
para no mezclar responsabilidades. Hacemos lo mismo con el destinatario → un
**`ContactRecipientResolver`** que resuelve `BD → .env`, inyectado en el mailer.

El resolver va en **`Domain/Settings/`**, no en Application: lo consumen tanto `BrevoMailer`
(Infrastructure) como el handler del GET (Application). Ponerlo en Application obligaría a que
Infrastructure dependa de Application, una inversión que este código no hace en ningún sitio
(Infrastructure y Application dependen los dos de Domain; los controllers de Application). Es una de
las pocas clases concretas en `Domain/`, y se justifica porque es lógica de dominio sin framework:
solo depende del puerto `SettingsRepositoryInterface`, de `Psr\Log\LoggerInterface` y de dos strings.

Los **seis valores por defecto hardcodeados** del constructor de `BrevoMailer`
(`= 'salumvi@gmail.com'`, `= 'Dulzia Salamanca Eventos'`, …) **se eliminan**. Hoy son una tercera
fuente de verdad que duplica el `.env`; con el resolver habría tres sitios de los que puede salir el
destinatario, justo la ambigüedad que este cambio debe eliminar. No rompe nada: `services.yaml`
siempre pasa los seis argumentos, y en el entorno de test `MailerInterface` está aliasado a
`NullMailer`, así que `BrevoMailer` ni se instancia.

### Contrato de la API

```
GET /api/admin/settings/contact-recipient
{ "email": "salumvi@gmail.com", "name": "Dulzia Salamanca Eventos",
  "email_source": "env", "name_source": "env" }

PUT /api/admin/settings/contact-recipient   body: { "email": "…", "name": "…" }
→ 200 { "ok": true }   (como el resto de mutaciones admin)
→ 422 { "errors": { "email": ["El email no es válido"] } }
```

`email`/`name` son siempre los **valores efectivos** (los que usará el mailer), y `*_source` dice si
vienen de la BD o del `.env`. Eso es lo que permite mostrar la verdad en el panel sin sembrar filas.

**Por qué un endpoint específico y no un `GET/PUT /api/admin/settings` genérico:** en este código la
validación vive en Commands tipados con atributos `#[Assert]` (`SubmitContactCommand`,
`ServiceCommandFactory`). Un mapa genérico no puede llevar constraints estáticas sin un validador
dinámico por clave, y el formato de email es la regla no negociable. La **tabla** sigue siendo
genérica; la API crece un par handler/command por ajuste, que es el coste normal de un caso de uso aquí.

---

## Etapa 0 — Guardar el plan en `docs/` ✅ (2026-09-23)

Crear `docs/plan-ajustes-email.md` con este contenido, incluida la sección **Estado de ejecución**,
que se va actualizando al terminar cada etapa. De paso se corrigió el encabezado obsoleto de
`docs/plan-r2.md`, que afirmaba que `docs/` estaba gitignored cuando sí está versionado.

**Criterios de aceptación**
- El fichero existe en `docs/` y su tabla de estado refleja la etapa en curso. ✅

## Etapa 1 — Tabla `settings` y capa de datos ✅ (2026-09-23)

| Fichero | Contenido |
|---|---|
| `backend/migrations/Version20260923120000.php` | DDL de `settings`. Estilo de `Version20260922103000`: SQL crudo con `addSql()`, `getDescription()` en inglés. Ordena después de `Version20260922103000`, así que aplica limpio |
| `backend/src/Entity/Setting.php` | Entidad `#[ORM\Entity] #[ORM\Table(name: 'settings')]`. **PK en `key`** (la clave *es* la identidad; el índice único pedido es el de la PK) y columnas `key`/`value`/`updated_at`. `key` y `value` **sin comillas**: DBAL no los considera palabras reservadas de PostgreSQL y así coinciden con lo que generaría `migration-diff`. `value` es `TEXT`. Propiedades privadas, sin setters, `setValue()` actualizando `updatedAt` — estilo `ContactSubmission` |
| `backend/src/Domain/Settings/SettingKey.php` | `CONTACT_RECIPIENT_EMAIL = 'contact_recipient_email'` y `CONTACT_RECIPIENT_NAME = 'contact_recipient_name'`. Verificado: no chocan con nada del repo. Imprescindible: sin él, el literal vive en dos sitios y una errata degrada en silencio a "usar el .env" |
| `backend/src/Domain/Settings/SettingsRepositoryInterface.php` | Puerto mínimo: `get(string $key): ?string` (devuelve `null` si la clave no existe **o su valor está vacío**), `set(string $key, string $value): void`, `delete(string $key): void` (no-op si no existe, para que vaciar sea idempotente). Sin `all()`: nada lo consume y cada método del puerto es contrato que todos los dobles deben implementar |
| `backend/src/Infrastructure/Repository/DoctrineSettingsRepository.php` | `get()` con DQL escalar (`SELECT s.value FROM App\Entity\Setting s WHERE s.key = :key`); `set()` con **`INSERT … ON CONFLICT (key) DO UPDATE`** por SQL crudo → upsert atómico, sin `flush()` dentro de un handler y sin riesgo de `UniqueConstraintViolationException` como 500. `updated_at` se pasa desde PHP (no `NOW()`) para que no discrepe de la zona horaria con la que Doctrine escribe el resto de timestamps |
| `backend/tests/Unit/Entity/SettingTest.php` | Creación y `setValue()` actualizando `updatedAt` |

**Edición obligatoria:** `backend/tests/Integration/IntegrationTestCase.php:32` — añadir `'settings'` a
`TABLES`. El schema de test se crea solo desde los mappings de Doctrine, pero el `TRUNCATE` recorre esa
lista: sin añadirla, las filas que escriba un test se filtran a todos los siguientes. Es la forma más
probable de tener una suite verde y luego inestable.

La entidad es necesaria aunque el repositorio escriba con SQL crudo, precisamente porque
`ensureSchema()` construye el schema de test desde los mappings.

**Criterios de aceptación**
- `make migrate` crea la tabla y `doctrine:schema:validate` no reporta desajustes entre migración y mapping.
- `dbal:run-sql "SELECT * FROM settings"` devuelve 0 filas sin error.
- Test de entidad en verde.

> Nota sobre las etapas 2 y 3: sus criterios de aceptación automatizados están cubiertos
> (`make test-unit` 107 tests, `make test-integration` 43 tests, en verde). Los dos criterios que
> exigen un envío real de email se comprueban en la etapa 5, porque en local no se puede enviar sin
> usar la API de Brevo de verdad.

## Etapa 2 — Resolución BD → `.env` y cableado al mailer ✅ (2026-09-23)

| Fichero | Contenido |
|---|---|
| `backend/src/Domain/Settings/ContactRecipient.php` | VO `readonly`: `email`, `name`, `emailFromSettings`, `nameFromSettings` |
| `backend/src/Domain/Settings/ContactRecipientResolver.php` | `resolve(): ContactRecipient` — lee las dos claves, cae al valor del `.env` cuando son `null`, y **envuelve la lectura en try/catch**: si la tabla no existe todavía o la BD falla, registra un `error` y devuelve el valor del `.env`. Sin esto, un problema en `settings` dejaría al negocio sin recibir los mensajes del formulario |
| `backend/tests/Unit/Domain/Settings/ContactRecipientResolverTest.php` | El test más importante, y el que hoy no se puede escribir (justifica el refactor) |

`backend/config/services.yaml` — tres cambios:
- bind del puerto nuevo junto a los demás (líneas 24-32):
  `App\Domain\Settings\SettingsRepositoryInterface: '@App\Infrastructure\Repository\DoctrineSettingsRepository'`
- bloque `App\Domain\Settings\ContactRecipientResolver:` con `$defaultEmail: '%mailer.to_email%'` y
  `$defaultName: '%mailer.to_name%'` → **los parámetros existentes se reutilizan tal cual**; el `.env` no se toca
- en el bloque `BrevoMailer:` quitar `$toEmail`/`$toName` de los `arguments`

`backend/src/Infrastructure/Email/BrevoMailer.php` — inyecta `ContactRecipientResolver` y usa
`$contact->email` / `$contact->name` en el `'to'` de la notificación al negocio. Se van los parámetros
`$toEmail`/`$toName` y los seis literales por defecto.

**Criterios de aceptación**
- Con la tabla vacía, `make logs` no muestra errores y el envío usa el destinatario del `.env`.
- Con una fila insertada a mano, el email llega a la dirección de la fila.
- `make test-unit` en verde, incluidos los casos "sin fila", "fila vacía" y "el repositorio lanza excepción".

## Etapa 3 — API de admin ✅ (2026-09-23)

| Fichero | Contenido |
|---|---|
| `backend/src/Application/Settings/GetContactRecipient/{Query,Handler}.php` | La Query es una `final readonly class` vacía (patrón de `ListServicesQuery`). El handler usa el resolver y devuelve valores **efectivos** + de dónde vienen |
| `backend/src/Application/Settings/UpdateContactRecipient/{Command,Handler}.php` | Command `readonly` con `Assert\Email` + `Assert\Length(max: 255)` en `string $email` y `Assert\Length(max: 150)` en `string $name`. **Sin `NotBlank`**: vacío es un valor válido aquí (= volver al `.env`), y el validador de Symfony ya ignora `null` y `''`. El handler hace `trim()` y, si queda vacío, `delete()`, si no `set()` |
| `backend/src/Controller/AdminSettingsController.php` | `GET`/`PUT /api/admin/settings/contact-recipient`; valida el command con `ValidatorInterface` y lanza `ValidationFailedException` (→422 por `ApiExceptionListener`), igual que `AdminContactController`. **La auth es automática**: `AdminAuthListener` ya protege todo `/api/admin` |
| `backend/tests/Unit/Application/Settings/UpdateContactRecipientHandlerTest.php` | Con doble del puerto: con valor → `set()`; vacío → `delete()`; y que recorta espacios |
| `backend/tests/Unit/Application/Settings/UpdateContactRecipientCommandTest.php` | Validación sin framework (`Validation::createValidatorBuilder()->enableAttributeMapping()`): `('', '')` → **0 violaciones** (es la aserción que sostiene la semántica de vaciar), `'no-es-email'` → 1 violación, email de 256 y nombre de 151 caracteres → violación |
| `backend/tests/Integration/Controller/AdminSettingsControllerTest.php` | 401 sin token; tabla vacía → `email` igual a `getenv('MAILER_TO_EMAIL')` y `email_source === 'env'` (assertar contra `getenv()` y no contra un literal hace el test independiente del `.env` de cada uno); PUT → 200, fila en BD, GET devuelve `'db'`; PUT con `''`/`''` → filas borradas y vuelta a `'env'`; PUT con email inválido → 422 |

**Criterios de aceptación**
- `make test-unit && make test-integration` en verde.
- `curl` con token contra el GET devuelve los valores efectivos con su `*_source`.
- Un `PUT` con email inválido devuelve 422 y no escribe nada.

## Etapa 4 — Panel de administración ✅ (2026-09-23)

**Nuevos**
- `frontend/src/composables/useEmailSettings.js` — espejo de `useMessages.js`: `email`, `name`,
  `emailSource`, `nameSource`, `loading`, `saving`, `error`; `fetch()` captura el error internamente
  y lo deja en `error.value`; `save()` **no** captura pero sí tiene `finally` (convención del repo: la
  lectura se traga el error, la escritura lo propaga y la página decide).
- `frontend/src/pages/admin/AdminEmailSettingsPage.vue` — esqueleto de `AdminMessageDetailPage.vue`
  (raíz `min-height:100vh;background:#f7f4f1`, `.admin-page-header` > `.admin-header__inner` max-width
  900px con `.btn-back` a `/dulzia-panel/mensajes`, `.admin-main`, bloque de tres estados
  `loading`/`error`/contenido) y estilos de formulario de `AdminDashboardPage.vue` (`.form-label`,
  `.btn-save` rosa `#c8748a`, `:disabled="saving"`, etiqueta `'Guardando…'`). Tarjeta
  `white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06)`, max-width ~560px.
  - `<input type="email">` (sin `required`) es la guarda principal: el navegador bloquea un email
    malformado y permite vacío, espejo de la regla del servidor. Necesario porque `handleResponse` no
    sabe leer los 422 de validación (ver el arreglo de abajo).
  - Bajo cada campo, el origen: `Configurado en el panel` / `Valor por defecto del servidor`. No es
    decoración: evita la trampa de "por qué ya no me hace caso el `.env`" tras guardar.
  - Botón **Restablecer** (vacía los dos campos y guarda → borra las filas → vuelven los valores del
    `.env`): hace descubrible la semántica de vaciar en vez de dejarla como folklore.
  - Tras guardar, re-`fetch()` (los valores efectivos pueden haber cambiado al vaciar un campo) y
    avisos inline (`saveError`/`saved`), como el `.modal-error` del dashboard, sin `alert`.
- `frontend/__tests__/useEmailSettings.spec.js` — con `vi.mock` del servicio y `vi.clearAllMocks`,
  como `useMessages.spec.js`.

**Editados**
- `frontend/src/services/adminService.js` — sección `// ── Ajustes de email ──` con
  `apiGetContactRecipient()` y `apiUpdateContactRecipient(data)` (PUT con `{ ...headers(), 'Content-Type' }`).
  Además, un arreglo de 3 líneas en `handleResponse`: hoy solo lee `data.error`, así que un 422 del
  backend (`{errors: {...}}`) llega al panel como el texto `"Error 422"`. Añadir
  `const fieldErrors = data.errors && Object.values(data.errors).flat().filter(Boolean)` y usar
  `data.error || fieldErrors?.[0] || \`Error ${res.status}\`` mejora el mensaje en **todos** los
  formularios del admin, no solo en este.
- `frontend/src/router/index.js` — `{ path: '/dulzia-panel/ajustes-email', component: () => import('@/pages/admin/AdminEmailSettingsPage.vue'), meta: { requiresAuth: true } }`, insertado después de `mensajes/:id` y **antes del catch-all**. Ruta hermana, no `/dulzia-panel/mensajes/ajustes` (ocupada por `:id`). El `meta.requiresAuth` es obligatorio o el guard no redirige.
- `frontend/src/pages/admin/AdminMessagesPage.vue` — enlace "Ajustes de email" en la cabecera (líneas 21-27), después del `.header-badge`, con un `.btn-config` clonado de `.btn-back`. Es el apartado de mensajes pedido.
- `frontend/src/pages/admin/AdminDashboardPage.vue` — opcional: un `router-link` más en `.toolbar__actions` (líneas 84-90) junto a "📩 Mensajes", para llegar también desde el panel principal.

**Criterios de aceptación**
- `make test-frontend` en verde.
- Desde `/dulzia-panel/mensajes` llego a la página de ajustes, veo el destinatario actual con su origen, lo cambio y al guardar la etiqueta pasa a "Configurado en el panel".
- Vaciar los campos y guardar deja la tabla en 0 filas y la etiqueta vuelve a "Valor por defecto del servidor".

## Etapa 5 — Verificación end-to-end y documentación 🔄 (2026-09-23)

**Hecho:**
- `make test` completo en verde: 107 unitarios (368 aserciones), 43 de integración (158
  aserciones), 15 de frontend.
- `make migrate` aplicado en local; `doctrine:schema:validate` sin desajustes entre migración y
  mapping. La tabla `settings` existe y está vacía.
- Los dos endpoints responden **401** sin token, tanto por el backend (`:8000`) como a través del
  proxy del frontend (`:5173`): la ruta está registrada y protegida por `AdminAuthListener`.
- `npm run build` de producción correcto: emite `AdminEmailSettingsPage-*.js` y el prerender sigue
  diciendo "All routes rendered successfully!".
- Documentación: comentarios en `.env` y `.env.example`, y sección "Ajustes desde el panel" en el
  README con las reglas BD-vs-`.env`.

**Pendiente (necesita navegador y envío real de email):**
1. Entrar en `/dulzia-panel/mensajes` → "⚙️ Ajustes de email" y ver que muestra el destinatario
   actual con la etiqueta "Valor por defecto del servidor", coincidiendo con el `.env`.
2. Cambiar a un buzón real + nombre → Guardar → la etiqueta pasa a "Configurado en el panel", y
   `docker exec -i shared-postgres-db psql -U postgres -d dulzia -c "select * from settings"` debe
   mostrar las dos filas.
3. Enviar el formulario público de `/contacto`: la notificación debe llegar a la dirección nueva
   (esto además valida el remitente de Brevo, que es la tarea pendiente del interesado).
4. "Restablecer" → la tabla queda en 0 filas y la etiqueta vuelve a "Valor por defecto del servidor".

Los pasos 1, 2 y 4 son también la comprobación de que el panel funciona; el 3 es el único que no
puede automatizarse sin enviar correo de verdad.



```bash
make up
make cache-clear      # el contenedor recompila services.yaml con el servicio nuevo
make test             # unit + integración + frontend
docker compose exec dulzia-backend php bin/console doctrine:schema:validate
```

Comprobación manual end-to-end:
1. `/dulzia-panel/mensajes` → "Ajustes de email" → la página muestra el destinatario actual con la
   etiqueta "Valor por defecto del servidor", y coincide con el `MAILER_TO_EMAIL` del `.env` raíz.
2. Cambiar a un buzón real que se pueda leer + un nombre → Guardar → "Guardado" y etiqueta
   "Configurado en el panel".
3. Enviar el formulario público de `/contacto`: la notificación llega a la **dirección nueva** y la
   confirmación al visitante sigue llegando a la dirección que escribió en el formulario.
4. Vaciar los dos campos → Guardar → la etiqueta vuelve a "Valor por defecto del servidor" y
   `SELECT * FROM settings` devuelve 0 filas. Enviar otra vez → vuelve al destinatario del `.env`.
5. Ruta de fallo: en local, `dbal:run-sql "DROP TABLE settings"` y enviar el formulario → el mensaje
   **sigue llegando** al destinatario del `.env` y `make logs` muestra el error "No se pudieron leer
   los ajustes". Recrear la tabla con `make migrate` después.

Documentación:
- `.env.example` y `.env`: un comentario en las dos líneas `MAILER_TO_*` aclarando que son el valor
  por defecto y que el panel puede sobrescribirlos. **Sin cambiar valores ni borrar variables.**
- README: nota sobre los dos puntos que sorprenden (riesgos R3 y R5).

**Criterios de aceptación:** los 5 pasos manuales se cumplen, `make test` en verde y el
README/`.env.example` actualizados.

---

## Riesgos

- **Tabla ausente en producción** (migración sin ejecutar): el resolver lo captura y usa el `.env`,
  así que **el formulario de contacto nunca se queda sin enviar**; solo falla la página de ajustes.
  Es deliberado: la alternativa estricta (propagar la excepción) haría que `SubmitContactHandler`
  saltara el envío y marcara `email_sent = false`, dejando al negocio sin los mensajes por un fallo
  del panel.
- **Trampa del valor fijado (R5)**: guardar el formulario sin cambiar nada *fija* el valor del `.env`
  como override en BD, y a partir de ahí el `.env` deja de mandar. Lo mitigan la etiqueta de origen y
  el botón Restablecer; va documentado en el README.
- **Cambiar el `.env` en producción (R3)**: el fallback se lee por el mismo `%env()%` de siempre, así
  que no hay regresión, pero en prod (`opcache.validate_timestamps=0`) editar `MAILER_TO_EMAIL` exige
  `make cache-clear` **y reiniciar el contenedor**. Ahora se nota más porque se va a alternar entre BD
  y `.env`. Merece una línea de README.
- **Un destinatario válido pero equivocado** recibe datos de clientes (nombres, teléfonos, mensajes).
  `Assert\Email` evita errores de sintaxis, no una dirección mal escrita: de ahí que el panel muestre
  siempre el valor efectivo. Nota: Brevo rechazando el destinatario aborta `sendContactNotification`
  antes de enviar la confirmación al visitante — comportamiento preexistente que este cambio no toca.
- **`failOnWarning`/`failOnNotice`/`failOnDeprecation` están a `true`**: cualquier warning de PHP
  tumba la suite. Cuidado con casts `(string)` sobre arrays y claves inexistentes.
- **Prerender**: `vite build` solo prerenderiza `/`, `/servicios`, `/nosotros`, `/contacto`. La ruta
  nueva va con `import()` dinámico y no interfiere; verificar que sigue diciendo "All routes rendered
  successfully!".
- **`settings` es global**, no por entorno: si algún día un staging apunta a la misma BD `dulzia`,
  hereda el destinatario. Aceptable, pero anotado.

### Gap conocido

`BrevoMailer` no tiene test (hace `curl`, y en test está aliasado a `NullMailer`). La lógica de
resolución queda cubierta por el test del resolver; el uso de una línea en el mailer, por la
comprobación manual de la etapa 5.

## Producción (cuando corresponda)

`git pull` → `make prod-up` (reconstruye la imagen del frontend con la página nueva) → `make migrate`
(crea la tabla). Sin el `migrate`, el resolver cae al `.env` y el flujo de contacto sigue funcionando,
pero la página de ajustes daría error.
