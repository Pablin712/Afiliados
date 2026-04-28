# Red de Afiliados
## Cambio de lógica
En esta sección se describe el cambio de lógica que se realizó en la red de afiliados, pasando de una lógica basada en referidos a una lógica basada en niveles. Una red más compleja y profesional.

Cliente Esteban pidió este cambio, detallaré como está armada esta arquitectura.

## Algunas reglas
1. Cada nuevo afiliado o renovación, otorga 100 pts al sponsor. Ahora se calculará por puntos.
2. Los afiliados no directos también dan puntos, pero dependiendo de la situación que mencionaremos en adelante.
3. Ahora cada usuario tendrá un rango (membership): actualmente son 5 tipos de membresía (customer, beginner, explorer, proffesional, elite) Bueno ahora habrán 7 rangos de red además del customer y free.
- Beginner
- Constructor
- Explorer
- Proffesional
- Elite
- Master
- Legend

4. Para subir de rango, el usuario debe cumplir ciertos requisitos, como tener un número mínimo de afiliados directos o alcanzar un cierto número de puntos, entre otros requisitos.

5. Habrán 7 niveles de red, los cuales son:
- Nivel 1: Solo afiliados directos, cada afiliado directo suma 100 pts. Usuario nuevo o membresía nueva da comisión de $22, y reactivación de membresía da comisión de $18.
- Nivel 2: Afiliados indirectos, cada afiliado de este nivel suma 100 pts. Sea usuario nuevo o reactivación de membresía, su comisión es de $12.
- Nivel 3: Afiliados indirectos, cada afiliado de este nivel suma 100 pts. Sea usuario nuevo o reactivación de membresía, su comisión es de $8.
- Nivel 4: Afiliados indirectos, cada afiliado de este nivel suma 100 pts. Sea usuaario nuevo o reactivación de membresía, su comisión es de $6.
- Nivel 5: Afiliados indirectos, cada afiliado de este nivel suma 100 pts. Sea usuario nuevo o reactivación de membresía, su comisión es de $3.50.
- Nivel 6: Afiliados indirectos, cada afiliado de este nivel suma 100 pts. Sea usuario nuevo o reactivación de membresía, su comisión es de $2.50.
- Nivel 7: Afiliados indirectos, cada afiliado de este nivel suma 100 pts. Sea usuario nuevo o reactivación de membresía, su comisión es de $1.20.

6. Nueva regla, solo no pagan reactivación de membresía cuando llegan a la meta de 3 nuevos usuarios customer. (reactivaciones no cuentan, solo nuevos), si esto no se logra, entonces se paga lo normal, el costo de la membresía o reactivación, ninguna membresía tiene el privilegio de gratuidad, solo llegan a ese beneficio si son constantes en tener 3 nuevos siempre.
## Rango 0A: Free
- No tiene afiliados directos.
- No tiene afiliados indirectos.
- No compró membresía

## Rango 0: Customer
- No tiene afiliados directos.
- No tiene afiliados indirectos.
- Compró membresía

## Rango 1: Beginner
- Tiene al menos 1 afiliado directo.
- Solo gana por nivel 1: No tiene afiliados indirectos.
- Compró membresía
- Tiene 100 pts por cada afiliado.

## Rango 2: Constructor
- Tiene al menos 3 afiliados directos (alcanzó 300 pts).
- Desbloquea nivel 2: Puede ganar por afiliados indirectos (suman 100 pts también)
- Compró membresía
- Tiene 100 pts por cada afiliado.

## Rango 3: Explorer
- Tiene al menos 5 afiliados directos (alcanzó 500 pts).
- Desbloquea nivel 3: Puede ganar por afiliados indirectos (suman 100 pts cada usuario en la red)
- Compró membresía
- Tiene 100 pts por cada afiliado.
- Recibe bono de $40 por llegar aquí, si en otro mes se mantiene, su bono es $20

## Rango 4: Proffesional
- Tiene al menos 8 afiliados directos.
- Tiene al menos 1200 puntos en equipo (sean de afiliados directos o indirectos)
- Desbloquea nivel 4: Puede ganar por afiliados indirectos (suman 100 pts cada usuario en la red)
- Compró membresía
- Tiene 100 pts por cada afiliado.
- Recibe bono de $100 por llegar aquí, si en otro mes se mantiene, su bono es $60

## Rango 5: Elite
- Tiene al menos 10 afiliados directos.
- Tiene al menos 2000 puntos en equipo (sean de afiliados directos o indirectos)
- Tiene al menos 2 afiliados en rango 2 o mayor
- Desbloquea nivel 5: Puede ganar por afiliados indirectos (suman 100 pts cada usuario en la red)
- Compró membresía
- Tiene 100 pts por cada afiliado.
- Recibe bono de $250 por llegar aquí, si en otro mes se mantiene, su bono es $175

## Rango 6: Master
- Tiene al menos 12 afiliados directos.
- Tiene al menos 4000 puntos en equipo (sean de afiliados directos o indirectos)
- Tiene al menos 2 afiliados en rango 4 o mayor
- Desbloquea nivel 6: Puede ganar por afiliados indirectos (suman 100 pts cada usuario en la red)
- Compró membresía
- Tiene 100 pts por cada afiliado.
- Recibe bono de $550 por llegar aquí, si en otro mes se mantiene, su bono es $400

## Rango 7: Legend
- Tiene al menos 15 afiliados directos.
- Tiene al menos 9000 puntos en equipo (sean de afiliados directos o indirectos)
- Tiene al menos 3 afiliados en rango 4 o mayor
- Desbloquea nivel 7: Puede ganar por afiliados indirectos (suman 100 pts cada usuario en la red)
- Compró membresía
- Tiene 100 pts por cada afiliado.
- Recibe bono de $1100 por llegar aquí, si en otro mes se mantiene, su bono es $800

## Casos probados

### Caso 1
- user_id: 2
- nombre: Gabriel Mercado
- tipo_usuario: free
- afiliados_directos: 1
- puntos_equipo: 100
- membership_actual: free
- membership_target: free
- bono: 0
- profits: []
- observacion: el sponsor free no sube de rango ni gana comisión aunque tenga 1 afiliado customer aprobado
- success: true

### Caso 2
- user_id: 3
- nombre: Adam Martos
- tipo_usuario: customer
- afiliados_directos: 1
- puntos_equipo: 100
- membership_actual: customer
- membership_target: beginner
- bono: 0
- profits: []
- observacion: en la primera ejecución el sponsor sí subía a beginner, pero no cobraba comisión porque la distribución corría antes del recálculo de rango; este flujo ya fue corregido para recalcular primero y distribuir después
- success: true

### Caso 3
- user_id: 6
- nombre: Alberto Sanabria
- tipo_usuario: customer
- afiliados_directos: 1
- puntos_equipo: 100
- membership_actual: beginner
- membership_target: beginner
- bono: 0
- profits: [{"source_level":1,"amount":22.00,"state":"pending"}]
- observacion: después de corregir el orden del flujo, el sponsor customer con 1 afiliado customer aprobado sube a beginner y cobra su comisión de nivel 1 en la misma aprobación
- success: true

### Caso 4
- user_id: 8
- nombre: Aaron Pozo
- tipo_usuario: customer
- afiliados_directos: 3
- puntos_equipo: 300
- membership_actual: constructor
- membership_target: constructor
- bono: 0
- profits: [{"source_user_id":9,"source_level":1,"amount":22.00,"state":"pending"},{"source_user_id":10,"source_level":1,"amount":22.00,"state":"pending"},{"source_user_id":11,"source_level":1,"amount":22.00,"state":"pending"}]
- observacion: al aprobar 3 afiliados customer directos, el sponsor alcanza rango constructor y acumula comisión total de 66.00 en nivel 1
- success: true

### Caso 5
- user_id: 8
- nombre: Aaron Pozo
- tipo_usuario: constructor (reactivación)
- afiliados_directos: 3
- puntos_equipo_antes: 300
- puntos_equipo_despues: 300
- puntos_sumados_evento: 0
- membership_actual: constructor
- membership_target: constructor
- bono: 0
- profits: [{"source_user_id":9,"source_level":1,"amount":18.00,"state":"pending"},{"source_user_id":10,"source_level":1,"amount":18.00,"state":"pending"},{"source_user_id":11,"source_level":1,"amount":18.00,"state":"pending"}]
- comision_delta: 54.00
- observacion: en renovación/reactivación de afiliados (no primer pago), la comisión de nivel 1 es 18.00 por afiliado; balance del sponsor pasó de 66.00 a 120.00
- success: true

### Caso 6
- user_id: 8
- nombre: Aaron Pozo
- tipo_usuario: constructor (reactivación parcial de red)
- afiliados_directos: 2
- puntos_equipo_antes: 300
- puntos_equipo_despues: 200
- puntos_sumados_evento: -100
- membership_actual: beginner
- membership_target: beginner
- bono: 0
- profits: [{"source_user_id":9,"source_level":1,"amount":18.00,"state":"pending"},{"source_user_id":10,"source_level":1,"amount":18.00,"state":"pending"}]
- comision_delta: 36.00
- observacion: se simuló que solo 2 de 3 afiliados directos reactivaron (1 quedó expirado). Resultado: el sponsor baja de constructor a beginner y gana comisión solo por 2 renovaciones (2 x 18.00)
- success: true

### Caso 7
- user_id: 8
- nombre: Aaron Pozo
- tipo_usuario: constructor (reactivación total + 2 afiliados nuevos)
- afiliados_directos: 5
- puntos_equipo_antes: 200
- puntos_equipo_despues: 500
- puntos_sumados_evento: 300
- membership_actual: explorer
- membership_target: explorer
- bono: 0
- profits: [{"source_user_id":9,"source_level":1,"amount":18.00,"state":"pending"},{"source_user_id":10,"source_level":1,"amount":18.00,"state":"pending"},{"source_user_id":11,"source_level":1,"amount":18.00,"state":"pending"},{"source_user_id":12,"source_level":1,"amount":22.00,"state":"pending"},{"source_user_id":13,"source_level":1,"amount":22.00,"state":"pending"}]
- comision_delta: 98.00
- observacion: al reactivar sponsor + 3 afiliados existentes y aprobar 2 afiliados nuevos, el sponsor llega a 5 directos activos y sube a explorer. La comisión mezcla renovaciones (3 x 18.00) y nuevas altas (2 x 22.00)
- success: true

### Caso 8
- user_id: 3
- nombre: Adam Martos
- tipo_usuario: beginner
- afiliados_directos: 1
- afiliados_indirectos_nivel_2: 1
- puntos_equipo: 200
- membership_actual: beginner
- membership_target: beginner
- bono: 0
- profits: []
- comision_delta: 0.00
- observacion: se aprobó un usuario nuevo (id 14) con sponsor del afiliado directo del beginner (id 5), es decir evento en nivel 2 para el beginner. Resultado: no recibe comisión, porque beginner solo desbloquea nivel 1. El sponsor directo sí recibió 22.00 en nivel 1.
- success: true

### Caso 9
- user_id: 15
- nombre: Ian Luján
- tipo_usuario: constructor
- afiliados_directos: 3
- afiliados_indirectos_nivel_2_nuevos: 2
- puntos_equipo_antes: 300
- puntos_equipo_despues: 500
- points_delta: 200
- membership_actual: constructor
- membership_target: constructor
- bono: 0
- profits: [{"source_user_id":19,"source_level":2,"amount":12.00,"state":"pending"},{"source_user_id":20,"source_level":2,"amount":12.00,"state":"pending"}]
- comision_delta: 24.00
- observacion: para un usuario constructor, los afiliados indirectos nuevos de nivel 2 pagan 12.00 cada uno. En este caso: 2 indirectos x 12.00 = 24.00
- success: true

### Caso 10
- user_id: 21
- nombre: Natalia Calvo
- tipo_usuario: explorer
- afiliados_directos: 8
- puntos_equipo_antes: 500
- puntos_equipo_despues: 800
- points_delta: 300
- membership_actual: explorer
- membership_target: explorer
- bono: 0
- profits: [{"source_user_id":69,"source_level":1,"amount":22.00,"state":"pending"},{"source_user_id":70,"source_level":1,"amount":22.00,"state":"pending"},{"source_user_id":71,"source_level":1,"amount":22.00,"state":"pending"}]
- comision_delta: 66.00
- observacion: aunque Natalia llega a 8 directos activos, solo alcanza 800 puntos de equipo. No sube a professional porque ese rango exige 8 directos y 1200 puntos. Se mantiene en explorer y gana comisión nivel 1 por los 3 nuevos directos (3 x 22.00)
- success: true

### Caso 11
- user_id: 22
- nombre: Yago Bustamante
- tipo_usuario_antes: explorer
- tipo_usuario_despues: professional
- afiliados_directos_antes: 5
- afiliados_directos_despues: 8
- afiliados_indirectos_nivel_2_nuevos: 5 (uno por cada directo ya existente: 34,35,36,37,38)
- puntos_equipo_antes: 500
- puntos_equipo_despues: 1300 (8 directos x100 + 5 indirectos x100)
- membership_actual: professional
- membership_target: professional
- bono: 0
- profits: [{"source_user_id":72,"source_level":1,"amount":22.00},{"source_user_id":73,"source_level":1,"amount":22.00},{"source_user_id":74,"source_level":1,"amount":22.00},{"source_user_id":75,"source_level":2,"amount":12.00},{"source_user_id":76,"source_level":2,"amount":12.00},{"source_user_id":77,"source_level":2,"amount":12.00},{"source_user_id":78,"source_level":2,"amount":12.00},{"source_user_id":79,"source_level":2,"amount":12.00}]
- comision_delta: 126.00 (3 directos nivel 1 x22.00 + 5 indirectos nivel 2 x12.00)
- observacion: Yago sube de explorer a professional porque alcanza 8 directos y 1300 puntos de equipo (threshold: 8 directos + 1200 puntos). Los 5 indirectos también generan comisión nivel 2 de 12.00 cada uno porque professional desbloquea nivel 2. No hay requisito de sub-rango para professional.
- success: true

### Caso 12
- user_id: 23
- nombre: Marta Bermúdez
- tipo_usuario_antes: explorer
- tipo_usuario_despues: explorer
- afiliados_directos: 5 (sin cambio)
- puntos_equipo_antes: 500
- puntos_equipo_despues: 1000 (5 directos x100 + 5 indirectos nuevos x100)
- membership_actual: explorer
- membership_target: explorer
- bono: 0
- nuevos_indirectos:
  - nivel 2: user_id=80 (via 39) y user_id=81 (via 40)
  - nivel 3: user_id=82 (via 80)
  - nivel 4: user_id=83 y user_id=84 (via 82)
- profits: [{"source_user_id":80,"source_level":2,"amount":12.00},{"source_user_id":81,"source_level":2,"amount":12.00},{"source_user_id":82,"source_level":3,"amount":8.00}]
- comision_delta: 32.00 (2 x nivel2 x12.00 + 1 x nivel3 x8.00)
- nivel_4_recibido: false (explorer solo desbloquea hasta nivel 3)
- observacion: Marta permanece en explorer porque solo tiene 5 directos activos (necesita 8 + 1200 puntos para professional). Recibe comisión de nivel 2 y 3, pero NO de nivel 4 ni 5 porque explorer solo desbloquea hasta nivel 3. Los usuarios 83 y 84 (nivel 4) no generan profit para Marta. Regla: cada rango desbloquea un nivel adicional (beginner=1, constructor=2, explorer=3, professional=4, elite=5, master=6, legend=7).
- success: true

### Caso 13
- user_id: 24
- nombre: Aitana Trejo
- tipo_usuario_antes: explorer
- tipo_usuario_despues: professional
- afiliados_directos_antes: 5
- afiliados_directos_despues: 10
- afiliados_indirectos_nivel_2_nuevos: 10 (uno debajo de cada directo: 44,45,46,47,48,85,86,87,88,89)
- puntos_equipo_antes: 500
- puntos_equipo_despues: 2000 (10 directos x100 + 10 indirectos x100)
- membership_actual: professional
- membership_target: professional
- bono: 0
- profits: [{"source_user_id":85,"source_level":1,"amount":22.00},{"source_user_id":86,"source_level":1,"amount":22.00},{"source_user_id":87,"source_level":1,"amount":22.00},{"source_user_id":88,"source_level":1,"amount":22.00},{"source_user_id":89,"source_level":1,"amount":22.00},{"source_user_id":90,"source_level":2,"amount":12.00},{"source_user_id":91,"source_level":2,"amount":12.00},{"source_user_id":92,"source_level":2,"amount":12.00},{"source_user_id":93,"source_level":2,"amount":12.00},{"source_user_id":94,"source_level":2,"amount":12.00},{"source_user_id":95,"source_level":2,"amount":12.00},{"source_user_id":96,"source_level":2,"amount":12.00},{"source_user_id":97,"source_level":2,"amount":12.00},{"source_user_id":98,"source_level":2,"amount":12.00},{"source_user_id":99,"source_level":2,"amount":12.00}]
- comision_delta: 230.00 (5 directos nivel 1 x22.00 + 10 indirectos nivel 2 x12.00)
- observacion: Aitana sube de explorer a professional porque alcanza 10 directos y 2000 puntos de equipo. No sube a elite aunque cumple directos y puntos, porque elite exige además 2 afiliados directos en rango constructor o mayor. En este escenario, cada directo tiene solo 1 customer debajo, así que sus directos quedan en beginner, no en constructor.
- success: true

### Caso 14
- user_id: 25
- nombre: Gonzalo Costa
- nota_escenario: en la base actual, Elena Lugo (26) e Isabel Escobedo (27) no son afiliadas directas de Gonzalo; ambas tienen sponsor_id=1. El caso se ejecutó sobre la red real actual de Gonzalo.
- tipo_usuario_antes: explorer
- tipo_usuario_despues: professional
- afiliados_directos_antes: 5
- afiliados_directos_despues: 13
- afiliados_indirectos_nivel_2_nuevos: 2 (user_id=108 vía 100, user_id=109 vía 101)
- puntos_equipo_antes: 500
- puntos_equipo_despues: 1500 (13 directos x100 + 2 indirectos x100)
- membership_actual: professional
- membership_target: professional
- bono: 100.00 (solo corresponde el bono de professional por ascenso)
- profits: [{"source_user_id":100,"source_level":1,"amount":22.00},{"source_user_id":101,"source_level":1,"amount":22.00},{"source_user_id":102,"source_level":1,"amount":22.00},{"source_user_id":103,"source_level":1,"amount":22.00},{"source_user_id":104,"source_level":1,"amount":22.00},{"source_user_id":105,"source_level":1,"amount":22.00},{"source_user_id":106,"source_level":1,"amount":22.00},{"source_user_id":107,"source_level":1,"amount":22.00},{"source_user_id":108,"source_level":2,"amount":12.00},{"source_user_id":109,"source_level":2,"amount":12.00},{"detalle":"rank_bonus|promotion|professional","amount":100.00}]
- comision_delta: 300.00 (8 directos nivel 1 x22.00 + 2 indirectos nivel 2 x12.00 + bono professional 100.00)
- observacion: Gonzalo sube de explorer a professional porque llega a 13 directos activos y 1500 puntos de equipo, superando el umbral de professional (8 directos + 1200 puntos). Los 2 nuevos indirectos pagan 12.00 cada uno en nivel 2. Regla corregida de bonos: en un mismo evento solo se paga el bono del rango alcanzado; el bono de mantenimiento solo aplica si el mes anterior ya tenía esa misma membresía.
- success: true

*Casos futuros, usuarios preparados*
21 Natalia Calvo
22 Yago Bustamante
23 Marta Bermúdez
24 Aitana Trejo
25 Gonzalo Costa
26 Elena Lugo
27 Isabel Escobedo
28 Hugo Manzano

