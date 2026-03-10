# 🌐 Multi-Language Setup Guide

Este proyecto está configurado con soporte multiidioma para **Inglés** y **Español**.

## 📁 Estructura de Carpetas

```
resources/
├── lang/
│   ├── en/              # Traducciones en inglés
│   │   ├── messages.php
│   │   └── validation.php
│   └── es/              # Traducciones en español
│       ├── messages.php
│       └── validation.php
```

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
APP_LOCALE=en                    # Idioma por defecto
APP_FALLBACK_LOCALE=en           # Idioma de respaldo
```

### Cambiar el Idioma

Hay varias formas de cambiar el idioma:

#### 1. **Query Parameter** (Más prioritario)
```
https://tuapp.com/dashboard?locale=es
```

#### 2. **Sesión** (Automático después de usar query parameter)
Se guarda en la sesión del usuario.

#### 3. **Cookie** (Persistente)
Se guarda por 1 año si el usuario cambia el idioma.

#### 4. **Default** (Config)
Usa la configuración de `APP_LOCALE` en `.env`

## 🔤 Helpers de Traducción

### 1. **Función Global `trans()` o `__()`**

La forma más común en Laravel:

```blade
<!-- En Blade Templates -->
{{ __('messages.welcome') }}
{{ trans('messages.affiliate') }}

<!-- Con parámetros -->
{{ __('messages.error', ['field' => 'email']) }}
```

```php
// En PHP/Controladores
trans('messages.welcome');
__('messages.save');
trans('validation.required', ['attribute' => 'name']);
```

### 2. **TranslationHelper (Helper Personalizado)**

Ubicación: `App\Helpers\TranslationHelper`

```php
use App\Helpers\TranslationHelper;

// Obtener traducción
TranslationHelper::get('messages.welcome');

// Obtener idioma actual
$locale = TranslationHelper::getCurrentLocale(); // 'en' o 'es'

// Cambiar idioma
TranslationHelper::setLocale('es');

// Obtener idiomas disponibles
$locales = TranslationHelper::getAvailableLocales(); // ['en', 'es']

// Traducción con valor por defecto
TranslationHelper::getOrDefault('custom.key', 'Valor por defecto');

// Verificar si existe una clave de traducción
if (TranslationHelper::hasKey('messages.welcome')) {
    echo TranslationHelper::get('messages.welcome');
}
```

## 📝 Ejemplos de Uso en el Proyecto

### En Blade Templates

```blade
<!-- Navbar con selector de idioma -->
<div class="navbar">
    <h1>{{ __('messages.welcome') }}</h1>
    
    <div class="language-selector">
        <a href="?locale=en">English</a>
        <a href="?locale=es">Español</a>
    </div>
</div>

<!-- Formulario con validación -->
<form method="POST" action="/save">
    <label>{{ __('messages.affiliate') }}</label>
    <input type="text" name="name" placeholder="{{ __('messages.name') }}">
    
    <button type="submit">{{ __('messages.save') }}</button>
    <a href="/back">{{ __('messages.back') }}</a>
</form>

<!-- Mensajes de éxito/error -->
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

### En Controladores

```php
namespace App\Http\Controllers;

use App\Helpers\TranslationHelper;

class AffiliateController extends Controller
{
    public function store()
    {
        // Cambiar a español
        TranslationHelper::setLocale('es');

        // Obtener mensaje traducido
        $successMessage = __('messages.success');

        return redirect()->back()->with('success', $successMessage);
    }

    public function changeLanguage($locale)
    {
        if (in_array($locale, ['en', 'es'])) {
            TranslationHelper::setLocale($locale);
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
```

### En Validaciones (FormRequest)

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffiliateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'sponsor_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'email.email' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.unique' => __('validation.unique', ['attribute' => __('validation.attributes.email')]),
            'sponsor_id.exists' => __('validation.required', ['attribute' => __('validation.attributes.sponsor_id')]),
        ];
    }
}
```

### En Servicios/Modelos

```php
namespace App\Services;

use App\Helpers\TranslationHelper;

class AuditLogService
{
    public function log($action, $model)
    {
        $locale = TranslationHelper::getCurrentLocale();
        
        Log::info(__('messages.action_logged'), [
            'action' => $action,
            'model' => $model,
            'locale' => $locale,
        ]);
    }
}
```

## 🔧 Agregar Nuevas Traducciones

### Paso 1: Crear archivo en ambos idiomas

`resources/lang/en/custom.php`:
```php
<?php
return [
    'key_name' => 'English translation',
];
```

`resources/lang/es/custom.php`:
```php
<?php
return [
    'key_name' => 'Traducción al español',
];
```

### Paso 2: Usar en el proyecto

```blade
{{ __('custom.key_name') }}
```

## 🗣️ Cambiar Idioma en Rutas

Opción 1: Crear una ruta para cambiar idioma

```php
// routes/web.php
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'es'])) {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }
    return redirect()->back();
})->name('locale.change');
```

Opción 2: Usar URL parameters automáticamente (ya configurado)

```
https://tuapp.com?locale=es
```

## 📊 Estructura de Archivos de Traducción

Cada archivo debe ser un array PHP con el formato:

```php
<?php
return [
    'nested' => [
        'key' => 'value',
    ],
    'simple_key' => 'simple value',
];
```

Acceso:
```blade
{{ __('filename.nested.key') }}
{{ __('filename.simple_key') }}
```

## ✅ Checklist para Traducir el Proyecto

- [ ] Agregar todas las cadenas de texto a los archivos de traducción
- [ ] Usar `__()` o `trans()` en lugar de hardcoded strings
- [ ] Incluir mensajes de validación en `validation.php`
- [ ] Probar amb os idiomas en la aplicación
- [ ] Verificar que el selector de idioma funcione correctamente
- [ ] Actualizar documentación si agrega más idiomas

## 🚀 Próximos Pasos

1. Reemplaza todos los textos hardcoded con `__('clave')`
2. Agrega más traducciones según sea necesario
3. Prueba ambos idiomas en tu aplicación
4. Usa el middleware `SetLocale` que ya está registrado

---

**Documentación:** Para más información sobre Laravel localization, visita: https://laravel.com/docs/localization
