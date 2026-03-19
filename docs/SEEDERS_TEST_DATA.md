# Seeders de Datos de Prueba

## Objetivo
El seeder `ComprehensiveTestScenarioSeeder` crea un escenario completo para probar:

- Arbol de afiliados con sponsors reales
- Pagos en estados `approved`, `pending`, `rejected`
- Membresias en estados `active`, `free`, `pending_payment`
- Cambio de tipo de membresia por cantidad de afiliados activos directos
- Profits en estados `pending` y `made`
- Transacciones de `income` y `expense`
- Estadisticas financieras diarias (`daily_financial_stats`)
- Auditoria base en tabla `actions`

## Que crea

1. 50 usuarios con rol `user` y relacion de sponsor entre ellos.
2. Cuentas `user_banks` (una por defecto para todos y algunas secundarias).
3. Datos de pagos mixtos:
- Pagos aprobados con ingresos reales a `banks`.
- Pagos pendientes con transaccion en cero (como en flujo real de carga de comprobante).
- Pagos rechazados.
4. Membresias para todos los `user`:
- Regla respetada: todos los `user` tienen una membresia.
- Regla respetada: quienes no tienen membresia activa pagada quedan en `free`.
- Regla aplicada: upgrade de membresia por afiliados activos directos:
- `beginner`: 3 a 9
- `explorer`: 10 a 19
- `professional`: 20 a 29
- `elite`: 30+
5. Profits:
- Se generan profits `pending` por pagos aprobados usando `ProfitDistributionService`.
- Una parte se marca como `made` usando `ProfitPayoutService`.
6. Estadisticas:
- Se registran 30 dias de estadisticas con `DailyFinancialStatsService`.

## Arbol de prueba (resumen)

- Nodo fuerte 1 con 30 afiliados directos activos (deberia escalar a `elite`).
- Nodo fuerte 2 con 10 afiliados directos activos (deberia escalar a `explorer`).
- Nodo fuerte 3 con 3 afiliados directos activos (deberia escalar a `beginner`).
- Usuarios adicionales con combinacion de estados para cubrir pendientes, rechazados y libres.

## Ejecucion

Ejecutar seed completo:

```bash
php artisan db:seed
```

Para entorno limpio:

```bash
php artisan migrate:fresh --seed
```

## Credenciales de prueba

- Admin:
- Email: `Aetsas01@gmail.com`
- Password: valor de `ADMIN_DEFAULT_PASSWORD` o `Admin12345*`

- Usuarios demo:
- Email: `demo.user01@afiliados.test` ... `demo.user50@afiliados.test`
- Password: `User12345*`

## Notas tecnicas

- Este seeder limpia primero los datos operativos del escenario previo (`actions`, `daily_financial_stats`, `profits`, `payments`, `transactions`, `memberships`, `user_banks`, usuarios `user`).
- No elimina el usuario admin ni el catalogo base (`membership_types`, `programs`, `banks`, roles/permisos).
- Si no existe la tabla `daily_financial_stats`, simplemente omite ese paso.
