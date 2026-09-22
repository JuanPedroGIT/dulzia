# PRD — Gestión de mensajes de contacto en el panel de administración

> Estado: **Fase 1 implementada** (2026-09-22) — Fases 2 y 3 pendientes.
> Implementación prevista por fases (ver "Plan por fases").

---

## 1. Contexto y problema

El formulario público (`POST /api/contact`) guarda cada envío en
`contact_submission` y, por email, avisa al negocio y confirma al remitente.

Problemas actuales:

1. **El panel admin no muestra los mensajes.** Solo se pueden consultar a
   mano en la base de datos. La spec de proyecto menciona "gestionar mensajes
   recibidos", pero no está implementado.
2. **Si el email a Brevo falla** (diseño no-fatal), el mensaje queda solo en
   BD con `email_sent = false` y hasta ahora no dejaba traza. Con el logging
   recién añadido hay traza en stderr, pero sigue sin ser visible desde el panel.
3. **No hay estado de "leído"**: aunque el negocio abra el email, no queda
   registrado qué mensajes están pendientes de gestionar.

## 2. Objetivos

- Ver desde el panel todos los mensajes recibidos (orden: más reciente primero).
- Saber de un vistazo cuántos mensajes nuevos (no leídos) hay.
- Marcar mensajes como leídos / no leídos.
- Borrar mensajes.
- Ver el estado del email (`email_sent` / `email_sent_at`) para detectar los
  que no llegaron y poder **reenviarlos** desde el panel.
- Hacer todo esto siguiendo la arquitectura actual (hexagonal + CQRS ligero,
  patrón pages → composables → services en frontend).

## 3. Fuera de alcance (no objetivos de este PRD)

- Responder al cliente por email desde el panel (se reenvía la notificación,
  no se redactan respuestas) — posible fase futura.
- Adjuntos, plantillas de respuesta, multi-admin, websockets/tiempo real.
- Exportación CSV/Excel — posible fase futura.

## 4. Requisitos funcionales

| ID | Requisito | Prioridad |
|---|---|---|
| FR-1 | Listar mensajes paginados (20 por página), orden `submitted_at` DESC | Must |
| FR-2 | Ver detalle de un mensaje (nombre, email, teléfono, tipo de evento, mensaje, IP, fecha, estado email) | Must |
| FR-3 | Marcar como leído / no leído | Must |
| FR-4 | Badge en el panel con el número de mensajes no leídos | Must |
| FR-5 | Borrar un mensaje (con confirmación en UI) | Must |
| FR-6 | Mostrar estado del email (`Enviado` / `No enviado`) y fecha de envío | Must |
| FR-7 | Reenviar la notificación del mensaje al negocio desde el panel | Should |
| FR-8 | Filtrar por leído / no leído / todos y por texto (nombre, email, mensaje) | Should |
| FR-9 | Acciones rápidas en detalle: copiar email, abrir `mailto:` | Could |

## 5. Requisitos no funcionales

- **Auth**: todos los endpoints bajo `/api/admin/messages*` quedan protegidos
  automáticamente por `AdminAuthListener` (token Bearer) — cero trabajo extra.
- **Validación**: comandos/queries con `#[Assert]` según patrón existente;
  errores 422/404 mapeados por `ApiExceptionListener` sin tocarlo.
- **BD**: una migration nueva (`contact_submission` + campo `read_at`);
  nunca modificar migrations ya ejecutadas en producción.
- **Rendimiento**: paginación server-side (Doctrine `setMaxResults`), no
  traerse todos los mensajes.
- **Tests**: unit por handler + integración WebTestCase por endpoint
  (sin tocar Brevo ni R2, como siempre).

## 6. Backend propuesto

### 6.1 Modelo de datos

Migration nueva que añade a `contact_submission`:

```
read_at   datetime_immutable  NULL   -- NULL = no leído
```

### 6.2 Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/admin/messages` | Lista paginada: `?page=1&status=all|unread|read&q=texto`. Respuesta: `{items: [...], page, totalPages, total, unreadCount}` |
| GET | `/api/admin/messages/{id}` | Detalle (404 si no existe) |
| POST | `/api/admin/messages/{id}/read` | Marcar leído |
| POST | `/api/admin/messages/{id}/unread` | Marcar no leído |
| DELETE | `/api/admin/messages/{id}` | Borrar |
| POST | `/api/admin/messages/{id}/resend` | Reenviar notificación al negocio vía `MailerInterface` y actualizar `email_sent` |

`unreadCount` en la respuesta del listado evita un endpoint aparte de stats
(el badge lo usa sin petición extra).

### 6.3 Estructura (hexagonal, una carpeta por caso de uso)

```
Application/Contact/
├── ListMessages/       # Query + Handler (paginación, filtros)
├── GetMessage/         # Query + Handler
├── MarkMessageRead/    # Command + Handler
├── MarkMessageUnread/  # Command + Handler
├── DeleteMessage/      # Command + Handler
└── ResendMessage/      # Command + Handler (reutiliza MailerInterface)
Domain/Contact/
└── ContactRepositoryInterface   # + findPaginated, find, markRead, markUnread, remove
Infrastructure/Repository/
└── DoctrineContactRepository    # implementa los métodos nuevos
Controller/
└── AdminContactController       # solo deserializa/valida y responde
Entity/ContactSubmission.php     # + $readAt, markRead(), markUnread(), isRead()
```

Fase 1 puede agrupar read/unread en un solo caso de uso `MarkMessageRead`
(parámetro `bool $read`) si se prefiere menos carpetas — decisión de
implementación.

## 7. Frontend propuesto

```
pages/admin/
├── AdminMessagesPage.vue        # lista + filtros + paginación + badge origen
└── AdminMessageDetailPage.vue   # detalle + acciones (leído/no leído, borrar, reenviar)
composables/
└── useMessages.js               # { messages, filters, pagination, actions }
services/adminService.js        # + apiGetMessages, apiGetMessage, apiMarkRead,
                                #   apiMarkUnread, apiDeleteMessage, apiResendMessage
router/index.js                 # /dulzia-panel/mensajes y /dulzia-panel/mensajes/:id
                                # (meta.requiresAuth — ya cubiertas por el guard)
AdminDashboardPage.vue          # enlace "Mensajes" + badge con unreadCount
```

- El listado obtiene `unreadCount` en la misma respuesta y lo expone para el
  badge del dashboard (un fetch ligero al entrar en el panel).
- El detalle puede ser página aparte o modal según lo que se prefiera en
  implementación (propuesta: página aparte, como `AdminServiceDetailPage`).

## 8. Plan por fases (paso a paso)

### Fase 1 — MVP: ver y gestionar lo básico ✅ Implementada (2026-09-22)

> Decisión de implementación aplicada: read/unread agrupados en un solo caso
> de uso `MarkMessageRead` (parámetro `bool $read`), como permitía el PRD.

1. Migration `read_at` en `contact_submission`.
2. `ContactSubmission::markRead()/markUnread()/isRead()` + tests de entidad.
3. `ContactRepositoryInterface` + `DoctrineContactRepository`: `findPaginated`,
   `find`, `markRead`/`markUnread`, `remove`.
4. Handlers: `ListMessages`, `GetMessage`, `MarkMessageRead/Unread`,
   `DeleteMessage` + tests unitarios (repositorio mockeado).
5. `AdminContactController` + tests de integración WebTestCase por endpoint.
6. Frontend: `adminService` + `useMessages` + `AdminMessagesPage` +
   `AdminMessageDetailPage` + rutas + enlace/badge en el dashboard.
7. Test Vitest de `useMessages`.

**Criterios de aceptación Fase 1**
- Desde el panel veo la lista paginada de mensajes, más reciente primero.
- Puedo abrir un mensaje, marcarlo leído/no leído y borrarlo (con confirmación).
- El badge del dashboard muestra los no leídos y se actualiza tras marcar.
- `make test` en verde (unit + integración + frontend).

### Fase 2 — Filtros y búsqueda

1. Filtros `status` y `q` en `ListMessages` (Doctrine criteria) + tests.
2. UI: pestañas Todos/No leídos/Leídos + buscador (nombre, email, mensaje).
3. Paginación visible (anterior/siguiente, nº de página).

**Criterios de aceptación Fase 2**
- Los filtros y la búsqueda combinan con la paginación server-side.
- Buscar "maría" devuelve mensajes cuyo nombre, email o mensaje coincidan.

### Fase 3 — Email: estado y reenvío

1. `ResendMessage` handler: reutiliza `MailerInterface::sendContactNotification`
   y actualiza `email_sent`/`email_sent_at` + logging (ya existente en
   `SubmitContactHandler` como referencia).
2. UI: badge "No enviado" en los mensajes con `email_sent = false` y botón
   "Reenviar" en el detalle.
3. Acciones rápidas: copiar email, enlace `mailto:`.

**Criterios de aceptación Fase 3**
- Un mensaje con `email_sent = false` muestra su estado y permite reenviar.
- Tras reenviar OK, el estado pasa a "Enviado" con su fecha.
- Si el reenvío falla, se loguea y la UI muestra el error (sin dejar de
  ser no-fatal para la BD).

### Fase 4 (opcional, futuro)

- Responder al cliente desde el panel (nuevo método en `MailerInterface`).
- Exportación CSV de los mensajes filtrados.
- Filtro por tipo de evento.

## 9. Riesgos y decisiones abiertas

| Tema | Decisión propuesta |
|---|---|
| Borrado | Físico con confirmación (no hay requisito legal de retención) |
| Reenvío de email | Reutiliza `BrevoMailer` tal cual; si falla, 500 con mensaje y log (no bloquea) |
| Paginación | Server-side desde Fase 1 (los mensajes pueden crecer) |
| Detalle: página vs modal | Página aparte (consistente con el patrón actual) |
| IP del remitente | Se muestra en detalle solo como dato informativo |
| `unreadCount` | En la respuesta del listado; si más adelante se necesita en más sitios, endpoint `/api/admin/messages/unread-count` |

## 10. Definición de hecho

- Endpoints y páginas según este PRD, con tests unit/integración/frontend en verde.
- `PROJECT_SPEC.md` actualizado (tabla de endpoints + estructura de directorios).
- Migration ejecutada en local y documentada en el deploy de producción
  (`make migrate` tras `make prod-up`).
- Probado a mano en móvil y escritorio (las tablas/listas del panel deben
  cumplir la pauta de scroll horizontal que se aplicó al dashboard).
