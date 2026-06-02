# Workflow n8n para verificador de pagos (adaptado a Afiliados)

Este documento reemplaza el flujo viejo de `recargas` y lo adapta al proyecto actual.

## 1) Endpoints internos disponibles en este proyecto

Todos estos endpoints usan token interno:

- Header recomendado: `X-Internal-Token: <INTERNAL_API_TOKEN>`
- Alternativa: `Authorization: Bearer <INTERNAL_API_TOKEN>`

Base URL esperada en n8n:

- `API_BASE=http://127.0.0.1:8000/api`

Endpoints nuevos para verificador:

- `GET {{$env.API_BASE}}/admin/payments/pending?limit=50`
- `GET {{$env.API_BASE}}/admin/payments/pending/{payment_id}`
- `GET {{$env.API_BASE}}/admin/payments/pending/{payment_id}/receipt`
- `POST {{$env.API_BASE}}/admin/payments/pending/{payment_id}/approve`
- `POST {{$env.API_BASE}}/admin/payments/pending/{payment_id}/reject`

## 2) Formato payload recomendado para el Webhook n8n

Si tu backend hace push a n8n (modo webhook), manda este JSON:

```json
{
  "event": "payment.pending",
  "payment_id": 148,
  "user_id": 272,
  "bank_id": 1,
  "payment_number": "45990812",
  "amount": 4.00,
  "created_at": "2026-03-24T22:45:29-05:00",
  "trace_id": "payment-148-20260324-224529",
  "bank": {
    "id": 1,
    "name": "Banco Pichincha",
    "owner": "Pablo Dario Jimenez Elizalde",
    "identification": "1004549976",
    "number": "2209859440",
    "detail": "Cuenta transaccional"
  },
  "payment_url": "http://127.0.0.1:8000/api/admin/payments/pending/148",
  "receipt_url": "http://127.0.0.1:8000/api/admin/payments/pending/148/receipt",
  "approve_url": "http://127.0.0.1:8000/api/admin/payments/pending/148/approve",
  "reject_url": "http://127.0.0.1:8000/api/admin/payments/pending/148/reject"
}
```

## 3) Node Webhook (n8n)

Configuracion sugerida:

- `HTTP Method`: `POST`
- `Path`: `afiliados-payment-verifier`
- `Response`: `Last Node`
- `Authentication`: opcional (recomendado usar `Header Auth` para que solo tu app lo dispare)

Entrada esperada del webhook:

- `body.payment_id`
- `body.payment_number`
- `body.amount`
- `body.bank.owner`
- `body.bank.name`
- `body.receipt_url`
- `body.approve_url`
- `body.reject_url`
- `body.trace_id`

## 4) Node Analyze image (OpenAI)

Tipo de nodo: `@n8n/n8n-nodes-langchain.openAi`

- `resource`: `image`
- `operation`: `analyze`
- `model`: `gpt-4o-mini`
- `imageUrls`: `={{ $json.body.receipt_url }}`

Prompt recomendado:

```text
Analiza este comprobante de pago y devuelve SOLO JSON valido.

Extrae, solo si es visible:
- payment_number (numero de comprobante)
- amount
- bank_name
- transfer_date (YYYY-MM-DD)
- account_owner (titular / beneficiario)

Evalua calidad y fraude:
- legible (true/false)
- blurry (true/false)
- edited (true/false)
- realistic (true/false)
- confidence (0-100)

Reglas:
- No inventar datos
- Si no se ve, usar null
- No validar contra datos externos
- Solo analiza la imagen

Formato de salida JSON:
{
  "payment_number": "string|null",
  "amount": 0,
  "bank_name": "string|null",
  "transfer_date": "YYYY-MM-DD|null",
  "account_owner": "string|null",
  "legible": true,
  "blurry": false,
  "edited": false,
  "realistic": true,
  "confidence": 90
}
```

## 5) Node Code (Parse + Decision)

Este script toma la salida del OCR y compara contra el payload del webhook.

```javascript
let raw = $input.first().json?.['0']?.content?.[0]?.text ?? $input.first().json?.text ?? '';

raw = String(raw)
  .replace(/```json/gi, '')
  .replace(/```/g, '')
  .trim();

let parsed = {};
try {
  parsed = JSON.parse(raw);
Webhook final activo:

- `https://autobot.aaronsoft.es/webhook/afiliados-payment-verifier`
} catch (e) {
  parsed = {};
}

const normalize = (str) =>
  String(str ?? '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, ' ')
    .trim();

const webhook = $items('Webhook1', 0, 0)[0].json.body;

const extractedNumber = String(parsed.payment_number ?? '').replace(/\s/g, '');
const extractedAmount = parsed.amount ?? null;
const extractedBank = normalize(parsed.bank_name);
const extractedOwner = normalize(parsed.account_owner);
const extractedDate = parsed.transfer_date ?? null;

const expectedNumber = String(webhook.payment_number ?? '').replace(/\s/g, '');
const expectedAmount = Number(webhook.amount ?? 0);
const expectedBank = normalize(webhook.bank?.name);
const expectedOwner = normalize(webhook.bank?.owner);
const expectedDateRaw = webhook.created_at;

const toDateOnly = (v) => {
  const d = new Date(v);
  if (Number.isNaN(d.getTime())) return null;
  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
};

let approved = true;
const errors = [];

if (!extractedNumber || extractedNumber !== expectedNumber) {
  approved = false;
  errors.push('payment_number_mismatch');
}

if (extractedNumber && /^(\d)\1+$/.test(extractedNumber)) {
  approved = false;
  errors.push('suspicious_payment_number');
}

if (extractedAmount === null || Math.abs(Number(extractedAmount) - expectedAmount) > 0.01) {
  approved = false;
  errors.push('amount_mismatch');
}

if (!extractedBank || !extractedBank.includes(expectedBank)) {
  approved = false;
  errors.push('bank_mismatch');
}

const splitWords = (s) => normalize(s).split(/\s+/).filter(Boolean);
const ocrWords = splitWords(extractedOwner);
const expectedWords = splitWords(expectedOwner);
let ownerMatches = 0;

for (const w of expectedWords) {
  for (const o of ocrWords) {
    if (o === w || o.startsWith(w.slice(0, 4)) || w.startsWith(o.slice(0, 4))) {
      ownerMatches++;
      break;
    }
  }
}

if (ownerMatches < 2) {
  approved = false;
  errors.push('owner_mismatch');
}

if (extractedDate) {
  const dOcr = toDateOnly(extractedDate);
  const dExpected = toDateOnly(expectedDateRaw);
  if (!dOcr || !dExpected) {
    approved = false;
    errors.push('date_parse_error');
  } else {
    const diffDays = Math.abs((dExpected - dOcr) / (1000 * 60 * 60 * 24));
    if (diffDays > 1) {
      approved = false;
      errors.push('date_out_of_range');
    }
  }
} else {
  approved = false;
  errors.push('date_not_detected');
}

if (parsed.legible === false) { approved = false; errors.push('image_not_legible'); }
if (parsed.blurry === true) { approved = false; errors.push('image_blurry'); }
if (parsed.edited === true) { approved = false; errors.push('image_possibly_edited'); }
if (parsed.realistic === false) { approved = false; errors.push('image_not_realistic'); }

const confidence = Number(parsed.confidence ?? 0);
if (confidence > 0 && confidence < 70) {
  approved = false;
  errors.push('low_ai_confidence');
}

let antiFraudScore = 100;
if (parsed.blurry) antiFraudScore -= 20;
if (parsed.edited) antiFraudScore -= 40;
if (parsed.realistic === false) antiFraudScore -= 40;
if (parsed.legible === false) antiFraudScore -= 30;

if (antiFraudScore < 60) {
  approved = false;
  errors.push('anti_fraud_score_low');
}

return [{
  json: {
    approved,
    errors,
    antiFraudScore,
    trace_id: webhook.trace_id ?? null,
    payment_id: webhook.payment_id,
    approve_url: webhook.approve_url,
    reject_url: webhook.reject_url,
    ai_result: parsed,
    ai_raw: raw,
  }
}];
```

## 6) Node HTTP Approve

Configuracion:

- `Method`: `POST`
- `URL`: `={{ $json.approve_url }}`
- `Headers`:
  - `X-Internal-Token: {{$env.INTERNAL_API_TOKEN}}`
  - `Content-Type: application/json`
- `Send Body`: `JSON`

Body JSON:

```json
{
  "trace_id": "={{ $json.trace_id }}",
  "ai_score": "={{ $json.antiFraudScore }}",
  "ai_errors": "={{ ($json.errors || []).join(',') }}"
}
```

## 7) Node HTTP Reject

Configuracion:

- `Method`: `POST`
- `URL`: `={{ $json.reject_url }}`
- `Headers`:
  - `X-Internal-Token: {{$env.INTERNAL_API_TOKEN}}`
  - `Content-Type: application/json`
- `Send Body`: `JSON`

Body JSON:

```json
{
  "reason": "={{ ($json.errors || []).join(',') }}"
}
```

## 8) Switch Node

Regla:

- Rama aprobada: `={{ $json.approved === true }}`
- Rama rechazada: `={{ $json.approved === false }}`

## 9) Modo alterno por Cron (sin push webhook)

Si prefieres no enviar webhooks desde Laravel:

1. Cron n8n cada 5 minutos.
2. `GET /admin/payments/pending`.
3. Loop por cada item (`Split in Batches`).
4. `Analyze image` usando `receipt_url`.
5. `Code` + `Switch`.
6. `POST approve_url` o `POST reject_url`.

## 10) Recomendaciones de seguridad

1. Mantener `INTERNAL_API_TOKEN` largo y rotarlo.
2. Agregar allowlist IP entre n8n y app si es posible.
3. Registrar `trace_id`, score y errores en cada aprobacion/rechazo.
4. Mantener idempotencia: si el pago ya no esta en `pending`, el API responde `422` y n8n debe terminar sin reintentar infinito.
