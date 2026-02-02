<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()
                ->get(['key', 'value', 'type'])
                ->mapWithKeys(function ($row) {
                    return [$row->key => $this->cast($row->value, $row->type)];
                })
                ->toArray();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->stringify($value, $type),
                'type'  => $type,
                'group' => $group,
            ]
        );

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function cast(?string $value, string $type): mixed
    {
        if ($value === null) return null;

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number'  => is_numeric($value) ? $value + 0 : $value,
            'json'    => json_decode($value, true) ?? [],
            default   => $value,
        };
    }

    private function stringify(mixed $value, string $type): ?string
    {
        if ($value === null) return null;

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json'    => json_encode($value, JSON_UNESCAPED_UNICODE),
            default   => (string) $value,
        };
    }
}
