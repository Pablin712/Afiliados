# Requisitos de Esteban

1. Verificador de pagos para sistema Afiliados. (cuando un usuario realiza un pago, el sistema aprueba o rechaza por él)
- Apis aprobar y rechazar pago
- Mandar datos necesarios a webhook: POST https://autobot.aaronsoft.es/webhook/afiliados-payment-verifier 

Tip:
ya hice el verificador de pagos para mi negocio
este es el json que llegaría al webhook:
[
  {
    "headers": {
      "host": "autobot.aaronsoft.es",
      "user-agent": "GuzzleHttp/7",
      "content-length": "833",
      "accept": "application/json",
      "content-type": "application/json",
      "x-forwarded-for": "82.29.157.199",
      "x-forwarded-host": "autobot.aaronsoft.es",
      "x-forwarded-port": "443",
      "x-forwarded-proto": "https",
      "x-forwarded-server": "vps-82a11f08",
      "x-real-ip": "82.29.157.199",
      "accept-encoding": "gzip"
    },
    "params": {},
    "query": {},
    "body": {
      "event": "recarga.created",
      "idrec": 148,
      "idcli": 272,
      "idban": 1,
      "banco_nombre": "Banco Pichincha",
      "banco": {
        "idban": 1,
        "nombreban": "Banco Pichincha",
        "propietarioban": "Pablo Darío Jiménez Elizalde",
        "cedulaban": "1004549976",
        "numeroban": "2209859440",
        "tipoban": "Transaccional",
        "detalleban": "Cuenta transaccional, deposita o transfiere en cualquier punto de Banco Pichincha",
        "foto": "storage/fotos/pichincha.jpg",
        "monto": 375.44,
        "created_at": "2025-01-30T23:39:28-05:00",
        "updated_at": "2026-03-24T22:37:45-05:00"
      },
      "numcomprobante": "45990812",
      "valor": 4,
      "recarga_url": "https://streamify.aaronsoft.es/public/api/v2/payments/n8n/recargas/148",
      "foto_url": "https://streamify.aaronsoft.es/public/api/v2/payments/n8n/recargas/148/comprobante",
      "created_at": "2026-03-24T22:45:29-05:00",
      "trace_id": "recarga-148-20260324-224529"
    },
    "webhookUrl": "https://autobot.aaronsoft.es/webhook/94d871f4-6485-4118-93ec-471171de71c7",
    "executionMode": "production"
  }
]

y el flujo sería:
## Flujo en donde se usa el webhook y apis aprobar y rechazar
Nota: las apis son ejemplo de como usa otro proyecto.
{
  "nodes": [
    {
      "parameters": {
        "httpMethod": "POST",
        "path": "afiliados-payment-verifier",
        "responseMode": "lastNode",
        "options": {}
      },
      "type": "n8n-nodes-base.webhook",
      "typeVersion": 2.1,
      "position": [
        560,
        1168
      ],
      "id": "e8713c41-9eca-48e7-a3ee-428921a85a97",
      "name": "afiliados",
      "webhookId": "9d15ba5f-d55d-45fe-8ee0-36e36e37f8a2"
    },
    {
      "parameters": {
        "resource": "image",
        "operation": "analyze",
        "modelId": {
          "__rl": true,
          "value": "gpt-4o-mini",
          "mode": "list",
          "cachedResultName": "GPT-4O-MINI"
        },
        "text": "Analiza este comprobante de pago.\n\n----------------------------------------\n\n1. Extrae del comprobante (si existe):\n- numero de comprobante\n- monto\n- banco\n- fecha\n- nombre del beneficiario / titular (a quien se envió el dinero)\n\n2. Evalúa la calidad de la imagen:\n- es legible?\n- está borrosa?\n- parece editada o manipulada?\n- parece un comprobante real o fake?\n\n----------------------------------------\n\nReglas IMPORTANTES:\n- No inventes datos\n- Si un dato no es visible, devuelve null\n- No hagas validaciones contra datos externos\n- Solo analiza lo que ves en la imagen\n\n----------------------------------------\n\nDevuelve SOLO JSON válido:\n\n{\n  \"comprobante\": \"string|null\",\n  \"monto\": number|null,\n  \"banco\": \"string|null\",\n  \"fecha\": \"YYYY-MM-DD|null\",\n  \"titular\": \"string|null\",\n\n  \"legible\": true/false,\n  \"borroso\": true/false,\n  \"editado\": true/false,\n  \"realista\": true/false,\n\n  \"confianza\": 0-100\n}",
        "imageUrls": "=https://streamify.aaronsoft.es/public/api/v2/payments/n8n/recargas/{{ $json.body.idrec }}/comprobante",
        "options": {}
      },
      "type": "@n8n/n8n-nodes-langchain.openAi",
      "typeVersion": 2.1,
      "position": [
        720,
        1168
      ],
      "id": "b926e3e7-d719-4623-9be0-fe4e2322a786",
      "name": "Analyze image",
      "credentials": {
        "openAiApi": {
          "id": "dBPlamBZ6NHPVTxU",
          "name": "OpenAi Pablin"
        }
      }
    },
    {
      "parameters": {
        "jsCode": "// ==========================\n// 🔹 1. OBTENER RESPUESTA OPENAI\n// ==========================\nlet raw = $input.first().json['0'].content[0].text;\n\nraw = raw\n  .replace(/```json/g, '')\n  .replace(/```/g, '')\n  .trim();\n\n// ==========================\n// 🔹 2. PARSEAR JSON\n// ==========================\nlet parsed;\n\ntry {\n  parsed = JSON.parse(raw);\n} catch (e) {\n  parsed = {};\n}\n\n// ==========================\n// 🔹 3. FUNCIONES UTILES\n// ==========================\nconst normalize = (str) =>\n  str?.toLowerCase()\n     .normalize(\"NFD\")\n     .replace(/[\\u0300-\\u036f]/g, \"\") // quitar tildes\n     .trim();\n\n// ==========================\n// 🔹 4. NORMALIZAR DATOS OCR\n// ==========================\nconst comprobanteOCR = parsed.comprobante?.replace(/\\s/g, '') || null;\nconst montoOCR = parsed.monto ?? null;\nconst bancoOCR = normalize(parsed.banco);\nconst fechaOCR = parsed.fecha || null;\nconst titularOCR = normalize(parsed.titular);\n\n// ==========================\n// 🔹 5. DATOS DEL WEBHOOK\n// ==========================\nconst webhook = $('Verificador').first().json.body;\n\nconst comprobanteWebhook = webhook.numcomprobante?.replace(/\\s/g, '');\nconst montoWebhook = webhook.valor;\nconst bancoWebhook = normalize(webhook.banco?.nombreban);\nconst titularWebhook = normalize(webhook.banco?.propietarioban);\nconst fechaWebhook = webhook.created_at;\n\n// ==========================\n// 🔹 6. VALIDACIONES\n// ==========================\nlet aprobado = true;\nlet errores = [];\n\n// ✔ comprobante\nif (!comprobanteOCR || comprobanteOCR !== comprobanteWebhook) {\n  aprobado = false;\n  errores.push(\"Comprobante no coincide\");\n}\n\n// ✔ comprobante sospechoso (ej: 111111)\nif (comprobanteOCR && /^(\\d)\\1+$/.test(comprobanteOCR)) {\n  aprobado = false;\n  errores.push(\"Comprobante sospechoso\");\n}\n\n// ✔ monto\nif (montoOCR === null || Math.abs(montoOCR - montoWebhook) > 0.01) {\n  aprobado = false;\n  errores.push(\"Monto no coincide\");\n}\n\n// ✔ banco (dinámico 🔥)\nif (!bancoOCR || !bancoOCR.includes(bancoWebhook)) {\n  aprobado = false;\n  errores.push(\"Banco no coincide\");\n}\n\n// ==========================\n// ✔ titular (ULTRA ROBUSTO 🔥)\n// ==========================\nconst splitWords = (str) => normalize(str).split(/\\s+/).filter(Boolean);\n\nconst ocrWords = splitWords(titularOCR);\nconst webhookWords = splitWords(titularWebhook);\n\n// evitar duplicados\nconst unique = arr => [...new Set(arr)];\n\nconst ocrSet = unique(ocrWords);\nconst webhookSet = unique(webhookWords);\n\n// contar coincidencias reales\nlet matches = 0;\n\nfor (let w of webhookSet) {\n  for (let o of ocrSet) {\n\n    if (o === w) {\n      matches++;\n      break;\n    }\n\n    // match parcial fuerte (mínimo 4 letras)\n    if (o.length >= 4 && w.length >= 4) {\n      if (o.includes(w.slice(0,4)) || w.includes(o.slice(0,4))) {\n        matches++;\n        break;\n      }\n    }\n  }\n}\n\n// porcentaje de match\nconst ratio = matches / webhookSet.length;\n\n// regla inteligente\nif (ratio < 0.6) {\n  aprobado = false;\n  errores.push(`Titular no coincide (${Math.round(ratio * 100)}%)`);\n}\n\n// ==========================\n// ✔ fecha (INTELIGENTE 🔥)\n// ==========================\nif (fechaOCR) {\n\n  const toDateOnly = (f) => {\n    const d = new Date(f);\n    return new Date(d.getFullYear(), d.getMonth(), d.getDate());\n  };\n\n  const fOCR = toDateOnly(fechaOCR);\n  const fWEB = toDateOnly(fechaWebhook);\n\n  const diffDias = Math.abs(\n    (fWEB - fOCR) / (1000 * 60 * 60 * 24)\n  );\n\n  // detectar fin de semana\n  const isWeekend = (date) => {\n    const day = date.getDay();\n    return day === 0 || day === 6;\n  };\n\n  let maxDias = 1;\n\n  // si cae en finde → ampliar ventana\n  if (isWeekend(fOCR) || isWeekend(fWEB)) {\n    maxDias = 3;\n  }\n\n  // tolerancia adicional por banca (opcional)\n  maxDias = Math.max(maxDias, 2);\n\n  if (diffDias > maxDias) {\n    aprobado = false;\n    errores.push(`Fecha fuera de rango (${diffDias} días)`);\n  }\n\n} else {\n  aprobado = false;\n  errores.push(\"Fecha no detectada\");\n}\n\n// ==========================\n// 🚨 7. ANTIFRAUDE (IA)\n// ==========================\n\n// ilegible\nif (parsed.legible === false) {\n  aprobado = false;\n  errores.push(\"Imagen no legible\");\n}\n\n// borroso\nif (parsed.borroso === true) {\n  aprobado = false;\n  errores.push(\"Imagen borrosa\");\n}\n\n// editado\nif (parsed.editado === true) {\n  aprobado = false;\n  errores.push(\"Posible edición\");\n}\n\n// no real\nif (parsed.realista === false) {\n  aprobado = false;\n  errores.push(\"No parece real\");\n}\n\n// baja confianza\nif (parsed.confianza !== undefined && parsed.confianza < 70) {\n  aprobado = false;\n  errores.push(\"Baja confianza IA\");\n}\n\n// ==========================\n// 🧠 8. SCORE ANTIFRAUDE (PRO 🔥)\n// ==========================\nlet score = 100;\n\nif (parsed.borroso) score -= 20;\nif (parsed.editado) score -= 40;\nif (!parsed.realista) score -= 40;\nif (!parsed.legible) score -= 30;\n\nif (score < 60) {\n  aprobado = false;\n  errores.push(\"Score antifraude bajo\");\n}\n\n// ==========================\n// 🧾 9. RESULTADO FINAL\n// ==========================\nreturn [\n  {\n    json: {\n      aprobado,\n      errores: errores.join(\", \"),\n      score,\n\n      data_extraida: {\n        comprobante: comprobanteOCR,\n        monto: montoOCR,\n        banco: bancoOCR,\n        fecha: fechaOCR,\n        titular: titularOCR,\n        titularWebhook: titularWebhook\n      },\n\n      idrec: webhook.idrec,\n      raw\n    }\n  }\n];"
      },
      "type": "n8n-nodes-base.code",
      "typeVersion": 2,
      "position": [
        848,
        1168
      ],
      "id": "26ffd176-58cb-4e05-b0bb-30c7729939e3",
      "name": "Parsear"
    },
    {
      "parameters": {
        "rules": {
          "values": [
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "leftValue": "={{ $json.aprobado }}",
                    "rightValue": "",
                    "operator": {
                      "type": "boolean",
                      "operation": "true",
                      "singleValue": true
                    },
                    "id": "62746053-f510-4da7-8be8-8be67d44368d"
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "aprobado"
            },
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "id": "c769c3a0-8777-498e-b05b-b29a2de83645",
                    "leftValue": "={{ $json.aprobado }}",
                    "rightValue": false,
                    "operator": {
                      "type": "boolean",
                      "operation": "false",
                      "singleValue": true
                    }
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "rechazado"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.switch",
      "typeVersion": 3.4,
      "position": [
        976,
        1168
      ],
      "id": "80c0ce74-10ce-4b96-a4c0-d8ee7da73a82",
      "name": "Switch2"
    },
    {
      "parameters": {
        "method": "POST",
        "url": "=https://streamify.aaronsoft.es/public/api/v2/payments/n8n/recargas/{{ $('Verificador').item.json.body.idrec }}/aprobar",
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.4,
      "position": [
        1120,
        1088
      ],
      "id": "340d2549-3225-494a-9406-cc3312a001fa",
      "name": "Aprobar recarga"
    },
    {
      "parameters": {
        "method": "POST",
        "url": "=https://streamify.aaronsoft.es/public/api/v2/payments/n8n/recargas/{{ $('Verificador').item.json.body.idrec }}/rechazar",
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.4,
      "position": [
        1120,
        1232
      ],
      "id": "044edd34-409b-4d03-8d11-4525475924b8",
      "name": "Rechazar recarga"
    }
  ],
  "connections": {
    "afiliados": {
      "main": [
        [
          {
            "node": "Analyze image",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Analyze image": {
      "main": [
        [
          {
            "node": "Parsear",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Parsear": {
      "main": [
        [
          {
            "node": "Switch2",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Switch2": {
      "main": [
        [
          {
            "node": "Aprobar recarga",
            "type": "main",
            "index": 0
          }
        ],
        [
          {
            "node": "Rechazar recarga",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  },
  "pinData": {},
  "meta": {
    "templateCredsSetupCompleted": true,
    "instanceId": "2a4787fedcd3a9fda6d63f2231359e551e48f7e0d6a6b433946467fe82f7e7a4"
  }
}
