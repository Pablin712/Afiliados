# Últimos requisitos
## Bancos de usuarios
Actualmente, en la vista profile del usuario, se puede agregar cuenta de binance, lo que también quiero que pueda agregar, es un banco que tenga (podría ser pichincha, guayaquil, produbanco, etc), entonces el usuario podrá guardar dos bancos:
1. Binance (ya listo e implementado)
2. Otro Banco (por si el usuario desea guardar otro banco, aquí el usuario guarda nombre de banco, titular, etc)
## Cobro de comisiones de usuario al administrador
Se requiere un botón de cobro de comisiones en la vista del usuario (mis-ganancias.blade)
En este botón, el usuario notifica al administrador que tal usuario está cobrando el total de ganancias, por lo que el admin (Esteban) tiene que pagar.

*¿Cómo funcionará este botón?*
El usuario deberá tener una cuenta de binance u otro banco asociado, para que Esteban pueda realizar el pago. Si no tiene, entonces sale un modal de aviso, esto le dirá al usuario que debe asociar una cuenta bancaria (binance u otro), este modal tendrá un botón 'Agregar mi cuenta bancaria' que lo llevará a la vista profile, la sección de bancos.

Al hacer clic en el botón, y tiene una cuenta de banco asociada (sea binance, otro banco o ambas), se enviará una notificación por WhatsApp (usando evo api) al administrador (Esteban) indicando que el usuario ha solicitado el cobro de sus ganancias. El administrador podrá revisar esta solicitud y proceder a realizar el pago correspondiente al usuario.

la notificación se verá algo así:
"Hola Esteban, el usuario *[Nombre_Usuario]* ha solicitado el cobro de sus ganancias de *[monto_total]*. Por favor, revisa su solicitud y realiza el pago correspondiente."

*¿Qué habrá del otro lado, en el lado admin?*
Actualmente hay la vista profits management, la cual indica las ganancias de los usuarios, quiero ajustar un poco a esta vista, aunque la tabla esté bien, actualmente un usuario tiene muchos registros de sus ganancias, por lo que para Esteban será muy molesto o demoroso el estar actualizando como pagado cada una de esas ganancias, entonces hay que agregar una pestalla, en el que se muestre la misma tabla, pero indicando sumatoria de los usuarios, es decir hacer join.

Entonces, en la vista de administración, habrá dos pestañas:
1. Ganancias por usuario: Aquí se mostrará la tabla actual, con el detalle de cada ganancia individual de los usuarios. Esta vista está lista, necesaria.
2. Ganancias totales por usuario: Aquí se mostrará una tabla que agrupe las ganancias por usuario, mostrando la sumatoria total de las ganancias de cada usuario. Esta vista se necesita implementar. Tendría el mismo botón de acciones que la primera pestaña, pero pagaría el total, al pagar esa sumatoria, entonces obviamente todos los profits pendientes de ese usuario quedarían con estado: pagado.

## Evo-Api
Aquí te dejo el api para que puedas implementar las notificaciones.
Solamente la variable text será dinámica, el resto ya está predefinida.
POST https://evoapi.abigailsoft.com/message/sendText/AET-SAS
headers:
apiKey: DAF579E359CA-43AA-959F-16EE1ED51F7A
body:
{
    "number": "+593986248511",
    "text": *notificación generada*
}

Notificación para el usuario
Además de la notificación para el administrador, también se requiere enviar una notificación al usuario cuando su solicitud de cobro de ganancias haya sido procesada por el administrador. Esta notificación informará al usuario que su solicitud ha sido recibida y que el administrador está revisando su solicitud para proceder con el pago.
Cuando el admin de clic en la acción confirmar pago (pago total o el profit individual) entonces se envía la notificación del monto pagado.
el mensaje quedaría:
"Hola *[Nombre_Usuario]*, tu solicitud de cobro de ganancias por un monto de *[monto_total]* ha sido procesada y pagada por el administrador. Revisa tu cuenta bancaria"
POST https://evoapi.abigailsoft.com/message/sendText/AET-SAS
headers:
apiKey: DAF579E359CA-43AA-959F-16EE1ED51F7A
body:
{
    "number": *numero_cliente*,
    "text": *notificación generada*
}
