<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class DashboardMetrics
{

    /* =========================
        CACHING KEYS
    ========================= */
    protected function cacheKey(string $key): string
    {
        return "dashboard:metrics:{$key}";
    }

    /* =========================
   DATE HELPERS (SPARKLINES)
========================= */

    protected function dateKeysLastDays(int $days): array
    {
        // مثال: ["2026-01-03", "2026-01-02", ...] لكن نرجعها تصاعديًا
        return collect(range($days - 1, 0))
            ->map(fn($i) => now()->subDays($i)->toDateString())
            ->values()
            ->all();
    }

    protected function fillDailySeries(array $dateKeys, $rows): array
    {
        // $rows collection فيها: date, total
        $map = collect($rows)->mapWithKeys(function ($row) {
            $date = is_array($row) ? ($row['date'] ?? null) : ($row->date ?? null);
            $total = is_array($row) ? ($row['total'] ?? 0) : ($row->total ?? 0);
            return [$date => (int) $total];
        });

        return collect($dateKeys)
            ->map(fn($d) => (int) ($map->get($d, 0)))
            ->all();
    }


    /* =========================
       BASIC COUNTS
    ========================= */

    public function totalUsers(): int
    {
        return Cache::remember(
            $this->cacheKey('users'),
            now()->addMinutes(5),
            fn() => User::count()
        );
    }

    public function totalProducts(): int
    {
        return Cache::remember(
            $this->cacheKey('products'),
            now()->addMinutes(5),
            fn() => Product::count()
        );
    }

    public function totalVisits(): int
    {
        return Cache::remember(
            $this->cacheKey('visits'),
            now()->addMinutes(5),
            fn() => DB::table('analytics_events')
                ->where('event', 'page_view')
                ->count()
        );
    }

    public function totalContactMessages(): int
    {
        return Cache::remember(
            $this->cacheKey('contacts'),
            now()->addMinutes(5),
            fn() => DB::table('analytics_events')
                ->where('event', 'contact_submitted')
                ->count()
        );

    }

    protected function sparklineFromCollection($collection): array
    {
        return $collection->pluck('total')->map(fn($v) => (int) $v)->toArray();
    }

    public function visitsSparkline(): array
    {
        return Cache::remember(
            $this->cacheKey('visits_sparkline_7'),
            now()->addMinutes(5),
            function () {
                $keys = $this->dateKeysLastDays(7);

                $rows = DB::table('analytics_events')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                    ->where('event', 'page_view')
                    ->where('created_at', '>=', now()->subDays(7)->startOfDay())
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                return $this->fillDailySeries($keys, $rows);
            }
        );
    }

        public function visitsTrend(): int
    {
        $today = $this->todayVisits();
        $yesterday = $this->yesterdayVisits();

        if ($yesterday === 0) {
            return $today > 0 ? 100 : 0;
        }

        return (int) round((($today - $yesterday) / $yesterday) * 100);
    }



    public function contactsSparkline(): array
    {
        return Cache::remember(
            $this->cacheKey('contacts_sparkline_7'),
            now()->addMinutes(5),
            function () {
                $keys = $this->dateKeysLastDays(7);

                $rows = DB::table('analytics_events')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                    ->where('event', 'contact_submitted')
                    ->where('created_at', '>=', now()->subDays(7)->startOfDay())
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                return $this->fillDailySeries($keys, $rows);
            }
        );
    }


    public function conversionTrend(): int
    {
        $startThisWeek = now()->subWeek();
        $startLastWeek = now()->subWeeks(2);
        $endLastWeek = now()->subWeek();

        $thisWeekVisits = DB::table('analytics_events')
            ->where('event', 'page_view')
            ->where('created_at', '>=', $startThisWeek)
            ->count();

        $thisWeekContacts = DB::table('analytics_events')
            ->where('event', 'contact_submitted')
            ->where('created_at', '>=', $startThisWeek)
            ->count();

        $lastWeekVisits = DB::table('analytics_events')
            ->where('event', 'page_view')
            ->whereBetween('created_at', [$startLastWeek, $endLastWeek])
            ->count();

        $lastWeekContacts = DB::table('analytics_events')
            ->where('event', 'contact_submitted')
            ->whereBetween('created_at', [$startLastWeek, $endLastWeek])
            ->count();

        $thisRate = $thisWeekVisits > 0 ? ($thisWeekContacts / $thisWeekVisits) * 100 : 0;
        $lastRate = $lastWeekVisits > 0 ? ($lastWeekContacts / $lastWeekVisits) * 100 : 0;

        if ($lastRate == 0.0) {
            // لو الأسبوع الماضي 0%، والأسبوع الحالي صار فيه أي تحويل -> اعتبرها +100 (اختيار UX)
            return $thisRate > 0 ? 100 : 0;
        }

        return (int) round((($thisRate - $lastRate) / $lastRate) * 100);
    }


    /* =========================
       CLICK EVENTS
    ========================= */

    public function whatsappClicks(): int
    {
        return Cache::remember(
            $this->cacheKey('whatsapp_clicks'),
            now()->addMinutes(5),
            fn() => DB::table('analytics_events')
                ->where('event', 'whatsapp_click')
                ->count()
        );
    }

    public function socialClicks(): int
    {
        return Cache::remember(
            $this->cacheKey('social_clicks'),
            now()->addMinutes(5),
            fn() => DB::table('analytics_events')
                ->where('event', 'social_click')
                ->count()
        );

    }

    /* =========================
       TOP PAGES
    ========================= */

    public function topPages(int $limit = 5)
    {
        return Cache::remember(
            $this->cacheKey("top_pages_{$limit}"),
            now()->addMinutes(10),
            fn() => DB::table('analytics_events')
                ->select('page', DB::raw('COUNT(*) as visits'))
                ->where('event', 'page_view')
                ->groupBy('page')
                ->orderByDesc('visits')
                ->limit($limit)
                ->get()
        );
    }

    /* =========================
       CONVERSION RATE
       Visitors → Contact
    ========================= */

public function conversionRate(): float
{
    return Cache::remember(
        $this->cacheKey('conversion_rate_this_week'),
        now()->addMinutes(5),
        function () {
            $startOfWeek = now()->subWeek();

            $visits = DB::table('analytics_events')
                ->where('event', 'page_view')
                ->where('created_at', '>=', $startOfWeek)
                ->count();

            $contacts = DB::table('analytics_events')
                ->where('event', 'contact_submitted')
                ->where('created_at', '>=', $startOfWeek)
                ->count();

            return $visits === 0
                ? 0
                : round(($contacts / $visits) * 100, 2);
        }
    );
}


    public function funnel(): array
    {
        return Cache::remember(
            $this->cacheKey('funnel'),
            now()->addMinutes(5),
            fn() => [
                'visits' => $this->totalVisits(),
                'contacts' => $this->totalContactMessages(),
                'whatsapp_clicks' => $this->whatsappClicks(),
            ]
        );
    }

    public function topSources(int $limit = 5)
    {
        return Cache::remember(
            $this->cacheKey("top_sources_{$limit}"),
            now()->addMinutes(10),
            fn() => DB::table('analytics_events')
                ->selectRaw('COALESCE(NULLIF(source, ""), "unknown") as source, COUNT(*) as total')
                ->where('event', 'contact_submitted')
                ->groupBy('source')
                ->orderByDesc('total')
                ->limit($limit)
                ->get()
        );
    }


    public function isDashboardHealthy(): bool
    {
        return $this->todayVisits() > 0 || $this->todayContacts() > 0;
    }

    // public function lastAdminLogin()
// {
//     return Cache::remember(
//         $this->cacheKey('last_admin_login'),
//         now()->addMinutes(5),
//         fn() => User::role('admin')
//             ->orderByDesc('last_login_at')
//             ->first(['name', 'last_login_at'])
//     );
// }


    /* =========================
       DAILY CHART
    ========================= */

    public function dailyVisits(int $days = 7)
    {
        return Cache::remember(
            $this->cacheKey("daily_visits_{$days}"),
            now()->addMinutes(5),
            fn() => DB::table('analytics_events')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->where('event', 'page_view')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        );
    }

    public function todayVisits(): int
    {
        return Cache::remember(
            $this->cacheKey('visits_today'),
            now()->addMinutes(2),
            fn() => DB::table('analytics_events')
                ->where('event', 'page_view')
                ->whereDate('created_at', today())
                ->count()
        );
    }

    public function yesterdayVisits(): int
    {
        return Cache::remember(
            $this->cacheKey('visits_yesterday'),
            now()->addMinutes(2),
            fn() => DB::table('analytics_events')
                ->where('event', 'page_view')
                ->whereDate('created_at', today()->subDay())
                ->count()
        );
    }


    public function todayContacts(): int
    {
        return Cache::remember(
            $this->cacheKey('contacts_today'),
            now()->addMinutes(2),
            fn() => DB::table('analytics_events')
                ->where('event', 'contact_submitted')
                ->whereDate('created_at', today())
                ->count()
        );
    }

    // public function singlePageVisits(): int
// {
//     return Cache::remember(
//         $this->cacheKey('single_page_visits'),
//         now()->addMinutes(5),
//         fn() => DB::table('analytics_events')
//             ->where('event', 'page_view')
//             ->whereNotNull('session_id')
//             ->havingRaw('COUNT(*) = 1')
//             ->count()
//     );
// }


    /* =========================
       MONTHLY CHART
    ========================= */

    public function monthlyVisits(int $months = 6)
    {
        return Cache::remember(
            $this->cacheKey("monthly_visits_{$months}"),
            now()->addMinutes(10),
            fn() => DB::table('analytics_events')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
                ->where('event', 'page_view')
                ->where('created_at', '>=', now()->subMonths($months))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
        );
    }


    /* =========================
       ACTIVITY LOGS
    ========================= */

    public function latestActivities(int $limit = 10)
    {
        return DB::table('analytics_events')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /* =========================
         NOTIFICATIONS
    ========================= */
    public function latestNotifications(int $limit = 5)
    {
        return Cache::remember(
            $this->cacheKey("notifications_{$limit}"),
            now()->addMinutes(1),
            fn() => DatabaseNotification::latest()
                ->limit($limit)
                ->get()
        );
    }


    /* =========================
       ALL METRICS (ONE CALL)
    ========================= */

    public function all(): array
    {
        return Cache::remember(
            $this->cacheKey('all'),
            now()->addMinutes(3),
            fn() => [
                // Core
                'users' => $this->totalUsers(),
                'products' => $this->totalProducts(),
                'visits' => $this->totalVisits(),
                'contacts' => $this->totalContactMessages(),

                // Today / Trends
                'today_visits' => $this->todayVisits(),
                'yesterday_visits' => $this->yesterdayVisits(),
                'today_contacts' => $this->todayContacts(),
                'visits_trend' => $this->visitsTrend(),
                'conversion_trend' => $this->conversionTrend(),

                // Engagement
                // 'single_page_visits' => $this->singlePageVisits(),
                'conversion_rate' => $this->conversionRate(),
                'funnel' => $this->funnel(),

                // Clicks
                'whatsapp_clicks' => $this->whatsappClicks(),
                'social_clicks' => $this->socialClicks(),

                // Sources / Pages
                'top_pages' => $this->topPages(),
                'top_sources' => $this->topSources(),

                // Charts
                'daily_visits' => $this->dailyVisits(),
                'monthly_visits' => $this->monthlyVisits(),
                'visits_sparkline' => $this->visitsSparkline(),
                'contacts_sparkline' => $this->contactsSparkline(),

                // System
                'activities' => $this->latestActivities(),
                'notifications' => $this->latestNotifications(),
                'dashboard_health' => $this->isDashboardHealthy(),
                // 'last_admin_login' => $this->lastAdminLogin(),
            ]
        );
    }


}