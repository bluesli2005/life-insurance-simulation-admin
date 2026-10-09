<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;

class ApiCors
{
    public function handle($request, Closure $next)
    {
        // This service exposes JSON only, including unknown routes and errors.
        $request->headers->set('Accept', 'application/json');
        $origin = $request->header('Origin');
        $allowed = config('cors.allowed_origins');
        if ($origin && ! in_array($origin, $allowed, true)) {
            return response()->json(['message' => 'この接続元は許可されていません。'], 403);
        }

        if ($request->isMethod('OPTIONS')) {
            $method = $request->header('Access-Control-Request-Method');
            $headers = array_filter(array_map('trim', explode(',', strtolower($request->header('Access-Control-Request-Headers', '')))));
            $valid = $origin && in_array($method, config('cors.allowed_methods'), true)
                && ! array_diff($headers, config('cors.allowed_headers'));
            $response = $valid ? response()->noContent() : response()->json(['message' => '無効なプリフライト要求です。'], 403);
        } else {
            try {
                $response = $next($request);
            } catch (Exception $exception) {
                $handler = app(ExceptionHandler::class);
                $handler->report($exception);
                $response = $handler->render($request, $exception);
            }
        }

        $response->headers->set('Cache-Control', 'no-store, private');
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', config('cors.allowed_methods')));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', config('cors.allowed_headers')));
            $response->setVary('Origin', false);
        }

        return $response;
    }
}
