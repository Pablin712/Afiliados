# Vistas de Usuario: Panel, Mi Red y Mis Ganancias

## Objetivo
Estas vistas exponen la actividad del afiliado autenticado sin mostrar segmentos de red que no le pertenecen.

## Modulos agregados
- `Panel` (`/dashboard`): resumen personal con KPIs de red, ganancias del mes, saldo pendiente, membresia actual, sponsor y actividad reciente.
- `Mi red` (`/mi-red`): arbol visual estilo pizarron con sponsor directo + usuario actual + descendencia propia.
- `Mis ganancias` (`/mis-ganancias`): listado personal de profits, con estado, origen y pago asociado.

## Roles
- `Mi red` y `Mis ganancias` son solo para cuentas con rol `user`.
- El usuario `admin` conserva sus vistas administrativas en el dropdown de perfil.
- En `Panel`, si entra `admin`, la etiqueta de suscripcion se muestra como `ADMIN` (no `Free`) y no se muestran ganancias personales.

## Regla de visibilidad
- El usuario ve:
  - Su sponsor directo.
  - Su propia ficha.
  - Sus afiliados directos e indirectos dentro de la profundidad seleccionada.
- El usuario no ve:
  - Afiliados del sponsor.
  - Nodos ajenos a su rama.
  - Insights fuera de su alcance, incluso si conoce el ID.

## Arbol de usuario
- Se reutiliza `AffiliateTreeService` con alcance restringido por usuario.
- El grafo contiene solo nodos visibles para el usuario.
- El modal del sponsor muestra perfil/actividad, pero no lista afiliados del sponsor.

## Ganancias por pago
Para responder con precision "cuanto ganas por cada pago", `profits` ahora guarda estos metadatos de origen:

- `source_payment_id`
- `source_user_id`
- `source_level`

Estos campos se llenan desde `ProfitDistributionService` al aprobar pagos que generan comisiones.

## Navegacion
Los accesos van en la navegacion principal de app, no dentro del dropdown de perfil:

- `Panel`
- `Mi red`
- `Mis ganancias`

## Panel recomendado
El panel se usa como capa de lectura rapida para usuarios free y de pago.

KPIs recomendados:
- Afiliados en red
- Ganancias del mes
- Pendiente por cobrar
- Membresia actual

Bloques recomendados:
- Sponsor directo
- Actividad reciente de ganancias
- Nuevos afiliados recientes
- Acciones rapidas hacia red, ganancias y planes

## Estandar aplicado
- `Mis ganancias` usa `x-enhanced-table` y respeta el patron de vista estandar cuando se usa tabla.
- `Mi red` no usa tabla por requerimiento funcional; conserva formato visual del arbol del admin con restricciones de alcance.
- Todos los textos nuevos usan `__()` y se agregan en `resources/lang/es/messages.php` y `resources/lang/en/messages.php`.
