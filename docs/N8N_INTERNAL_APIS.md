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

### 8.2) Listar usuarios con membresia que vence manana (cron recordatorio)
- Método: `GET`
- URL: `/admin/memberships/expired-today`
- Query opcional:
  - `date=2026-04-15` (formato `Y-m-d`, por defecto manana)
  - `limit=500` (maximo 2000)
- Data por item incluye:
  - `user_id`, `name`, `email`, `phone`, `affiliate_code`
  - `membership` (`type`, `status`, `started_at`, `expires_at`, `expires_on`, `expired_date`)
  - `sponsor` (`id`, `name`, `email`)
  - `reminder` (`event`, `message_es`, `message_en`)

## Endpoints de ciclo de vida de usuarios (admin)
Prefijo: `/admin`

### 8.1) Eliminar usuarios inactivos (cron)
- Método: `POST`
- URL: `/admin/users/prune-inactive`
- Body JSON opcional:
```json
{
  "months": 3,
  "dry_run": false,
  "limit": 500
}
```
Notas:
- Inactividad se calcula por `sessions.last_activity` (sin actividad en 3 meses por defecto).
- Reasigna la red de afiliados del usuario eliminado para no romper `sponsor_id`.
- Usa `dry_run=true` para ver impacto sin eliminar.

## Endpoints de verificacion de pagos (admin)
Prefijo: `/admin`

Compatibilidad adicional para flujos legados:
- Prefijo alias: `/admin/v2/payments/n8n/recargas`
- Objetivo: reutilizar flujos n8n que usan nombres `idrec`, `foto_url`, `aprobar`, `rechazar`.

### 9) Listar pagos pendientes para verificador
- Método: `GET`
- URL: `/admin/payments/pending?limit=50`
- Meta:
  - `count`
  - `limit`
- Data por item incluye:
  - `payment_id`
  - `payment_number`
  - `amount`
  - `bank` (name/owner/identification/number/detail)
  - `receipt_url`
  - `approve_url`
  - `reject_url`

### 10) Consultar un pago pendiente
- Método: `GET`
- URL: `/admin/payments/pending/{payment}`
- Respuesta `422` si el pago ya no está pendiente.

### 11) Obtener imagen de comprobante
- Método: `GET`
- URL: `/admin/payments/pending/{payment}/receipt`
- Respuesta:
  - Binario de imagen si existe
  - `404` si no hay archivo

Alias legado:
- `GET /admin/v2/payments/n8n/recargas/{payment}/comprobante`

### 12) Aprobar pago pendiente por automatización
- Método: `POST`
- URL: `/admin/payments/pending/{payment}/approve`
- Body JSON opcional:
```json
{
  "reviewed_by": 1,
  "trace_id": "n8n-trace-001",
  "ai_score": 95,
  "ai_errors": "",
  "gateway_reference": "GW-ABC-001"
}
```
- Notas:
  - Reusa la misma lógica de aprobación manual del admin.
  - Si el pago ya fue procesado, responde `422`.

Alias legado:
- `POST /admin/v2/payments/n8n/recargas/{payment}/aprobar`

### 13) Rechazar pago pendiente por automatización
- Método: `POST`
- URL: `/admin/payments/pending/{payment}/reject`
- Body JSON opcional:
```json
{
  "reviewed_by": 1,
  "reason": "amount_mismatch,owner_mismatch"
}
```
- Si el pago ya fue procesado, responde `422`.

Alias legado:
- `POST /admin/v2/payments/n8n/recargas/{payment}/rechazar`

## Payload webhook para verificador
Cuando se crea un pago pendiente, el backend envia `POST` a `PAYMENT_VERIFIER_WEBHOOK_URL` con payload JSON que incluye:
- Campos actuales: `payment_id`, `payment_number`, `amount`, `bank`, `receipt_url`, `approve_url`, `reject_url`.
- Campos de compatibilidad: `idrec`, `idcli`, `idban`, `banco_nombre`, `numcomprobante`, `valor`, `recarga_url`, `foto_url`, `banco`.
- URL publica firmada para IA: `receipt_public_url` (tambien llega en `foto_url`).

Importante para nodo OpenAI Analyze image:
- Usa `{{$json.body.foto_url}}` (si el trigger es webhook) o `{{$json.receipt_url}}` (si viene de listado `/payments/pending`).
- `receipt_url` ya se entrega como URL publica firmada.
- `receipt_internal_url` queda solo para consumo interno con token.
- No uses rutas hardcodeadas tipo `/public/admin/.../comprobante` porque pueden devolver 404.

Esto permite editar un flujo n8n existente con cambios minimos.

## Pruebas rapidas (curl)
Suponiendo:
- `API_BASE=https://tu-dominio/api`
- `TOKEN=tu_INTERNAL_API_TOKEN`

1) Listar pendientes:
```bash
curl -X GET "$API_BASE/admin/payments/pending?limit=5" \
  -H "X-Internal-Token: $TOKEN" \
  -H "Accept: application/json"
```

2) Aprobar (ruta actual):
```bash
curl -X POST "$API_BASE/admin/payments/pending/ID/approve" \
  -H "X-Internal-Token: $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"trace_id":"n8n-test-001","ai_score":95}'
```

3) Rechazar (ruta alias legado):
```bash
curl -X POST "$API_BASE/admin/v2/payments/n8n/recargas/ID/rechazar" \
  -H "X-Internal-Token: $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reason":"monto_no_coincide"}'
```

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

### Flujo E (verificador automatico de pagos pendientes)
1. Cron cada 5 minutos (o Webhook push desde tu app)
2. HTTP Request `GET /admin/payments/pending`
3. Por cada item:
  - Analizar `receipt_url` con IA
  - Si pasa reglas: `POST /admin/payments/pending/{id}/approve`
  - Si falla reglas: `POST /admin/payments/pending/{id}/reject`
4. Guardar `trace_id`, score y errores para auditoría

### Flujo F (limpieza de usuarios inactivos)
1. Cron mensual (ejemplo: día 1, 03:00)
2. HTTP Request `POST /admin/users/prune-inactive` con `dry_run=true`
3. Revisar `data.total_candidates`
4. Si es correcto, ejecutar `POST /admin/users/prune-inactive` con `dry_run=false`

### Flujo G (recordatorio diario de membresias vencidas)
1. Cron diario (ejemplo: 08:05 UTC)
2. HTTP Request `GET /admin/memberships/expired-today`
3. Iterar `data[]` y enviar mensaje por WhatsApp/SMS usando `phone`, `name` y `reminder.message_es` (recordatorio de un dia antes)
4. Guardar auditoria (`user_id`, canal, estado de envio)

### Flujo H (expulsión de grupos – limpieza inicial o de emergencia)
**Nota:** La expulsión ocurre **automáticamente** en el Flujo D (recalculo semanal de membresías).
Este flujo solo se usa para limpiezas manuales o la carga inicial.

1. Trigger manual (o cron puntual)
2. HTTP Request `POST /admin/groups/whatsapp/remove-free` con body `{"dry_run": true}`
3. Revisar `meta.candidates` y `data.users` para validar la lista
4. Si es correcto, ejecutar `POST /admin/groups/whatsapp/remove-free` con `dry_run=false`
5. Guardar `meta.removed` para auditoría

**Respuesta:**
```json
{
  "message": "Free members removal from WhatsApp group processed.",
  "meta": {
    "dry_run": false,
    "candidates": 5,
    "removed": 5,
    "success": true
  },
  "data": {
    "users": [{ "id": 12, "name": "Juan", "phone": "+593961778319" }],
    "phones_normalized": ["593961778319"]
  }
}
```

**Comportamiento automático del Flujo D:**
Al ejecutar `POST /admin/memberships/recalculate-tiers`, la respuesta ahora incluye expulsión de los 4 grupos:
```json
{
  "meta": {
    "whatsapp_group_removed": 2,
    "telegram_groups_banned": 2
  },
  "data": {
    "whatsapp_group": {
      "phones_queued": ["593961778319"],
      "removed": 2,
      "success": true
    },
    "telegram_groups": {
      "users_queued": [123456789],
      "banned": 2,
      "results": {
        "123456789": {
          "aet_premium": true,
          "aet_vip_deriv": true,
          "aet_vip_weltrade": true
        }
      }
    }
  }
}
```

### Flujo I (registro de Telegram vía bot)
Este flujo tiene **solo 2 nodos** — el bot hace todo el trabajo incluyendo responder al usuario.

1. Webhook Trigger: `https://autobot.aaronsoft.es/webhook/aet-first-bot`
   - Configurado en BotFather como webhook del bot `@aetfirstbot`
2. HTTP POST: `POST /admin/telegram/register-chat-id`
   - Headers: `X-Internal-Token: {{$env.INTERNAL_API_TOKEN}}`
   - Body: `{"chat_id": "{{$json.message.from.id}}", "code": "{{$json.message.text}}"}`

**Respuestas posibles:**
- `201 registered: true` — registrado exitosamente (el API ya mensajeó al usuario)
- `200 registered: false` — ya estaba registrado (el API ya mensajeó al usuario)
- `400` — código inválido (no se mensajea al usuario — el mensaje puede usarse para otros flujos)

## APIs sugeridas para cron job
1. `POST /admin/financial-stats/register-today`
2. `POST /admin/financial-stats/register-yesterday`
3. `POST /admin/financial-stats/register-range` (solo recuperación histórica/manual)
4. `POST /admin/memberships/recalculate-tiers` ← ahora expulsa de los 4 grupos automáticamente
5. `GET /admin/payments/pending` (verificador cada 5 minutos)
6. `POST /admin/users/prune-inactive` (mensual)
7. `GET /admin/memberships/expired-today` (recordatorio diario para vencimientos de manana)
8. `POST /admin/groups/whatsapp/remove-free` (limpieza manual WhatsApp; la automática corre en el punto 4)
9. `POST /admin/groups/telegram/remove-free` (limpieza manual Telegram; la automática corre en el punto 4)

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
