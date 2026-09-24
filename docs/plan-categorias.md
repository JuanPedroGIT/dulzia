# Plan: categorías gestionables desde el panel

> Plan de implementación — Fecha: 2026-09-24 · Proyecto: dulziasalamanca
> Estado: **ejecutado** (queda la prueba manual desde el panel)

## Contexto

Hoy las categorías **no existen como dato**: son cuatro listas escritas a mano en el frontend y una
columna de texto libre en `service`:

| Fichero | Qué es |
|---|---|
| `pages/admin/AdminDashboardPage.vue:14` | El `<select>` del modal de servicios |
| `pages/ServiciosPage.vue:74` | Las pestañas del catálogo (con emoji) |
| `components/features/ServiceCard.vue:27` | Etiqueta de la tarjeta |
| `pages/ServicioDetallePage.vue:202` | Etiqueta del hero de la ficha |

`service.category` es un `VARCHAR(50)` **sin validación**: `ServiceCommandFactory` solo pone `'food'`
por defecto. Añadir una categoría obliga a tocar 4 ficheros, y si se olvida alguno la web lo pinta
mal en silencio (etiqueta vacía en tarjetas y ficha, identificador crudo en el panel).

**Decisiones tomadas:**

1. **Tabla `category`** propia, con `id` (identificador estable, PK), `name`, `emoji` y `sort_order`.
2. **`service.category` sigue siendo el identificador** en la API (`category: 'food'`): el contrato no
   cambia. La integridad la garantiza el backend, no una clave foránea (ver "Decisiones que
   cambiaron durante la ejecución").
3. **Sección nueva en el panel** (`/dulzia-panel/categorias`) con el CRUD completo; el desplegable del
   modal de servicios lee de ahí.
4. **Borrar una categoría en uso se bloquea** (409) con el número de secciones que la usan. Nunca se
   puede romper la web desde el panel.
5. **El emoji se guarda en la categoría** para que las pestañas de `/servicios` sigan igual.
6. El identificador **no se edita** una vez creada (se genera del nombre, como el de los servicios):
   cambiarlo obligaría a reescribir las claves foráneas.

**Consecuencia asumida:** la web pública necesita la lista de categorías, así que añade una petición
`GET /api/categories` — pequeña, en paralelo a la del catálogo y cacheada por sesión con el mismo
patrón que `useServices`.

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | Esquema: tabla `category` + semilla con las 3 actuales | ✅ Hecha |
| 2 | Backend: entidad, puerto, 4 casos de uso (listar, crear, editar, borrar) y excepción 409 | ✅ Hecha |
| 3 | API: `/api/categories` (público) y `/api/admin/categories` (CRUD) | ✅ Hecha |
| 4 | Panel: página de categorías + enlace + el desplegable del modal lee de la tabla | ✅ Hecha |
| 5 | Web pública: pestañas y etiquetas desde la API (fuera las 4 listas a mano) | ✅ Hecha |
| 6 | Tests (PHPUnit + Vitest) | ✅ Hechos (170 + 69 + 67 en verde) |
| 7 | Snapshot de prerender + `PROJECT_SPEC.md` | ✅ Hechos |
| 8 | Prueba manual de gestión desde el panel | ⬜ Pendiente (requiere sesión de admin) |

## Detalle por etapa

### 1. Esquema

- `Entity/Category.php`: `id` (string 50, PK, slug), `name` (100), `emoji` (20, nullable),
  `sortOrder` (int). `toArray()` → `{id, name, emoji, sort_order}`.
- Migración `Version20260924220000.php`:
  1. `CREATE TABLE category (...)`;
  2. semilla con `food`/Gastronomía/🍴, `decoration`/Decoración/🎨, `experience`/Experiencias/✨;
  3. por si la BD tiene algún servicio con una categoría que no esté en la semilla, se dan de alta
     esas antes de la FK (`INSERT ... SELECT DISTINCT`), para que la migración no falle;
  4. `ALTER TABLE service ADD CONSTRAINT FK ... FOREIGN KEY (category) REFERENCES category (id)`.
- Sin `ON DELETE CASCADE`: borrar una categoría en uso lo impide la propia BD además del handler.

### 2. Backend

- `Domain/Category/`: `CategoryRepositoryInterface` (`findAll`, `findById`, `save`, `delete`) y
  `CategoryInUseException` (409, con el número de secciones en el mensaje).
- `ServiceRepositoryInterface::countByCategory(string $id): int` — la cuenta es de servicios, así que
  vive en su puerto.
- `Application/Category/`: `ListCategories`, `CreateCategory`, `UpdateCategory`, `DeleteCategory`
  (query/command + handler cada uno, mismo patrón que `Application/Service/`).
  - `CategoryIdGenerator`: slug del nombre con sufijo numérico si ya existe (espejo de
    `ServiceIdGenerator`).
  - `DeleteCategoryHandler`: si `countByCategory > 0` lanza `CategoryInUseException`.
- `Infrastructure/Repository/DoctrineCategoryRepository`.

### 3. API

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| GET | `/api/categories` | — | Lista para la web (pestañas y etiquetas) |
| GET | `/api/admin/categories` | token | Lista con el número de secciones de cada una |
| POST | `/api/admin/categories` | token | Crear (201) |
| PUT | `/api/admin/categories/{id}` | token | Editar nombre, emoji y orden |
| DELETE | `/api/admin/categories/{id}` | token | Borrar (409 si está en uso) |

- `CategoryController` (público) y `AdminCategoryController`, con el parseo del body en un
  `CategoryCommandFactory` (como el de servicios).

### 4. Panel

- `pages/admin/AdminCategoriesPage.vue` en `/dulzia-panel/categorias` (`meta.requiresAuth`), con
  enlace en la cabecera del panel junto a "Mensajes" y "Ajustes de email".
- Tabla: Nombre | Identificador | Secciones | Orden | Acciones. Modal de alta/edición con nombre,
  emoji y orden; el identificador se genera del nombre y se muestra en solo lectura.
- Borrado con confirmación; el 409 del backend se muestra tal cual ("La usan 3 secciones").
- `AdminDashboardPage.vue`: `CATEGORIES` deja de estar en el código y sale de la API; el
  `<select>` del modal y la etiqueta de la tabla usan esa lista.

### 5. Web pública

- `composables/useCategories.js`: mismo patrón que `useServices` (estado a nivel de módulo, una
  petición por sesión, dedupe, `loaded`).
- `ServiciosPage.vue`: las pestañas salen de la lista (`Todos` + las categorías ordenadas).
- `ServiceCard.vue` y `ServicioDetallePage.vue`: la etiqueta sale del nombre de la categoría; si una
  categoría no se encuentra, se muestra el identificador en vez de dejarlo en blanco (hoy las
  tarjetas lo pintan vacío).

### 6. Tests

- PHPUnit: entidad `Category`, los 4 handlers (incluido el bloqueo al borrar en uso y el 409 en
  integración), generador de identificadores, factory del body, y las rutas (público sin token,
  admin con token, 404 y 409).
- Vitest: `useCategories` (una petición por sesión), página de categorías (listar, crear, borrar),
  pestañas del catálogo desde la API, etiquetas de tarjeta y ficha, y el desplegable del modal de
  servicios.

### 7. Documentación

- Regenerar `frontend/prerender-data/snapshot.json` (las páginas prerenderizadas usan las etiquetas
  de categoría).
- `docs/PROJECT_SPEC.md`: entidad `category`, los 5 endpoints y la sección de categorías en el
  frontend (en lugar de las listas a mano).

## Verificación

1. `make test-unit`, `make test-integration`, `npm --prefix frontend run test` en verde.
2. `GET /api/categories` devuelve las 3 con su emoji y su orden.
3. `npm run build`: las 15 rutas prerenderizadas con las pestañas y etiquetas correctas.
4. Manual (requiere sesión de admin): crear una categoría, asignarla a una sección, verla en la web,
   intentar borrarla (bloqueo con el aviso) y borrarla tras quitarla de la sección.
5. `make migrate` en el despliegue.

## Verificación hecha

- `make test-unit` (**170**), `make test-integration` (**69**) y `npm --prefix frontend run test`
  (**67**) en verde.
- Migración `Version20260924220000` aplicada en la BD de desarrollo: tabla `category` con las 3
  categorías (`food`/Gastronomía/🍴, `decoration`/Decoración/🎨, `experience`/Experiencias/✨).
- `npm run build`: las 15 rutas prerenderizadas; en `/servicios` salen las 4 pestañas
  (Todos + las 3 con su emoji) y las tarjetas con el nombre de su categoría.
- Los mismos 6 servicios siguen destacados en la portada y sus enlaces a la ficha funcionan.

## Decisiones que cambiaron durante la ejecución

- **Sin clave foránea `service.category → category.id`.** El plan la incluía, pero el esquema de
  tests se genera desde los mapeos de Doctrine: una FK puesta a mano en SQL no está declarada ahí,
  y la siguiente `migration-diff` generaría una migración que la borra. Declararla de verdad exige
  una relación ManyToOne (tocar toda la cadena de servicios y sembrar categorías en cada test). Se
  cambió por **validación en el backend**: `CategoryResolver` rechaza con 400 una categoría
  inexistente al crear o editar un servicio.
- **El generador de identificadores quita los acentos** (`Animación` → `animacion`). La política de
  slug se extrajo a `Application\Shared\Slug`, compartida por `ServiceIdGenerator` y
  `CategoryIdGenerator`: antes cada uno la tenía duplicada y `Animación` daba `animaci-n`.

## Pendiente

- **Prueba manual desde el panel** (`/dulzia-panel/categorias`): crear, editar, el bloqueo al
  borrar una categoría en uso y el borrado tras quitarla de la sección. Requiere sesión de admin.
- **Producción**: ejecutar la migración (`php bin/console doctrine:migrations:migrate --no-interaction`)
  y regenerar el snapshot de prerender (el comando ya incluye las categorías, ver `docs/plan-seo.md`).
