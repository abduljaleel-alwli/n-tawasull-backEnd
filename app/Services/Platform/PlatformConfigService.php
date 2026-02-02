<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PlatformConfigService
{
    protected string $url = 'https://api.jsonsilo.com/public/a43f6133-3a5c-4897-8b0b-eade71d31844';

    public function get(): array
    {
        return Cache::remember('platform.config.json', now()->addHours(6), function () {
            $response = Http::timeout(5)->get($this->url);

            return $response->successful()
                ? $response->json()
                : [];
        });
    }
}
