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

<style>
    :root {
        --v-color-theme-background-primary: #f9fafb;
        --v-color-theme-background-secondary: #ffffff;

        --v-color-secondary:
            {{ $settings['colors.secondary'] ?? '#0ea5e9' }}
        ;
        --v-color-accent:
            {{ $settings['colors.accent'] ?? '#22c55e' }}
        ;
        --v-color-background:
            {{ $settings['colors.background'] ?? '#0b1220' }}
        ;
    }

    .dark {
        --v-color-theme-background-primary: #0b1220;
        --v-color-theme-background-secondary: #050c1a;
    }
</style>


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=Tajawal:400,500,600" rel="stylesheet" />

@vite(['resources/css/admin.css', 'resources/js/admin.js'])
@fluxAppearance