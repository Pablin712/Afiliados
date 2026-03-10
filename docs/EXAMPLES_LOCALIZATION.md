# 📋 Ejemplos Prácticos de Uso de Traducción

Este archivo contiene ejemplos reales de cómo integrar traducción en diferentes partes del proyecto Afiliados.

## 1️⃣ En Vistas Blade

### Selector de Idioma en Header/Navbar

```blade
<!-- resources/views/layouts/app.blade.php -->

<header>
    <nav>
        <div class="flex justify-between items-center">
            <h1>{{ __('messages.welcome') }}</h1>
            
            <!-- Language Switcher Component -->
            <x-language-switcher />
        </div>
    </nav>
</header>
```

### Tabla de Afiliados

```blade
<!-- resources/views/affiliates/index.blade.php -->

<div class="container">
    <h2>{{ __('messages.affiliates') }}</h2>
    
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.email') }}</th>
                <th>{{ __('messages.sponsor') }}</th>
                <th>{{ __('messages.commission') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($affiliates as $affiliate)
                <tr>
                    <td>{{ $affiliate->name }}</td>
                    <td>{{ $affiliate->email }}</td>
                    <td>{{ $affiliate->sponsor->name }}</td>
                    <td>{{ $affiliate->commission_balance }}</td>
                    <td>
                        <a href="{{ route('affiliate.edit', $affiliate) }}" class="btn">
                            {{ __('messages.edit') }}
                        </a>
                        <button onclick="delete({{ $affiliate->id }})" class="btn btn-danger">
                            {{ __('messages.delete') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-gray-500">
                        {{ __('messages.no_records_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
```

### Formulario de Registro

```blade
<!-- resources/views/auth/register.blade.php -->

<form method="POST" action="{{ route('register') }}">
    @csrf
    
    <!-- Name -->
    <div>
        <label for="name">{{ __('validation.attributes.name') }}</label>
        <input 
            id="name" 
            class="form-control @error('name') is-invalid @enderror"
            type="text" 
            name="name" 
            value="{{ old('name') }}"
            placeholder="{{ __('validation.attributes.name') }}"
            required
        >
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Email -->
    <div>
        <label for="email">{{ __('validation.attributes.email') }}</label>
        <input 
            id="email" 
            class="form-control @error('email') is-invalid @enderror"
            type="email" 
            name="email" 
            value="{{ old('email') }}"
            placeholder="{{ __('validation.attributes.email') }}"
            required
        >
        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <!-- Sponsor -->
    <div>
        <label for="sponsor">{{ __('messages.sponsor') }}</label>
        <select 
            id="sponsor" 
            class="form-control @error('sponsor_id') is-invalid @enderror"
            name="sponsor_id"
        >
            <option value="">{{ __('messages.select_sponsor') }}</option>
            @foreach ($sponsors as $sponsor)
                <option value="{{ $sponsor->id }}">{{ $sponsor->name }}</option>
            @endforeach
        </select>
        @error('sponsor_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit">{{ __('messages.register') }}</button>
</form>
```

### Alertas y Mensajes

```blade
<!-- resources/views/components/alerts.blade.php -->

@if ($errors->any())
    <div class="alert alert-danger">
        <h4>{{ __('messages.validation_errors') }}</h4>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
```

## 2️⃣ En Controladores

### AffiliateController Example

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreAffiliateRequest;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    public function store(StoreAffiliateRequest $request)
    {
        try {
            $affiliate = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'sponsor_id' => $request->sponsor_id,
            ]);

            // Success message
            $message = __('messages.successful_registration', [
                'name' => $affiliate->name
            ]);

            return redirect()->route('dashboard')
                           ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('messages.error_creating_affiliate'));
        }
    }

    public function destroy(User $affiliate)
    {
        try {
            $affiliate->delete();
            
            return back()->with('success', __('messages.affiliate_deleted_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.error_deleting_affiliate'));
        }
    }
}
```

### Cambiar Idioma Dinámicamente

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function change($locale)
    {
        $availableLocales = ['en', 'es'];
        
        if (!in_array($locale, $availableLocales)) {
            return redirect()->back()
                ->with('error', __('messages.invalid_locale'));
        }

        // Update session
        session(['locale' => $locale]);
        
        // Set application locale
        App::setLocale($locale);

        return redirect()->back()
            ->with('success', __('messages.language_changed'));
    }
}
```

## 3️⃣ En FormRequest (Validación)

### StoreAffiliateRequest

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'sponsor_id' => 'nullable|exists:users,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Mensajes traducidos de validación
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', [
                'attribute' => __('validation.attributes.name')
            ]),
            'name.string' => __('validation.string', [
                'attribute' => __('validation.attributes.name')
            ]),
            'email.required' => __('validation.required', [
                'attribute' => __('validation.attributes.email')
            ]),
            'email.email' => __('validation.email', [
                'attribute' => __('validation.attributes.email')
            ]),
            'email.unique' => __('validation.unique', [
                'attribute' => __('validation.attributes.email')
            ]),
            'sponsor_id.exists' => __('validation.exists', [
                'attribute' => __('validation.attributes.sponsor_id')
            ]),
        ];
    }

    /**
     * Nombres traducidos de atributos
     */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'sponsor_id' => __('validation.attributes.sponsor_id'),
            'commission_rate' => __('validation.attributes.commission_rate'),
        ];
    }
}
```

## 4️⃣ En Modelos

### User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'sponsor_id',
        'commission_balance',
    ];

    /**
     * Get audit log message
     */
    public function getAuditMessage(): string
    {
        return __('messages.user_created', [
            'name' => $this->name,
            'email' => $this->email
        ]);
    }

    /**
     * Get membership status label
     */
    public function getMembershipStatusLabel(): string
    {
        if ($this->membership?->status === 'active') {
            return __('messages.active_membership');
        }
        
        if ($this->membership?->status === 'expired') {
            return __('messages.expired_membership');
        }

        return __('messages.no_membership');
    }
}
```

## 5️⃣ En Servicios

### AuditLogService

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Helpers\TranslationHelper;

class AuditLogService
{
    public function log($action, $model, $changes = null)
    {
        $locale = TranslationHelper::getCurrentLocale();

        Log::channel('audit')->info(__('messages.action_logged', [
            'action' => $action,
            'model' => class_basename($model),
            'locale' => $locale,
        ]), [
            'model_id' => $model->id,
            'model_type' => get_class($model),
            'changes' => $changes,
            'user_id' => auth()->id(),
            'locale' => $locale,
        ]);
    }
}
```

## 6️⃣ En Notificaciones

### Email Notification

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AffiliateRegistered extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('messages.welcome_to_platform'))
            ->greeting(__('messages.hello', ['name' => $notifiable->name]))
            ->line(__('messages.registration_completed'))
            ->action(__('messages.view_dashboard'), url('/dashboard'))
            ->line(__('messages.thank_you'));
    }
}
```

## 7️⃣ Migration para Auditoría Multiidioma

Si necesitas guardar registros de auditoría en múltiples idiomas:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('locale'); // Store locale used when action occurred
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

---

## ✅ Checklist para Implementar Traducciones

- [ ] Reemplazar todos los textos hardcoded con `__('key')`
- [ ] Agregar componente `<x-language-switcher />` en el navbar
- [ ] Usar traducción en validaciones con `messages()` y `attributes()`
- [ ] Probar ambos idiomas (en, es)
- [ ] Verificar que los mensajes de éxito/error se traduzcan
- [ ] Usar `TranslationHelper` en servicios cuando sea necesario
- [ ] Actualizar archivo de requisitos según cambios

---

## 🔗 Rutas Relevantes

| Ruta | Método | Descripción |
|------|--------|------------|
| `/?locale=en` | GET | Cambiar a Inglés |
| `/?locale=es` | GET | Cambiar a Español |
| `/locale/en` | GET | Ruta explícita para Inglés |
| `/locale/es` | GET | Ruta explícita para Español |

