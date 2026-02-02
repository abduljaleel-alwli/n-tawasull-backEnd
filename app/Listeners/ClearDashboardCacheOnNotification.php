<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCacheOnNotification
{
    public function handle(NotificationSent $event): void
    {
        /*
         |-------------------------------------------------------
         | Clear dashboard related caches
         |-------------------------------------------------------
         | نستخدم forget بدل flush (آمن + دقيق)
         */

        Cache::forget('dashboard:metrics:all');
        Cache::forget('dashboard:metrics:notifications_5');
    }
}
