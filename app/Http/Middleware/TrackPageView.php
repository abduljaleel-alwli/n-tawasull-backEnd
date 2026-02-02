<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Analytics\AnalyticsService;

class TrackPageView
{
    public function handle($request, Closure $next)
    {
        // استثناء لوحة التحكم
        if ($request->is('admin/*')) {
            return $next($request);
        }

        // تتبع الصفحات العامة فقط
        if ($request->isMethod('GET') && ! $request->ajax()) {
            app(AnalyticsService::class)->track('page_view');
        }

        return $next($request);
    }
}
