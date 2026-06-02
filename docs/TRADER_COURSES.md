# Cursos Trader

## Objetivo

Implementar una biblioteca de clases para usuarios trader con experiencia tipo plataforma de cursos, y una consola de administracion para que el admin pueda crear modulos, subir videos, importarlos desde `storage/public/videos` y eliminar contenido.

## Estructura tecnica

- Tabla `course_modules`: modulo, descripcion, orden y estado.
- Tabla `course_videos`: video, modulo, archivo almacenado, peso, orden y estado.
- Vista alumno: `courses.index`.
- Vista admin: `admin.courses.index`.
- Acceso alumno restringido: solo usuarios autenticados con membresia distinta de `free`; admin siempre conserva acceso.
- Rutas nuevas:
  - `GET /cursos`
  - `GET /admin/courses`
  - `POST /admin/courses/modules`
  - `DELETE /admin/courses/modules/{module}`
  - `POST /admin/courses/videos`
  - `DELETE /admin/courses/videos/{video}`
  - `POST /admin/courses/import-existing`

## Fuente de archivos

- Los MP4 heredados pueden llegar desde `storage/app/public/videos`.
- Al importarlos, el sistema los mueve a almacenamiento privado (`storage/app/private/course-videos`) y deja de servirlos por URL publica directa.
- Los videos nuevos del admin ya se guardan directamente en almacenamiento privado.

## Proteccion aplicada

- El alumno no consume el archivo por `asset('/storage/...')`, sino por una ruta autenticada de streaming.
- El reproductor usa `controlsList="nodownload noplaybackrate"`, `disablePictureInPicture` y `disableRemotePlayback`.
- Se bloquea el menu contextual del video para evitar la descarga directa desde el reproductor.
- La respuesta del stream se sirve con `Content-Disposition: inline` y headers `no-store`.

## Limite tecnico

- Esto endurece mucho la descarga directa, pero no equivale a DRM tipo Netflix, Max o Disney.
- En un sitio web tradicional no se puede impedir de forma confiable la grabacion de pantalla sin infraestructura DRM especializada (por ejemplo Widevine/FairPlay/PlayReady y un pipeline de video compatible).

## Mapeo inicial segun cliente

### Modulo scanner

- `instala el scanner.mp4`
- `scanner y volumenes.mp4`
- `Scanner con simetria y reposicionamiento.mp4`

### Modulo 1

- `modulo 1.  simetrias.mp4`
- `scanner modulo 1  tendencias.mp4`
- `1_modulo 1.  temporalidades mp4.mp4`
- Pendiente en carpeta: `mod 1 techos y pisos.mp4` no estaba presente al momento de implementar.

### Modulo 2

- `mod 2   fractalidades y clean trades 2 clases.mp4`

### Modulo 3

- `fvgs, transiciones.mp4`
- `volumen y su funcion.mp4`

## Notas operativas

- El importador registra en base de datos los MP4 ya existentes sin volver a subirlos.
- Los videos nuevos se cargan desde el panel admin y se guardan en `public/videos`.
- El admin puede eliminar videos y tambien eliminar modulos vacios.
- Los usuarios `free` no ven el enlace de cursos en navegacion y si intentan entrar manualmente son redirigidos a planes con mensaje de restriccion.
