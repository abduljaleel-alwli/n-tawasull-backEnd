<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ $settings['seo.meta_title'] ?? config('app.name') }}
</title>

<meta name="description" content="{{ $settings['seo.meta_description'] ?? '' }}">

<meta name="keywords" content="{{ $settings['seo.keywords'] ?? '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

@if (!empty($settings['branding.favicon']))
    <link rel="icon" href="{{ asset('storage/' . $settings['branding.favicon']) }}">
@endif

<!-- Bootstrap RTL -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
{{-- <link href="assets/css/bootstrap.rtl.min.css" rel="stylesheet"> --}}

<!-- Font -->
<link href="https://fonts.bunny.net/css?family=Tajawal:400,500,600" rel="stylesheet" />

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

@vite(['resources/css/app.css', 'resources/js/app.js'])

@if (request()->is('/'))
    @include('partials.home.head')
@elseif (request()->is('about'))
    @include('partials.about.head')
@elseif (request()->is('products'))
    @include('partials.products.head')
@elseif (request()->is('contact'))
    @include('partials.contact.head')
@elseif (request()->is('clients'))
    @include('partials.clients.head')
@endif

<link data-page-style rel="stylesheet" href="{{ asset('assets/css/mousecursor.css') }}">

<style>
    :root {
        --secondary-color:
            {{ $settings['colors.secondary'] ?? '#0d6efdf2' }};
        --accent-color:
            {{ $settings['colors.accent'] ?? '#FED403' }};
        --background-color:
            {{ $settings['colors.background'] ?? '#0b1220' }};

        /* Reset App Colors */
        --yellow: var(--accent-color);
        --gh-yellow: var(--accent-color);
        --af-yellow: var(--accent-color);

        /* المفروض يكون اللون --accent-color ولكن بدرجة اعمق */
        /* --yellow2: var(--accent-color); */
        /* --yellow2: color-mix(in srgb, var(--secondary-color) 80%, transparent); */
        --yellow2: color-mix(in srgb,
                var(--accent-color) 31%,
                color-mix(in srgb, var(--secondary-color) 85%, transparent));

        --secondary-color-2: #f5f5f5;

        --blue: var(--secondary-color);
        --af-blue: var(--secondary-color);
    }

    @font-face {
        font-family: "Almarai-Regular";
        src: url("{{ asset('assets/fonts/Almarai-Regular.ttf') }}") format("truetype");
        font-weight: normal;
        font-style: normal;
    }
</style>
