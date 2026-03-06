<?php

namespace App\Providers;

use App\Models\Action;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerAuthAuditEvents();
        $this->registerModelAuditEvents();
    }

    protected function registerAuthAuditEvents(): void
    {
        $audit = app(AuditLogService::class);

        Event::listen(Login::class, function (Login $event) use ($audit): void {
            $audit->logAuthEvent('login', [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ], $event->user?->getAuthIdentifier());
        });

        Event::listen(Logout::class, function (Logout $event) use ($audit): void {
            $audit->logAuthEvent('logout', [
                'guard' => $event->guard,
            ], $event->user?->getAuthIdentifier());
        });

        Event::listen(Failed::class, function (Failed $event) use ($audit): void {
            $audit->logAuthEvent('login_failed', [
                'guard' => $event->guard,
                'email' => $event->credentials['email'] ?? null,
            ], $event->user?->getAuthIdentifier());
        });
    }

    protected function registerModelAuditEvents(): void
    {
        $audit = app(AuditLogService::class);

        Model::created(function (Model $model) use ($audit): void {
            if ($model instanceof Action) {
                return;
            }

            $audit->logModelEvent($model, 'create', null, $model->getAttributes());
        });

        Model::updated(function (Model $model) use ($audit): void {
            if ($model instanceof Action) {
                return;
            }

            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $oldValues = [];
            foreach (array_keys($changes) as $field) {
                $oldValues[$field] = $model->getOriginal($field);
            }

            $audit->logModelEvent($model, 'update', $oldValues, $changes);
        });

        Model::deleted(function (Model $model) use ($audit): void {
            if ($model instanceof Action) {
                return;
            }

            $audit->logModelEvent($model, 'delete', $model->getOriginal(), null);
        });
    }
}
