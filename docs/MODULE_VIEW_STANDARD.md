# Estandar Base Para Nuevos Modulos (Vistas + CRUD)

## 1. Objetivo
Este documento define lo minimo obligatorio para construir cualquier modulo nuevo siguiendo el mismo patron aplicado en auditoria.

Aplica para modulos como: users, memberships, payments, profits, banks, etc.

## 2. Estructura minima por modulo
Usar este layout de archivos (ajustando nombres):

- `routes/web.php`
- `app/Http/Controllers/{Module}Controller.php`
- `app/Http/Requests/{Module}/Index{Module}Request.php` (si hay filtros)
- `app/Http/Requests/{Module}/Store{Module}Request.php` (si hay create)
- `app/Http/Requests/{Module}/Update{Module}Request.php` (si hay update)
- `resources/views/{module}/index.blade.php`
- `resources/views/{module}/partials/table-rows.blade.php`
- `resources/views/{module}/exports/pdf.blade.php` (si exporta pdf server-side)
- `resources/lang/en/{module}.php`
- `resources/lang/es/{module}.php`

## 3. Checklist de UI obligatorio

### 3.1 Traducciones
- No usar textos hardcoded en Blade ni controlador.
- Usar `__()` para labels, botones, mensajes y titulos.

### 3.2 Tabla principal
- Usar `<x-enhanced-table>` para listados.
- Si el dataset va a crecer, usar `:serverSide="true"`.
- Definir `headers` con `sort_by` real de base de datos cuando aplique.
- Pasar `searchUrl` a la ruta del index.

### 3.3 Acciones CRUD en modales (obligatorio)
- Para `create`, `edit` y `delete`, usar `<x-modal>` (no formularios inline dentro de la tabla).
- Los modales deben vivir en partials separados del index:
	- `resources/views/{module}/partials/modals/create.blade.php`
	- `resources/views/{module}/partials/modals/edit.blade.php`
	- `resources/views/{module}/partials/modals/delete.blade.php`
- No generar modales con `@foreach`.
- Debe existir un solo modal de `edit` y un solo modal de `delete`; se rellenan dinamicamente con el `id`/datos de la entidad seleccionada.
- Los botones de acciones deben abrir modales por `id` (eventos `open-modal` y formularios con `action` dinamico).

### 3.4 Selects de catalogos/filtros
- Usar `<x-searchable-select>` para selects medianos o grandes.

### 3.5 Botones de accion con iconos (obligatorio)
- No usar textos largos como accion principal en celdas de tabla (`Editar`, `Eliminar`).
- Usar botones compactos con iconos reutilizables (componente compartido, por ejemplo `<x-action-icon-button>`).
- Cada boton debe incluir `title` y `aria-label` para accesibilidad.

### 3.6 Exportaciones
- En tablas server-side, las exportaciones deben resolverse en backend (`?export=csv|excel|json|pdf`).
- No depender solo de export local JS si el modulo maneja muchos datos.

## 4. Navegacion (menu o dropdown)
- Agregar acceso al modulo en `resources/views/layouts/navigation.blade.php`.
- Version desktop y responsive.
- Mostrar links con `@can('view {module}')`.

Ejemplo:
- `@can('view users')`
- `@can('view payments')`

## 5. Seguridad y permisos (Spatie)
Cada modulo debe contemplar estos permisos:
- `view {module}`
- `create {module}`
- `edit {module}`
- `delete {module}`
- `manage {module}`
- `report {module}`

Reglas de uso:
- Ruta index/listado: `permission:view {module}`
- Exportaciones: validar `report {module}`
- Acciones create/update/delete: validar permiso correspondiente
- Botones en Blade: proteger con `@can` o `@canany`

## 6. Controlador: patron recomendado
Todo modulo de listado debe incluir al menos:

1. `index()`:
- Devuelve vista normal.
- Si `ajax=true`, devuelve JSON para `enhanced-table` (`html`, `total_records`, `current_page`, `per_page`).
- Si llega `export`, delega a metodos de export.

2. `buildQuery()`:
- Filtros por search y filtros especificos del modulo.
- Eager loading con `with()` para evitar N+1.
- Joins solo cuando sean necesarios para ordenar o filtrar.

3. `resolveSortBy()`:
- Whitelist de columnas permitidas para `sort_by`.
- Nunca usar `sort_by` directo sin validar.

4. `export()`:
- Valida `report {module}`.
- Soporta formatos necesarios (`csv`, `excel`, `json`, `pdf`).

## 7. Requests y validacion
- Crear FormRequest para `store`, `update` y filtros de index cuando ya hay varios parametros.
- Mantener reglas, mensajes y autorizacion centralizados en Requests.

## 8. Queries y rendimiento
- Evitar `orderBy` fijo que rompa el orden dinamico de `enhanced-table`.
- Limitar maximo de export server-side (ejemplo: 20k registros por archivo) o paginar export en lotes.
- Indexar columnas de busqueda/orden en migraciones cuando el volumen crezca.

## 9. Auditoria transversal
- No reimplementar logs por controlador.
- Confiar en middleware y servicio central de auditoria ya definidos.
- Solo agregar contexto extra en modulo si es estrictamente necesario.

## 10. Definicion de terminado (DoD)
Un modulo se considera listo cuando:
- Tiene permisos completos del modulo.
- Tiene enlace en navegacion con `@can`.
- Listado usa `x-enhanced-table` y funciona server-side (si aplica).
- Exportaciones respetan `report {module}`.
- Textos estan traducidos (`en` y `es`).
- Validaciones viven en FormRequest.
- No hay errores en `php artisan about`, `php artisan route:list` y `npm run build`.

## 11. Scaffold rapido (opcional)
Existe un comando para generar la base tecnica del modulo:

- `php artisan module:scaffold-view {module}`
- `php artisan module:scaffold-view {module} --model={Model}`
- `php artisan module:scaffold-view {module} --force`

Ejemplos:
- `php artisan module:scaffold-view users --model=User`
- `php artisan module:scaffold-view membership_types --model=MembershipType`

El comando crea:
- Controlador base del modulo
- FormRequests (`Index`, `Store`, `Update`)
- Vistas (`index`, `partials/table-rows`, `exports/pdf`)
- Traducciones (`resources/lang/en|es/{module}.php`)

Notas:
- No modifica automaticamente `routes/web.php` ni `navigation.blade.php`; esos pasos se aplican manualmente para mantener control del flujo.
