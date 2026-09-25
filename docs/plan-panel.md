# Plan: el panel como índice de las zonas de edición

> Plan de implementación — Fecha: 2026-09-25 · Proyecto: dulziasalamanca
> Estado: **ejecutado**

## Contexto

El panel (`/dulzia-panel`) era a la vez la portada del admin **y** la tabla de secciones, con
los accesos a lo demás como botones sueltos en la cabecera ("Mensajes", "Ajustes de
contacto", "Categorías"). Con cuatro zonas de edición eso se queda corto: no hay un sitio
que diga qué se puede editar, y cada página vuelve a un lugar distinto ("← Secciones" en
categorías, "← Mensajes" en ajustes).

**Decisiones tomadas:**

1. **El panel es el índice**: cuatro tarjetas (Secciones, Categorías, Avisos por email y
   Datos de contacto), cada una con una frase de qué se edita ahí y su enlace.
2. **La tabla de secciones se muda** a `/dulzia-panel/servicios` (`AdminServicesPage`, con el
   contenido que antes era el panel). Así "Secciones" es una zona más y no el marco de todas
   las demás.
3. **Ajustes se parte en dos páginas**: los avisos del formulario (destinatario, interno) y
   los datos publicados (email y teléfono). Eran dos bloques de una misma página; con el panel
   como índice, cada tarjeta necesita la suya. Esto revierte el "dos bloques en una página"
   de `plan-contacto.md`.
4. **Cada página vuelve al panel** con "← Panel". Las que tienen jerarquía propia la
   conservan: las fotos de una sección vuelven a "Secciones" y el detalle de un mensaje, a
   "Mensajes".
5. **Mensajes es el buzón**, no una zona de edición, pero se lleva su propia tarjeta: va
   arriba del todo, a lo ancho y con el contador de sin leer (es lo que pide atención antes
   que nada).

## Estado de ejecución

| Etapa | Descripción | Estado |
|---|---|---|
| 1 | El panel pasa a ser el índice con cuatro tarjetas | ✅ Hecha |
| 2 | `AdminServicesPage` con la tabla de secciones | ✅ Hecha |
| 3 | Ajustes partido en dos páginas (avisos y datos publicados) | ✅ Hecha |
| 4 | "← Panel" en todas las zonas, con las jerarquías internas conservadas | ✅ Hecha |
| 5 | Tests (Vitest) y `PROJECT_SPEC.md` | ✅ Hechos (106 en verde) |
| 6 | Arreglo: la tabla de categorías no tenía scroll horizontal | ✅ Hecho |
| 7 | Tarjeta del buzón de mensajes en el panel, con el contador | ✅ Hecha |
| 8 | Filtro de leídos / sin leer en la lista de mensajes | ✅ Hecho |

## El filtro de mensajes (etapa 8)

Tres pestañas con sus contadores (Todos · Sin leer · Leídos). **Filtra el servidor**, no el
cliente: la lista está paginada y filtrar solo la página visible daría totales falsos.

- `GET /api/admin/messages?page=1&filter=all|unread|read` (validado con `Assert\Choice`,
  422 si el filtro no existe).
- La respuesta cambia `unreadCount` por **`counts: {all, unread, read}`** (los tres
  contadores globales de las pestañas; los leídos salen de restar, así que siguen siendo dos
  consultas) y añade `filter`. `total` pasa a ser el del filtro activo, que es lo que pagina
  la tabla.
- El puerto del repositorio recibe el filtro ya traducido (`findPage($offset, $limit,
  ?bool $isRead)`): los nombres de la API no llegan al dominio.
- Con un filtro puesto, marcar un mensaje como leído **relee la página** para que la fila
  desaparezca de la lista (en "Todos" se actualiza en el sitio, sin ir al servidor).

## El arreglo de la tabla de categorías

`.table-wrap` tenía `overflow:hidden`: en pantallas estrechas recortaba la tabla en vez de
dejar desplazarla. Ahora es `overflow-x:auto` y la tabla tiene `min-width:560px`, igual que
la de secciones.

## Verificación

1. `npm --prefix frontend run test` en verde (**106**).
2. `npm run build`.
3. Manual (requiere sesión de admin): entrar al panel, ver las cuatro tarjetas, recorrer las
   cuatro zonas y volver desde cada una con "← Panel"; y la tabla de categorías en el móvil.
