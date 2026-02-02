<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $title;
    public string $description;
    public string $email;
    public string $phone;
    public string $location;
    public string $working_time;
    public string $mapUrl;
    public array $socialLinks = [];

    public function mount(): void
    {
        $this->title = settings('contact.title', __('Contact us'));
        $this->description = settings('contact.description', '');
        $this->email = settings('contact.email_to', '');
        $this->phone = settings('contact.phone', '');
        $this->location = settings('contact.location', '');
        $this->working_time = settings('contact.working_time', '');
        $this->mapUrl = settings('contact.map_url', '');
        $this->socialLinks = (array) settings('contact.social_links', []);
    }
};
?>

<!-- Contact Us Section (AFLAAK) -->
<div>
    <div class="contact-bg__overlay"></div>

    <div class="container">
        <div class="contact-bg__wrap">
            <!-- Right: Form -->
            <livewire:app.contact.contact-form />

            <!-- Left: Info -->
            <div class="af-card af-info-card">
                <div class="af-info-head">
                    <div class="af-badge">AFLAAK</div>
                    @if ($title)
                        <h3 class="af-info-title">{{ $title }}</h3>
                    @endif
                    @if ($description)
                        <p class="af-info-sub">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                <div class="af-info-grid">
                    @if ($phone)
                        <div class="af-info-item">
                            <div class="af-ico accent-color " aria-hidden="true"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <div class="af-k">{{ __('Phone') }}:</div>
                                <div class="af-v"><a href="tel:+975552182700">{{ $phone }}</a></div>
                            </div>
                        </div>
                    @endif

                    @if ($email)
                        <div class="af-info-item">
                            <div class="af-ico accent-color " aria-hidden="true"><i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <div class="af-k">{{ __('Email') }}:</div>
                                <div class="af-v"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                            </div>
                        </div>
                    @endif

                    @if ($location)
                        <div class="af-info-item">
                            <div class="af-ico accent-color " aria-hidden="true"><i
                                    class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <div class="af-k">{{ __('Location') }}:</div>
                                <div class="af-v">{{ $location }}</div>
                            </div>
                        </div>
                    @endif

                    @if ($working_time)
                        <div class="af-info-item">
                            <div class="af-ico accent-color" aria-hidden="true"><i class="fa-solid fa-stopwatch"></i>
                            </div>
                            <div>
                                <div class="af-k">{{ __('Working time') }}</div>
                                <div class="af-v">{{ $working_time }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if (count($socialLinks))
                    <!-- السوشيال ميديا -->
                    <div class="aflak-social mt-4 d-flex justify-content-center mx-auto">
                        @foreach ($socialLinks as $social)
                            @php
                                $platform = Str::of($social['platform'])
                                    ->lower()
                                    ->replace(' ', '_');
                            @endphp

                            <a href="{{ $social['url'] }}"
                            target="_blank"
                            rel="noopener"
                            data-analytics
                            data-event="social_click"
                            data-entity="{{ $platform }}_contact"
                            data-id="{{ $platform }}_contact"
                            data-source="contact_page"
                            class="social-link"
                            >
                                @if ($social['icon_type'] === 'class')
                                    <i class="{{ $social['icon_value'] }}"></i>
                                @else
                                    {!! $social['icon_value'] !!}
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>




        </div>

        {{-- Map --}}
        @if ($mapUrl)
            <section class="af-map-section mt-5">

                {{-- Text side --}}
                <div class="af-map-text">
                    <h3 class="af-map-title">موقعنا على الخريطة</h3>
                    <p class="af-map-sub">
                        يسعدنا زيارتكم في مقرنا، يمكنكم الوصول إلينا بسهولة عبر الخريطة.
                    </p>
                </div>

                {{-- Map side --}}
                <div class="af-map-frame">
                    <div class="af-map-inner">
                        <iframe src="{{ $mapUrl }}" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

            </section>
        @endif
    </div>
</div>
