# Función de expulsión de grupos de WhatsApp y Telegram a miembros no activos
Cuando a un usuario se le acaba su membresía y este no ha pagado o renovado, entonces quiere decir que su membresía es free y ya no es un customer u otra membresía activa, entonces, se le debe expulsar del grupo de whatsapp, y los de telegram.

Entonces te dejo las apis respectivas de whatsapp y telegram para que agregues esta función a los métodos de finalización de membresía customer existentes, es decir las que pasan a ser free.

# API de expulsión de WhatsApp
La única variable que es dinámica, que se cambiaría, es participants, el cual es el número del usuario, o los números de los usuarios en caso de limpieza semanal. Todo lo demás es tal y como está la api: apikey, url, instance, gruupid.

POST https://evoapi.abigailsoft.com/group/updateParticipant/AET-SAS?groupJid=120363425909738995@g.us
apikey: DAF579E359CA-43AA-959F-16EE1ED51F7A
body: 
{
  "action": "remove",
  "participants": [
    "593961778319"
  ]
}

# Telegram
A continuación los tres grupos de telegram

## AET PREMIUM
chat_id: -5279685071
## AET VIP DERIV
chat_id: -1003633952853
## AET VIP WELTRADE
chat_id: -1003742317642
## BOT TOKEN
ubicado en .env la variable: TELEGRAM_BOT_TOKEN
## API de expulsión de Telegram
POST https://api.telegram.org/bot<TU_BOT_TOKEN>/banChatMember
body:
{
  "chat_id": "-1001234567890",
  "user_id": 123456789
}

*Osbtáculo*
No se sabe el user_id de los usuario hasta el momento, entonces, haremos un proceso que guarde el chat_id de telegram para cada usuario, de esa manera, podemos expulsarlos cuando se hagan free, o identificarlos si es que están en el grupo, cuando compran la membresía, es necesario crear un middleware que revise si el usuario tiene un chat_id de telegram (solo si es customer u otra membresía no free), en caso de no tenerlo, redirige a perfil a que haga el proceso de guardar su chat_id.

¿Cómo lo haremos? Fácil, cada usuario tendrá un código único digamos para Pablo, el código será HOIS81JK10, ese se mostrará en su perfil, y le informa al usuario que escriba ese código al bot (@aetfirstbot), para poder registrar su chat_id.

Este proceso lo haremos con n8n, el webhook para activar este evento cuando un usuario mensajee al bot será: https://autobot.aaronsoft.es/webhook/aet-first-bot
y el flujo sería así:
webhook trigger activa flujo, va al segundo nodo que sería un http post code, el cual es una api local para identificar si el usuario escribió un código válido, y retorna la respuesta,
1. si el código pertenece a un usuario y su chat_id de telegram es null, lo registra y retorna exitoso, además de eso le mensajea a ese chat_id que su telegram fue registrado.
2. si el código pertenece a un usuario y su chat_id ya fue registrado, no hace nada, solo mensajea que el chat_id ya ha sido registrado antes, solo se permite uno por usuario (persona)
3. si el código es incorrecto o es otro tipo de mensaje, entonces el response será 400 y no se mensajeará al cliente o persona (para ver si se usa ese mensaje para otro tipo de ejecuciones futuras)

Entonces la api post code, recibirá parámetros: chat_id, code.
esto permitirá en una sola llamada:
1. Registrar chat_id en caso de encontrar code en un usuario y chat_id de telegram sea null en la bd.
2. Mensajear al usuario para su respuesta de la llamada

La vista de perfil, el cual es redireccionado en middleware solo para usuarios customer u otra membresía no free, tendría un mensaje informativo: Tu chat id de telegram, y un botón eliminar en caso de que el usuario quiera cambiarlo, cuando el chat id de telegram es null, entonces habrá un mensaje informativo: tu código único para registrar tu chat id, envía este código a este chat para que sea registrado -> *link del bot*

El flujo de n8n tendría apenas 2 nodos: el trigger webhook, y el nodo http post code (o mejor llamarlo post chat_id)
Ya que la api de post chat id, haría todo el trabajo, incluído el mensajear al usuario su respuesta.
## API de mensaje al usuario
POST https://api.telegram.org/bot8295898924:AAFgQUPC26H7fyMQu3KsHwvsNym6z9I6EaQ/sendMessage
body:
{
    "chat_id": "",
    "text": ""
}