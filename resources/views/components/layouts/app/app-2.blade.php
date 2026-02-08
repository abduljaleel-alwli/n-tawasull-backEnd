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

    <main>
        {{ $slot }}
    </main>

    @fluxScripts

    {{-- Footer Script --}}
</body>

</html>
