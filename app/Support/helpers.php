<?php

use App\Services\Settings\SettingsService;

if (! function_exists('settings')) {
    function settings(string $key = null, mixed $default = null): mixed
    {
        /** @var SettingsService $svc */
        $svc = app(SettingsService::class);

        if ($key === null) {
            return $svc->all();
        }

        return $svc->get($key, $default);
    }
}
