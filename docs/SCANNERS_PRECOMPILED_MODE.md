# Scanners en modo precompiled (sin MetaEditor en servidor)

Este modo permite descargar scanners EX5 sin compilar en el servidor.

## Objetivo

- Evitar dependencia de MetaEditor en hosting Linux.
- Mantener flujo actual del dashboard (modal, broker, descarga).

## Configuracion .env

```env
SCANNER_DISTRIBUTION_MODE=auto
SCANNER_PRECOMPILED_DISK=public
SCANNER_PRECOMPILED_DIRECTORY=scanners-bin
```

Notas:
- Si `SCANNER_DISTRIBUTION_MODE=auto`, usa EX5 precompilados si existen todos; si no, intenta compilar.
- Si `SCANNER_DISTRIBUTION_MODE=compile`, el sistema usa MetaEditor como antes.
- Si `SCANNER_DISTRIBUTION_MODE=precompiled`, NO intenta compilar.

## Archivos requeridos

Sube estos EX5 en:

`storage/app/public/scanners-bin/`

Con estos nombres exactos:

- `AETBOOM.ex5`
- `AETCRASH.ex5`
- `AETGAINX.ex5`
- `AETPAINX.ex5`

## Verificacion rapida

1. Ejecuta:

```bash
php artisan optimize:clear
php artisan config:cache
```

2. Inicia sesion con un usuario no-free.
3. Abre dashboard y descarga por broker.
4. Debes recibir dos archivos EX5 por broker.

## Errores esperados

Si falta un archivo EX5, el sistema devuelve error 422 con mensaje:

- ES: archivo precompilado no encontrado.
- EN: precompiled EX5 file was not found.

## Limitacion importante

En `precompiled` no hay personalizacion por usuario durante descarga.
Si necesitas control por cuenta/fecha por usuario, debes implementarlo dentro del EA (validacion online contra API) o volver a compilacion por usuario en un entorno con MetaEditor.
