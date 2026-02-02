<?php

namespace App\Providers;

use App\Services\Platform\PlatformConfigService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('settings', settings());
        });

        View::composer('components.layouts.app.admin', function ($view) {
            $platform = app(PlatformConfigService::class)->get();
            $view->with('platformConfig', $platform);
        });
    }
}
