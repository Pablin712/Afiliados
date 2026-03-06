<?php

namespace App\Services;

use App\Models\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuditLogService
{
    /**
     * Avoid recursive write loops while the action itself is inserted.
     */
    protected static bool $isWriting = false;

    protected static ?bool $actionsTableExists = null;

    public function logHttpRequest(Request $request, SymfonyResponse $response): void
    {
        if (! $this->canWriteLog()) {
            return;
        }

        $method = strtoupper($request->method());
        $routeName = $request->route()?->getName();
        $routeUri = $request->route()?->uri();

        $this->store([
            'user_id' => Auth::id(),
            'module' => $this->resolveModule($routeName, $routeUri),
            'action' => $this->resolveActionName($method, $routeName, $routeUri),
            'method' => $method,
            'route' => $routeName,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $this->truncate((string) $request->userAgent(), 500),
            'payload' => $this->sanitizePayload($request->except($this->sensitiveKeys())),
            'old_values' => null,
            'new_values' => [
                'response_status' => $response->getStatusCode(),
                'response_class' => class_basename($response),
            ],
            'created_at' => now(),
        ]);
    }

    public function logAuthEvent(string $eventName, array $payload = [], ?int $userId = null): void
    {
        if (! $this->canWriteLog()) {
            return;
        }

        $request = request();

        $this->store([
            'user_id' => $userId,
            'module' => 'auth',
            'action' => $eventName,
            'method' => strtoupper($request?->method() ?? 'CLI'),
            'route' => $request?->route()?->getName(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $this->truncate((string) ($request?->userAgent() ?? ''), 500),
            'payload' => $this->sanitizePayload($payload),
            'old_values' => null,
            'new_values' => null,
            'created_at' => now(),
        ]);
    }

    public function logModelEvent(Model $model, string $eventName, ?array $oldValues = null, ?array $newValues = null): void
    {
        if (! $this->canWriteLog()) {
            return;
        }

        if ($model instanceof Action) {
            return;
        }

        $request = request();

        $this->store([
            'user_id' => Auth::id(),
            'module' => str($model->getTable())->lower()->toString(),
            'action' => $eventName,
            'method' => strtoupper($request?->method() ?? 'CLI'),
            'route' => $request?->route()?->getName(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $this->truncate((string) ($request?->userAgent() ?? ''), 500),
            'payload' => [
                'model' => $model::class,
                'model_id' => $model->getKey(),
            ],
            'old_values' => $this->sanitizePayload($oldValues),
            'new_values' => $this->sanitizePayload($newValues),
            'created_at' => now(),
        ]);
    }

    protected function store(array $data): void
    {
        if (self::$isWriting) {
            return;
        }

        self::$isWriting = true;

        try {
            Action::query()->create($data);
        } catch (\Throwable $exception) {
            report($exception);
        } finally {
            self::$isWriting = false;
        }
    }

    protected function canWriteLog(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if (self::$actionsTableExists === null) {
            self::$actionsTableExists = Schema::hasTable('actions');
        }

        return self::$actionsTableExists;
    }

    protected function resolveModule(?string $routeName, ?string $routeUri): string
    {
        if ($routeName) {
            return str($routeName)->before('.')->snake()->toString();
        }

        if ($routeUri) {
            return str($routeUri)->before('/')->snake()->toString();
        }

        return 'system';
    }

    protected function resolveActionName(string $method, ?string $routeName, ?string $routeUri): string
    {
        $suffix = $routeName
            ? str($routeName)->afterLast('.')->snake()->toString()
            : str($routeUri ?? 'unknown')->replace('/', '_')->snake()->toString();

        return strtolower($method).'_'.$suffix;
    }

    protected function sanitizePayload(mixed $payload): mixed
    {
        if ($payload === null) {
            return null;
        }

        if (is_array($payload)) {
            $result = [];

            foreach ($payload as $key => $value) {
                $normalizedKey = is_string($key) ? strtolower($key) : (string) $key;

                if ($this->isSensitiveKey($normalizedKey)) {
                    continue;
                }

                $result[$key] = $this->sanitizePayload($value);
            }

            return $result;
        }

        if (is_string($payload)) {
            return $this->truncate($payload, 2000);
        }

        return $payload;
    }

    protected function isSensitiveKey(string $key): bool
    {
        $needles = ['password', 'token', 'secret', 'remember', 'session', 'cookie'];

        foreach ($needles as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function sensitiveKeys(): array
    {
        return [
            '_token',
            'password',
            'password_confirmation',
            'current_password',
            'remember_token',
        ];
    }

    protected function truncate(string $value, int $max): string
    {
        return mb_strlen($value) > $max
            ? mb_substr($value, 0, $max - 3).'...'
            : $value;
    }
}
