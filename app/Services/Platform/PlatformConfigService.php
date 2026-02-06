<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;

class PlatformConfigService
{
    protected string $url = 'https://api.jsonsilo.com/public/a43f6133-3a5c-4897-8b0b-eade71d31844';

    public function get(): array
    {
        return Cache::remember('platform.config.json', now()->addHours(6), function () {
            try {
                $response = Http::timeout(10)->get($this->url);

                // إذا كانت الاستجابة ناجحة
                if ($response->successful()) {
                    return $response->json();
                }

                // في حالة الفشل، يمكن إرجاع بيانات افتراضية
                return [];

            } catch (ConnectionException $e) {
                // يمكنك تسجيل الخطأ في السجل في حال حدوث مشكلة في الاتصال
                \Log::error('فشل الاتصال مع API: ' . $e->getMessage());

                // في حالة حدوث خطأ، يمكن إرجاع بيانات افتراضية لتجنب تعطل الموقع
                return [];
            } catch (\Exception $e) {
                // في حالة أي أخطاء أخرى
                \Log::error('حدث خطأ غير متوقع: ' . $e->getMessage());

                // يمكن إرجاع بيانات افتراضية
                return [];
            }
        });
    }
}

