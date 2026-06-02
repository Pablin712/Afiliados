# Endpoints
Se enlistarán los endpoints ordenadamente para que otras aplicaciones las usen.
Actualmente tenemos algunos, ahora agregaremos uno más el cual es:
1. api de verificación de usuario con membresía activa (customer u otra que no sea free)
## Verificación de membresía activa
### Endpoint
```
GET https://aettraderacademy.es/public/api/verify-membership
```
### Descripción
Este endpoint se utiliza para verificar si un usuario tiene una membresía activa (customer u otra que no sea free) en el sistema. Es útil para controlar el acceso a ciertos recursos o funcionalidades que requieren una membresía activa.
### Parámetros
- `email` (requerido): Email único del usuario, con este le identificamos
### Respuesta
- **200 OK**: Si se consultó el usuario correctamente
```json
//Respuesta 1
{
  "active": "true",
  "membership": "customer",
  "message": "El usuario tiene una membresía activa."
}
//Respuesta 2
{
  "active": "false",
  "membership": "free",
  "message": "El usuario no tiene una membresía activa."
}
//Respuesta 3
{
  "active": "true",
  "membership": "explorer",
  "message": "El usuario tiene una membresía activa."
}

//Respuesta 4 (email no encontrado)
{
  "active": "false",
  "membership": null,
  "message": "No se encontró el usuario."
}
```
