<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function __construct(protected AuditLogService $auditLogService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->auditLogService->logHttpRequest($request, $response);

        return $response;
    }
}
