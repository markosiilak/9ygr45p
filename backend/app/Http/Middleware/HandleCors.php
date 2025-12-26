<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Allowed origins for CORS.
     * In production, this should be configured via environment variable.
     */
    private function getAllowedOrigins(): array
    {
        $origins = env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:8000,http://127.0.0.1:3000');
        return array_map('trim', explode(',', $origins));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight requests immediately
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        $origin = $request->header('Origin');
        $allowedOrigins = $this->getAllowedOrigins();

        // Only set CORS headers if origin is allowed
        if ($origin && (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins))) {
            // Use specific origin instead of * when credentials are needed
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
        }

        return $response;
    }
}
