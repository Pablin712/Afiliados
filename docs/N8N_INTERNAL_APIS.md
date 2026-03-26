# APIs internas para n8n

Este documento define el estandar para automatizaciones con n8n sobre el sistema de afiliados.

## Seguridad
- Base URL local: `http://127.0.0.1:8000/api`
- Middleware de seguridad: `internal_api_token`
- Header recomendado:
  - `X-Internal-Token: <INTERNAL_API_TOKEN>`
- Alternativa soportada:
  - `Authorization: Bearer <INTERNAL_API_TOKEN>`

Configurar token en `.env`:
- `INTERNAL_API_TOKEN=tu_token_largo_y_seguro`

## Convencion de respuesta
Todas las APIs nuevas deben devolver:
- `message`: texto corto
- `meta`: contexto de la ejecucion
- `data`: payload principal

## Endpoints de finanzas (admin)
Prefijo: `/admin`

### 1) Registrar estadisticas para una fecha
- Método: `POST`
- URL: `/admin/financial-stats/register`
- Body JSON opcional:
```json
{
  "date": "2026-03-25"
}
```
Si no se envía `date`, usa hoy.

### 2) Registrar estadisticas de hoy
- Método: `POST`
- URL: `/admin/financial-stats/register-today`
- Body: vacío

### 3) Registrar estadisticas de ayer
- Método: `POST`
- URL: `/admin/financial-stats/register-yesterday`
- Body: vacío

### 4) Registrar estadisticas por rango
- Método: `POST`
- URL: `/admin/financial-stats/register-range`
- Body JSON:
```json
{
  "from": "2026-03-01",
  "to": "2026-03-25"
}
```
Notas:
- Si `from > to`, el backend invierte automáticamente.
- Límite máximo: 366 días por request.

### 5) Consultar stats de una fecha
- Método: `GET`
- URL: `/admin/financial-stats/2026-03-25`
- Respuesta 404 si no existe fila para esa fecha.

### 6) Consultar dashboard financiero
- Método: `GET`
- URL: `/admin/financial-stats/dashboard?from=2026-03-01&to=2026-03-25`
- Si no hay snapshots diarios para el rango, el backend calcula en vivo desde:
  - `transactions` (ingresos/egresos)
  - `payments` aprobados
  - `profits` pendientes/pagadas

## Endpoints de membresias (admin)
Prefijo: `/admin`

### 7) Recalcular tiers de membresia
- Método: `POST`
- URL: `/admin/memberships/recalculate-tiers`
- Body JSON opcional:
```json
{
  "user_id": 3,
  "dry_run": false
}
```
Notas:
- Sin `user_id`, evalua todos los usuarios con membresia (`free` y `active`).
- Con `user_id`, recalcula solo ese usuario.
- `dry_run=true` calcula cambios sin persistirlos.
- Regla de conteo: afiliados directos con membresia `active` y tipo distinto de `free`.
- Regla de destino: usa `membership_types.affiliates_required` para elegir el mayor tier alcanzado.
- Regla de negocio: usuarios con membresia `free` no suben a `beginner` ni superiores; solo usuarios `active` pueden escalar tiers.

## Regla de comisiones para sponsors
- Un sponsor solo recibe comisiones si su membresia esta en `status=active` y su tipo es distinto de `free`.
- Si el sponsor esta en `free`, no se registra profit aunque tenga afiliados con pagos aprobados.

### 8) Inspeccionar evaluacion de un usuario
- Método: `GET`
- URL: `/admin/memberships/recalculate-tiers/3`
- Devuelve: tipo/status actual, cantidad de afiliados directos activos y tipo/status objetivo.

## Mapeo contable aplicado
- Todo pago aprobado de usuarios: ingreso del admin.
- Todo profit pagado a usuarios: egreso del admin.
- Todo profit pendiente: pendiente por pagar del admin.

## Flujos recomendados en n8n

### Flujo A (23:59)
1. Cron diario 23:59
2. HTTP Request `POST /admin/financial-stats/register-today`
3. Guardar resultado en logs

### Flujo B (04:00 del día siguiente)
1. Cron diario 04:00
2. HTTP Request `POST /admin/financial-stats/register-yesterday`
3. Guardar resultado en logs

### Flujo C (recuperación histórica)
1. Trigger manual
2. HTTP Request `POST /admin/financial-stats/register-range`
3. Verificar `meta.registered_rows`

### Flujo D (recalculo semanal de membresias)
1. Cron semanal (ejemplo: lunes 01:00)
2. HTTP Request `POST /admin/memberships/recalculate-tiers`
3. Guardar `meta.processed` y `meta.changed` para auditoria

## Plantilla de nodo HTTP (n8n)
- Method: `POST`
- URL: `{{$env.API_BASE}}/admin/financial-stats/register-today`
- Headers:
  - `X-Internal-Token: {{$env.INTERNAL_API_TOKEN}}`
  - `Content-Type: application/json`
- Response format: JSON

## Estandar para futuras APIs internas
Para mantener orden en n8n:
1. Ubicar nuevas APIs internas bajo `/api/admin/...` o `/api/internal/...`.
2. Reusar `internal_api_token`.
3. Responder siempre con `message`, `meta`, `data`.
4. Mantener endpoints idempotentes cuando sea posible (especialmente procesos diarios).
5. Documentar cada endpoint nuevo en este archivo.
