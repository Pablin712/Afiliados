# 🛠️ Comandos y Herramientas Útiles

## Comandos Artisan Útiles

```bash
# Limpiar caché de configuración (importante después de cambios)
php artisan config:clear
php artisan config:cache

# Limpiar caché de traducción (Laravel cache translations)
php artisan cache:clear

# Crear seeders de datos multiidioma
php artisan make:seeder MultiLanguageSeeder
```

## Testing de Traducción

### Test de Traducción en Controladores

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_locale_can_be_changed()
    {
        $response = $this->get('/?locale=es');
        $this->assertEquals('es', session('locale'));
    }

    public function test_message_translation()
    {
        $this->actingAs($this->user())
             ->get('/?locale=en');
        
        $message = __('messages.welcome');
        $this->assertEquals('Welcome', $message);
    }

    public function test_spanish_translation()
    {
        $this->actingAs($this->user())
             ->get('/?locale=es');
        
        $message = __('messages.welcome');
        $this->assertEquals('Bienvenido', $message);
    }

    public function test_missing_translation_returns_key()
    {
        $message = __('messages.nonexistent_key');
        $this->assertEquals('messages.nonexistent_key', $message);
    }
}
```

## Verificación de Idiomas

### Script para Verificar Covertura de Traducción

```php
<?php

// FILE: scripts/check-translations.php
// Ejecución: php scripts/check-translations.php

$baseDir = 'resources/lang';
$locales = ['en', 'es'];

$translations = [];

// Load all translations
foreach ($locales as $locale) {
    $translations[$locale] = [];
    $langDir = "$baseDir/$locale";
    
    if (!is_dir($langDir)) {
        echo "❌ Missing directory: $langDir\n";
        continue;
    }

    foreach (scandir($langDir) as $file) {
        if (!str_ends_with($file, '.php')) continue;
        
        $key = str_replace('.php', '', $file);
        $data = require "$langDir/$file";
        $translations[$locale][$key] = flattenArray($data);
    }
}

// Compare translations
echo "\n📊 Translation Coverage Report\n";
echo str_repeat("=", 50) . "\n";

$enKeys = array_keys($translations['en']);
$esKeys = array_keys($translations['es']);

$missingInSpanish = array_diff($enKeys, $esKeys);
$missingInEnglish = array_diff($esKeys, $enKeys);

if (empty($missingInSpanish) && empty($missingInEnglish)) {
    echo "✅ All files have translations in both languages\n";
} else {
    if (!empty($missingInSpanish)) {
        echo "⚠️  Missing in Spanish: " . implode(', ', $missingInSpanish) . "\n";
    }
    if (!empty($missingInEnglish)) {
        echo "⚠️  Missing in English: " . implode(', ', $missingInEnglish) . "\n";
    }
}

function flattenArray($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        $fullKey = $prefix ? "$prefix.$key" : $key;
        if (is_array($value)) {
            $result = array_merge($result, flattenArray($value, $fullKey));
        } else {
            $result[$fullKey] = $value;
        }
    }
    return $result;
}

echo "\n✨ Report complete\n";
?>
```

Ejecución:
```bash
php scripts/check-translations.php
```

## Funciones Helper Personalizadas

### Agregar a `app/Helpers/TranslationHelper.php`

```php
/**
 * Get all translations for current locale as JSON
 */
public static function getAllAsJson()
{
    $locale = App::getLocale();
    $path = resource_path("lang/$locale");
    
    $translations = [];
    foreach (scandir($path) as $file) {
        if (str_ends_with($file, '.php')) {
            $key = str_replace('.php', '', $file);
            $translations[$key] = require "$path/$file";
        }
    }
    
    return json_encode($translations);
}

/**
 * Switch locale and return new instance
 */
public static function switchTo($locale)
{
    App::setLocale($locale);
    return new self();
}
```

## Debugging de Traducciones

### En Blade Template

```blade
<!-- DEBUG: Ver toda la estructura de traducción -->
@if (config('app.debug'))
    <div class="debug-translations" style="display:none;">
        <pre>{{ json_encode(trans('messages'), JSON_PRETTY_PRINT) }}</pre>
    </div>
@endif
```

### En Controlador

```php
// Debug para ver qué traducciones están disponibles
\Log::debug('Current locale', ['locale' => App::getLocale()]);
\Log::debug('Available translations', [
    'messages' => trans('messages'),
    'validation' => trans('validation'),
]);
```

## Migración de Textos Hardcoded a Traducción

### Paso 1: Identificar Hardcoded Strings

```bash
# Buscar strings hardcoded en Blade (ejemplo: without translation helper)
grep -r "{{.*['\"]" resources/views/ | grep -v "__(" | grep -v trans
```

### Paso 2: Crear Script de Reemplazo

```php
<?php
// FILE: scripts/migrate-translations.php

$pattern = '/"{([^}]+)}"/';
$files = glob('resources/views/**/*.blade.php', GLOB_RECURSIVE);

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Log potential translations
    if (preg_match_all($pattern, $content, $matches)) {
        echo "📄 $file:\n";
        foreach ($matches[1] as $match) {
            if (!preg_match('/__\(|trans\(/', $match)) {
                echo "  - Consider translating: $match\n";
            }
        }
    }
}
?>
```

## Monitorización de Idioma

### Middleware Personalizado para Logging

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogCurrentLocale
{
    public function handle(Request $request, $next)
    {
        Log::channel('locale')->info('Request in locale', [
            'locale' => app()->getLocale(),
            'path' => $request->path(),
            'user_id' => auth()->id(),
        ]);

        return $next($request);
    }
}
```

## Performance Tips

### Caché de Traducciones

```php
<?php

// En AppServiceProvider.php boot()

$this->publishes([
    resource_path('lang') => base_path('resources/lang'),
]);

// Habilitar caché de traducciones
if (!$this->app->environment('local')) {
    Lang::setFallback(config('app.fallback_locale'));
}
```

---

## 📋 Checklist de Implementación

- [ ] Instalación completada
- [ ] Middleware registrado
- [ ] Archivos de traducción creados
- [ ] Componente selector de idioma funciona
- [ ] Rutas de cambio de idioma probadas
- [ ] Helper TranslationHelper disponible
- [ ] Ejemplos de uso revisados
- [ ] Documentación actualizada
- [ ] Textos hardcoded reemplazados
- [ ] Tests de traducción escritos
- [ ] Caché de configuración limpio

---

## 🔗 Útiles

| Comando | Descripción |
|---------|------------|
| `php artisan config:clear` | Limpiar caché de config |
| `php artisan cache:clear` | Limpiar todas las cachés |
| `php artisan tinker` | REPL para probar traducción |
| `grep -r "__(" resources/` | Buscar traducción en proyecto |
| `php -S localhost:8000` | Servidor PHP local |

---

## 📞 Soporte

Si tienes problemas:

1. **Verifica que el archivo existe:**
   ```bash
   ls -la resources/lang/en/
   ls -la resources/lang/es/
   ```

2. **Limpia la caché:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Verifica el locale:**
   - Session: `session('locale')`
   - Config: `config('app.locale')`
   - Runtime: `App::getLocale()`

