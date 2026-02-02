<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\Dashboard\DashboardMetrics;

new class extends Component {
    use AuthorizesRequests;

    public array $metrics = [];

    public string $chartRange = 'daily'; // daily | monthly
    public array $chart = [
        'categories' => [],
        'series' => [],
    ];

    public function mount(DashboardMetrics $dashboard): void
    {
        $this->authorize('access-dashboard');

        $this->metrics = $dashboard->all();

        $this->buildChart();
    }

    // Set chart range and rebuild chart
    public function setChartRange(string $range): void
    {
        $this->chartRange = in_array($range, ['daily', 'monthly'], true) ? $range : 'daily';

        $this->buildChart();

        // send updated data to JS chart
        $this->dispatch('dashboard-chart-updated', chart: $this->chart);
    }

    // Build chart data based on selected range
    protected function buildChart(): void
    {
        if ($this->chartRange === 'monthly') {
            $rows = collect($this->metrics['monthly_visits'] ?? []);
            $this->chart = [
                'categories' => $rows->pluck('month')->values()->all(),
                'series' => [
                    [
                        'name' => __('Visits'),
                        'data' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
                    ],
                ],
            ];
            return;
        }

        // daily
        $rows = collect($this->metrics['daily_visits'] ?? []);
        $this->chart = [
            'categories' => $rows->pluck('date')->values()->all(),
            'series' => [
                [
                    'name' => __('Visits'),
                    'data' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
                ],
            ],
        ];
    }

    // Refresh dashboard data periodically
    public function refreshDashboard(DashboardMetrics $dashboard): void
    {
        $this->metrics = $dashboard->all();

        $this->buildChart();

        // تحديث ApexChart في المتصفح
        $this->dispatch('dashboard-chart-updated', chart: $this->chart);
    }
};
?>



<div class="space-y-8" wire:poll.30s="refreshDashboard">

    {{-- Stat Cards --}}
    <div class="
    grid
    grid-cols-1
    sm:grid-cols-2
    md:grid-cols-3
    xl:grid-cols-4
    gap-4">

        <x-dashboard.card :title="__('Visitors')" :value="$metrics['visits']" icon="users" color="sky" :sparkline="$metrics['visits_sparkline'] ?? []"
            :trend="$metrics['visits_trend']" />

        <x-dashboard.card :title="__('Today Visits')" :value="$metrics['today_visits']" icon="calendar-days" color="sky" />

        <x-dashboard.card :title="__('Contacts')" :value="$metrics['contacts']" icon="envelope" color="emerald" :sparkline="$metrics['contacts_sparkline'] ?? []"
            href="{{ route('admin.contact-messages') }}" />

        <x-dashboard.card :title="__('Today Contacts')" :value="$metrics['today_contacts']" icon="inbox" color="emerald" />

        @role('super-admin')
            <x-dashboard.card :title="__('Users')" :value="$metrics['users']" icon="shield-check" color="violet"
                href="{{ route('admin.users') }}" />

            <x-dashboard.card :title="__('System Status')" :value="$metrics['dashboard_health'] ? __('Active') : __('Idle')" icon="signal"
                color="{{ $metrics['dashboard_health'] ? 'emerald' : 'rose' }}" />
        @endrole

        <x-dashboard.card :title="__('Products')" :value="$metrics['products']" icon="cube" color="amber"
            href="{{ route('admin.products') }}" />

        <x-dashboard.card :title="__('WhatsApp Clicks')" :value="$metrics['whatsapp_clicks']" icon="chat-bubble-left-right" color="green" />

        <x-dashboard.card :title="__('Social Clicks')" :value="$metrics['social_clicks']" icon="share" color="indigo" />

        <x-dashboard.card :title="__('Conversion Rate')" :value="$metrics['conversion_rate'] . '%'" icon="arrow-trending-up" color="rose"
            :trend="$metrics['conversion_trend'] ?? 0" />

    </div>


    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    {{ __('Visits') }}
                </h2>
                <p class="text-xs text-slate-500">
                    {{ $chartRange === 'daily' ? __('Last 7 days') : __('Last 6 months') }}
                </p>
            </div>

            <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <button wire:click="setChartRange('daily')"
                    class="px-3 py-1.5 text-xs font-medium transition
                       {{ $chartRange === 'daily' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-transparent text-slate-600 dark:text-slate-300' }}">
                    {{ __('Daily') }}
                </button>

                <button wire:click="setChartRange('monthly')"
                    class="px-3 py-1.5 text-xs font-medium transition
                       {{ $chartRange === 'monthly' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-transparent text-slate-600 dark:text-slate-300' }}">
                    {{ __('Monthly') }}
                </button>
            </div>
        </div>

        {{-- Apex chart --}}
        <div wire:ignore id="visitsChart" class="h-[320px]"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top Pages --}}
        <div
            class="rounded-2xl border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/90 p-5">

            {{-- Header --}}
            <div class="flex items-center gap-2 mb-5">
                <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center">
                    <flux:icon name="chart-bar" variant="solid" size="18" />
                </div>

                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                    {{ __('Top Pages') }}
                </h2>
            </div>

            {{-- List --}}
            <ul class="space-y-2 text-sm">

                @if (!count($metrics['top_pages'] ?? []))
                    @for ($i = 0; $i < 5; $i++)
                        <li class="flex items-center justify-between gap-3">
                            <x-skeleton.line w="w-3/4" />
                            <x-skeleton.line w="w-10" h="h-5" />
                        </li>
                    @endfor
                @else
                    @foreach ($metrics['top_pages'] as $page)
                        <li
                            class="flex items-center justify-between gap-3
                                               rounded-lg px-2 py-2
                                               hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">

                            <span class="truncate text-slate-700 dark:text-slate-300">
                                {{ $page->page }}
                            </span>

                            <span
                                class="min-w-[42px] text-center
                                                   px-2 py-0.5 rounded-md text-xs font-semibold
                                                   bg-slate-100 dark:bg-slate-800">
                                {{ $page->visits }}
                            </span>
                        </li>
                    @endforeach
                @endif

            </ul>

        </div>

        {{-- Notifications Quick View --}}
        <div
            class="rounded-2xl border border-slate-200 dark:border-slate-800
               bg-white dark:bg-slate-900/90 p-5">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <flux:icon name="bell" variant="solid" size="18" />
                    </div>

                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        {{ __('Notifications') }}
                    </h2>
                </div>

                <a href="{{ route('admin.notifications') }}" wire:navigate
                    class="inline-flex items-center gap-1 text-xs font-medium text-accent hover:underline">

                    {{ __('View all') }}
                    <flux:icon name="arrow-right" size="12" />
                </a>
            </div>

            {{-- List --}}
            <ul class="space-y-3 text-sm">

                @if (!count($metrics['notifications'] ?? []))
                    @for ($i = 0; $i < 4; $i++)
                        <li class="flex items-start gap-3">
                            <span class="mt-1.5 w-2 h-2 rounded-full bg-slate-300"></span>
                            <div class="flex-1 space-y-2">
                                <x-skeleton.line w="w-4/5" />
                                <x-skeleton.line w="w-24" h="h-3" />
                            </div>
                        </li>
                    @endfor
                @else
                    @foreach ($metrics['notifications'] as $notification)
                        @php $unread = is_null($notification->read_at); @endphp

                        <li
                            class="flex items-start gap-3 rounded-lg px-2 py-2
                                               hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">

                            <span
                                class="mt-1.5 w-2 h-2 rounded-full
                                                   {{ $unread ? 'bg-emerald-500' : 'bg-slate-300' }}">
                            </span>

                            <div class="flex-1 min-w-0">
                                <p class="truncate text-slate-700 dark:text-slate-300">
                                    {{ $notification->data['message'] ?? __('New notification') }}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                @endif

            </ul>

        </div>

    </div>

    {{-- Recent Activity --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800
           bg-white dark:bg-slate-900/90 p-5">

        {{-- Header --}}
        <div class="flex items-center gap-2 mb-5">
            <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                <flux:icon name="clock" variant="solid" size="18" />
            </div>

            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                {{ __('Recent Activity') }}
            </h2>
        </div>

        {{-- Timeline --}}
        <ul class="space-y-4 text-sm">

            @if (!count($metrics['activities'] ?? []))
                @for ($i = 0; $i < 6; $i++)
                    <li class="flex items-start gap-3">
                        <span class="mt-2 w-2 h-2 rounded-full bg-slate-300"></span>
                        <div class="flex-1 space-y-2">
                            <x-skeleton.line w="w-3/4" />
                            <x-skeleton.line w="w-32" h="h-3" />
                        </div>
                    </li>
                @endfor
            @else
                @foreach ($metrics['activities'] as $activity)
                    <li class="flex items-start gap-3">
                        <span class="mt-2 w-2 h-2 rounded-full bg-accent"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-slate-700 dark:text-slate-300">
                                {{ $activity->event }}
                                <span class="text-xs text-slate-400">
                                    {{ $activity->page ? '— ' . $activity->page : '' }}
                                </span>
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                            </p>
                        </div>
                    </li>
                @endforeach
            @endif

        </ul>

    </div>


    @push('scripts')
        <script data-page-style src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script data-page-style>
            document.addEventListener('livewire:init', () => {
                const el = document.querySelector('#visitsChart');
                if (!el) return;

                const initialCategories = @json($chart['categories']);
                const initialSeries = @json($chart['series']);

                const options = {
                    chart: {
                        type: 'area',
                        height: 320,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2,
                        colors: ['#3b82f6']
                    },
                    grid: {
                        strokeDashArray: 4
                    },
                    xaxis: {
                        categories: initialCategories,
                        labels: {
                            rotate: 0
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (val) => Math.round(val)
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => `${val}`
                        }
                    },
                    series: initialSeries,
                };

                const chart = new ApexCharts(el, options);
                chart.render();

                window.addEventListener('dashboard-chart-updated', (e) => {
                    const payload = e.detail?.chart;
                    if (!payload) return;

                    chart.updateOptions({
                        xaxis: {
                            categories: payload.categories
                        },
                        series: payload.series
                    }, true, true);
                });
            });
        </script>
    @endpush
