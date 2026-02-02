<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl" style="scroll-behavior: smooth;">

<head>
    {{-- Page Specific Head --}}
    @include('partials.app-head')

    {{-- Head scripts payload --}}
    <script>
        window.__HEAD_SCRIPT__ = @json($settings['scripts.head'] ?? '');
    </script>

    {{-- Footer scripts payload --}}
    <script>
        window.__FOOTER_SCRIPT__ = @json($settings['scripts.footer'] ?? '');
    </script>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    @php
        $socialLinks = (array) settings('contact.social_links', []);
    @endphp

    <!-- =========================================================
       ✅ HEADER
       ========================================================= -->
    <header class="site-header">
        <nav class="navbar navbar-expand-lg site-navbar p-0 text-light">
            <div class="container">

                {{-- Logo + Site name --}}
                <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-light fs-3"
                    href="{{ route('home') }}">

                    <span class="logo-wrap">

                        <span class="log-box">
                            <x-app-logo-icon width="75" />
                        </span>
                    </span>

                    {{ settings('site_name', 'اسم الموقع') }}
                </a>

                <a class="d-flex align-items-center gap-2 fw-bold text-light fs-3 opacity-0"
                    style="pointer-events: none" href="{{ route('home') }}">

                    <span class="logo-wrap">

                        <span class="log-box">
                            <x-app-logo-icon width="75" />
                        </span>
                    </span>

                    {{ settings('site_name', 'اسم الموقع') }}
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">

                    {{-- Navigation --}}
                    <ul class="navbar-nav me-auto align-items-lg-center gap-lg-4 mt-3 mt-lg-0">

                        <li class="nav-item">
                            <a href="{{ route('home') }}"
                                class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                الرئيسية
                            </a>
                        </li>

                        <li class="nav-item">
                            <a wire:navigate href="{{ route('about') }}"
                                class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                                من نحن
                            </a>
                        </li>

                        <li class="nav-item">
                            <a wire:navigate href="{{ route('products') }}"
                                class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}">
                                منتجاتنا
                            </a>
                        </li>

                        <li class="nav-item">
                            <a wire:navigate href="{{ route('clients') }}"
                                class="nav-link {{ request()->routeIs('clients') ? 'active' : '' }}">
                                آراء عملائنا
                            </a>
                        </li>

                        <li class="nav-item">
                            <a wire:navigate href="{{ route('contact') }}"
                                class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                                تواصل معنا
                            </a>
                        </li>

                    </ul>


                    {{-- Actions --}}
                    <div class="row justify-content-center justify-content-lg-end mt-3 mt-lg-0 g-2 mb-4 mb-lg-0">
                        <div class="col-10 col-sm-6 col-lg-auto">
                            <a class="btn header-btn" wire:navigate href="{{ route('contact') }}">
                                اطلب عرض سعر
                            </a>

                            <a class="btn header-btn outline" wire:navigate href="{{ route('contact') }}">
                                تواصل معنا
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </nav>
    </header>

    @if (request()->is('/'))
        <main class="home-main">
        @elseif (request()->is('about'))
            <main class="about-bg">
            @elseif (request()->is('products'))
                <main class="products-bg" id="products">
                @elseif (request()->is('contact'))
                    <main id="contact" class="contact-bg">
                    @elseif (request()->is('clients'))
                        <main class="aflak-testimonials">
    @endif
    {{ $slot }}

    <!-- ================== FOOTER ================== -->
    <footer class="footer-section text-white pt-5 pb-4">
        <div class="container">
            <div class="row">

                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">روابط سريعة</h5>

                    <ul class="footer-links">
                        <li>
                            <a href="{{ route('home') }}" class="accent-color">
                                الرئيسية
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('about') }}" class="accent-color">
                                من نحن
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('products') }}" class="accent-color">
                                منتجاتنا
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('clients') }}" class="accent-color">
                                آراء عملائنا
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('contact') }}" class="accent-color">
                                تواصل معنا
                            </a>
                        </li>
                    </ul>
                </div>


                <div class="col-md-3 mb-4">
                    <h5 class="footer-title d-flex justify-content-center mx-auto">معلومات التواصل</h5>
                    <ul class="footer-links">
                        <li class="accent-color"><i class="fa-solid fa-phone me-2" style="color:#0f1b2d;"></i>
                            {{ $settings['contact.phone'] }}</li>
                        <li class="accent-color"><i class="fa-solid fa-envelope me-2" style="color:#0f1b2d;"></i>
                            {{ $settings['contact.email_to'] }}</li>
                        <li class="accent-color"><i class="fa-solid fa-location-dot me-2" style="color:#0f1b2d;"></i>
                            {{ $settings['contact.location'] }}
                        </li>
                    </ul>
                </div>

                <div class="col-md-3 mb-5">
                    <h5 class="footer-title d-flex justify-content-center mx-auto">تابعنا</h5>
                    <div class="aflak-social  text-center">
                        @foreach ($socialLinks as $social)
                            @php
                                $platform = Str::of($social['platform'])->lower()->replace(' ', '_');
                            @endphp

                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener" data-analytics
                                data-event="social_click" data-entity="{{ $platform }}"
                                data-id="{{ $platform }}" data-source="footer_section" class="social-link">
                                @if ($social['icon_type'] === 'class')
                                    <i class="{{ $social['icon_value'] }}"></i>
                                @else
                                    {!! $social['icon_value'] !!}
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-3 mb-4 text-center">
                    {{-- <x-app-logo-icon width="110" class="mb-3" /> --}}
                    <img src="{{ asset('logo-white-2.png') }}" width="110" alt="{{ __('Site logo') }}"
                        {{ $attributes->merge(['class' => 'h-8']) }}>
                    <p class="small">
                        {{ $settings['site_description'] }}
                    </p>
                </div>
            </div>

            <hr class="border-light opacity-25" />

            <div class="text-center mt-3">
                <p class="mb-0">© {{ now()->year }} {{ $settings['site_name'] }}. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>
    </main>

    @fluxScripts

    @if (request()->is('/'))
        @include('partials.home.footer')
    @elseif (request()->is('about'))
        @include('partials.about.footer')
    @elseif (request()->is('products'))
        @include('partials.products.footer')
    @elseif (request()->is('contact'))
        @include('partials.contact.footer')
    @elseif (request()->is('clients'))
        @include('partials.clients.footer')
    @endif

    {{-- Footer Script --}}
    {{-- {!! $settings['scripts.footer'] ?? '' !!} --}}
</body>

</html>
