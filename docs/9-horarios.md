# Requisito de horarios para AET Trader Acadamy
Esteban quiere una nueva vista en el apartado del navbar: Horarios, aquí se podrá ver el calendario en mensual o semanal de las clases pendientes que habrá por parte de los profesores, entonces

1. Se crea un nuevo rol de usuario: Teacher, por lo que ya no serían solo dos roles, ahora serán 3: Admin, User, Teacher

El horario sería una vista en donde los profesores pueden programar clases, y no importaría si se cruzan con otros profesores, por lo que debe ofrecer una buena vista para mejor experiencia de los usuarios, y estos puedan escoger a gusto sus clases, el profesor dejaría link de la reunión, descripción y título de la clase, y hora de inicio y fin.

Los usuarios podrían ver el calendario de próximas clases, cada espacio de clase informa: Profesor, hora de inicio, hora de fin, link de la reunión, descripción (por si tiene)

El admin puede editar cualquier reunión de los profesores, a su antojo, y los profesores solo podrían de sus clases, actualmente todos los users tienen rol user y todos cuentan con una membresía, menos el admin, quien no cuenta con una membresía, el admin puede cambiar de rol a los usuarios que desee a teacher, que serían usuarios normales con membresía con más privilegio, enseñar.

Los espacios de las clases programadas estarían de distinto color (un color por profesor) para saber distinguir, y acomodar fácil para el usuario visualmente.

## Diseño de frontend
Para poder hacer uso de un calendario, usa lo más conveniente, se podría crear un componente llamado calendar.blade.php, o algo parecido, para que el código sea mantenible, lo más optimo y moderno posible.
El calendario deberá verse llamativo, elegante, moderno, o amigable.
Las funciones del calendario deberán ser muy funcionales y rápidas, fáciles de usar, sin nungún error o bug.

## Carga de datos
Cuando un teacher o admin agendan una reunión, o editan, al guardar cambios no deberá recargar la página, se actualiza solo ese cuadro en vivo, sin necesidad de recargar la página, esto para mejor experiencia de los teachers y el admin