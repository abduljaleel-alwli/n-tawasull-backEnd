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

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    :root {
        --secondary-color:
            {{ $settings['colors.secondary'] ?? '#203C71' }};
        --accent-color:
            {{ $settings['colors.accent'] ?? '#EF7F17' }};
        --background-color:
            {{ $settings['colors.background'] ?? '#f0f0f0' }};

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
