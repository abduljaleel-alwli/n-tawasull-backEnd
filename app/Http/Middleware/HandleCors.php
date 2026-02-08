<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // قراءة إعدادات CORS من config/cors.php
        $corsConfig = config('cors');

        $headers = [
            'Access-Control-Allow-Origin' => $corsConfig['allowed_origins'] ?? '*',
            'Access-Control-Allow-Methods' => implode(',', $corsConfig['allowed_methods'] ?? ['*']),
            'Access-Control-Allow-Headers' => implode(',', $corsConfig['allowed_headers'] ?? ['*']),
        ];

        // For OPTIONS requests, return a 200 response with the CORS headers
        if ($request->getMethod() == "OPTIONS") {
            return response()->json([], 200, $headers);
        }

        $response = $next($request);

        // إضافة CORS headers إلى كل استجابة
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
