<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lightweight request-level audit trail. Model-level create/update/delete
 * auditing is handled per-model by AuditObserver; this middleware captures
 * who hit which URL, for forensic trace of "who was in the system when".
 */
class LogAuditTrail
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && $request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch') || $request->isMethod('delete')) {
            if ($request->user()) {
                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => strtolower($request->method()).'_request',
                    'ip_address' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
            }
        }

        return $response;
    }
}
