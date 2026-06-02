# Requisitos
una vista que se llame Usuarios o mis usuarios, no usará enhanced-table como una vista estándar, va a verse como un árbol dinámico en el que se puede arrastrar, agrandar, etc.

Como puedes darte cuenta en requisitos, cada usuario está afiliado a otro, todos tienen un sponsor, por lo que te daré un ejemplo.

usuario admin es el usuario principal, raíz, del árbol (como puedes darte cuenta quizás sea lo más indicado que usemos un service Arbol, que sirva como clase para hacer la estructura de datos), continuando, el usuario admin es raíz, usuario Pablo es su afiliado (sponsor admin), este usuario Pablo invitó a otros 3 usuarios (alcanzó la meta de 3 entonces su membresía pasa de customer a begginer), estos 3 usuarios con sponsor = Pablo son Andres, Juan y Pedro, Juan invita a 10 personas, Andres a 2, Pedro a 5, María es una invitada de Juan (sponsor Juan).

entonces los niveles de María serían:
sponsor nivel 1: Juan
sponsor nivel 2: Pablo
sponsor nivel 3: admin (Esteban)

el admin no se toma en cuenta porque es dueño del programa, los niveles sirven para darles ganancias (como puedes leer en la documentación), entonces María tendría dos sponsors (siempre se ve sponsors 3 niveles o hasta que se llegue a Esteban, Esteban, o el admin, es el usuario final, la raíz)

los niveles de Pablo serían:
afiliados nivel 1: Andres, Juan, Pedro
afiliados nivel 2: invitados de Juan (María y 9 personas más), invitados de Andres, invitados de Pedro
afiliados nivel 3: invitados de María y todos los que sean invitados por las personas del nivel 2

## Vista usuarios
se puede ver a todos los usuarios en forma de arbol, o mapa mental mejor, se podrá manejar como si fuese un pizarrón, si doy clic en uno de ellos se abre un modal de visión del usuario (detalles de ese usuario, profits, afiliados, fecha de unión, último pago, etc) tiene que ser lo más moderno posible.

## Lógica
Será necesario hacer un Service Arbol, que estructure todo esto, o modificar los modelos para agregarles las propiedades sponsorN1, sponsorN2, sponsorN3 (null si es Esteban en cualquiera de estos niveles), afiliadosN1, afiliadosN2, afiliadosN3

las ganancias de un usuario es cuando se aprueba un pago y se registra una nueva membresía customer, cuando esto sucede, un porcentaje definido en la documentación requisitos principal será registrado como profits o ganancias para el usuario sponsor de los 3 niveles.

los profits son registrados como pendientes al principio, y cuando el admin ya registra como pagado o enviado, entonces se actualiza a ya pagado, esto es para que el admin pueda llevar organizado sus finanzas, obviamente los pagos de las membresías son registrados como ingresos, los pagos a los profits serán egresos para el admin, se necesita saber ganancias del mes o del día, habría que hacer una tabla de estadísticas nueva, para que esta tabla registre cada día ingresos de ese día, cuatos nuevos usuarios unidos o registrados, cuantos usuarios se volvieron clientes (membresía customer), egresos, ganancias (en relación de admin), y entre más columnas si hubiera le agregas porque hasta ahí pensé, para poder registrar esto lo haré con n8n con un cron trigger al final del día, por lo que es necesario hacer una api que permita registrar las estadísticas del día (haz uno también para registrar las de ayer, en donde se basa en la fecha) para quizas hacerlo más exacto, habrían dos apis así tengo dos alternativas de registrar las estadísticas, si hacerlo a las 23:59 del propio día o hacerlo al siguiente a las 4am

es necesario ver un dashboard de finanzas, que pueda ver las estadísticas registradas por lo que yo quiera ver, un gráfico de líneas de ingresos, costos, y que el de ganancias se vea como un gráfico de velas como si fuese trading, indicadores de total de usuarios, total de usuarios con membresía customer, beginner, etc. que se pueda filtrar por día, meses, últimos 30 días, cuanto pendiente me falta por pagar a los usuarios (soy admin)

Ya ves que ahora estamos en la parte compleja
