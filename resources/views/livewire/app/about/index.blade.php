<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $title;
    public string $subtitle;
    public string $description;

    public array $features = [];
    public array $topFeatures = [];
    public array $middleFeatures = [];

    public function mount(): void
    {
        $this->title = settings('features.title', 'مميزاتنا');
        $this->subtitle = settings('features.subtitle', '');
        $this->description = settings('features.description', '');

        $this->features = settings('features.items', []);

        // أول 3 مميزات
        $this->topFeatures = array_slice($this->features, 0, 3);

        // الباقي (لـ Middle section)
        $this->middleFeatures = array_slice($this->features, 3);
    }
};
?>

@php
    function renderFeatureIcon(array $feature): string
    {
        $type = $feature['icon_type'] ?? null;
        $icon = $feature['icon_value'] ?? null;

        if (!$type || !$icon) {
            return '';
        }

        return match ($type) {
            // Font Awesome (class)
            'class' => '<i class="' . e($icon) . '"></i>',
            // Inline SVG
            'svg' => $icon,

            default => '',
        };
    }
@endphp


<!-- AFLAAK | About Page -->
<div>
    <div class="aflaak-layer">
        <div class="container">

            <!-- HERO -->
            <div class="text-center mb-4">
                <h1 class="aflaak-hero-title">
                    {{ $title }}
                </h1>

                @if ($description)
                    <p class="aflaak-hero-sub">
                        {{ $description }}
                    </p>
                @endif

                <div class="mt-3">
                    <a class="aflaak-pill" wire:navigate href="{{ route('contact') }}">
                        <span>اطلب عرض سعر الآن</span>
                    </a>
                </div>
            </div>


            <!-- TOP 3 CARDS -->
            <div class="row g-4 justify-content-center">
                @foreach ($topFeatures as $feature)
                    <div class="col-12 col-lg-4">
                        <div class="aflaak-card p-4 h-100">
                            <div class="d-flex align-items-start gap-3">

                                <div class="aflaak-icon" aria-hidden="true">
                                    @switch($feature['icon_type'] ?? null)
                                        @case('flux')
                                            <flux:icon name="{{ $feature['icon_value'] }}" />
                                        @break

                                        @case('class')
                                        @case('svg')
                                            {!! renderNonFluxIcon($feature) !!}
                                        @break
                                    @endswitch
                                </div>

                                <div>
                                    <h2 class="aflaak-card-title text-light">
                                        {{ $feature['title'] ?? '' }}
                                    </h2>

                                    <p class="aflaak-card-text text-light">
                                        {{ $feature['description'] ?? '' }}
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            <!-- MIDDLE: Dynamic Features (same Vision & Mission design) -->
            <div class="row g-4 mt-1 align-items-stretch">
                <div class="col-12 col-lg-7">

                    @foreach ($middleFeatures as $index => $feature)
                        <div class="aflaak-card p-4 mb-4">
                            <div class="d-flex align-items-start gap-3">

                                <div class="aflaak-icon" aria-hidden="true">
                                    @switch($feature['icon_type'] ?? null)
                                        @case('flux')
                                            <flux:icon name="{{ $feature['icon_value'] }}" />
                                        @break

                                        @case('class')
                                        @case('svg')
                                            {!! renderFeatureIcon($feature) !!}
                                        @break
                                    @endswitch
                                </div>

                                <div>
                                    <h2 class="aflaak-card-title text-light">
                                        {{ $feature['title'] ?? '' }}
                                    </h2>

                                    <p class="aflaak-card-text text-light">
                                        {{ $feature['description'] ?? '' }}
                                    </p>
                                </div>

                            </div>
                        </div>
                    @endforeach

                </div>

                <div class="col-12 col-lg-5">
                    <!-- Replace background image below -->
                    <div class="aflaak-media-card"></div>
                </div>
            </div>


            <!-- Stats -->
            <div class="text-center mt-5">
                <h2 class="aflaak-sec-title">
                    لماذا نقطة تواصل؟
                </h2>
            </div>


            <div class="aflaak-stats-wrap">
                <div class="aflaak-mid-line d-none d-lg-block"></div>

                <div class="row g-3 justify-content-center">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="aflaak-stat " style="background-color: #071225bb;">
                            <div class="aflaak-num">+10,000</div>
                            <div class="aflaak-lbl text-light">طن توريد سنوي</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="aflaak-stat" style="background-color: #071225bb;">
                            <div class="aflaak-num">+500</div>
                            <div class="aflaak-lbl  text-light ">عميل ومقاول</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="aflaak-stat" style="background-color: #071225bb;">
                            <div class="aflaak-num">24/7</div>
                            <div class="aflaak-lbl  text-light ">متابعة واستجابة</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="aflaak-stat" style="background-color:#071225bb; ">
                            <div class="aflaak-num">+7</div>
                            <div class="aflaak-lbl  text-light ">فئات منتجات</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="row justify-content-center mt-4">
                <div class="col-12 col-lg-10">
                    <div class="aflaak-cta">
                        <h3 class="aflaak-cta-title">جاهز تجهّز مشروعك؟</h3>
                        <p class="aflaak-cta-text ">
                            تواصل معنا للحصول على عرض سعر سريع، ومعرفة التوفر والمقاسات،
                            وخيارات التوريد التي تناسب احتياجك.
                        </p>
                        <div class="mt-3">
                            <a class="aflaak-pill aflaak-pill--alt" wire:navigate href="{{ route('contact') }}"><span>تواصل معنا الآن</span></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
