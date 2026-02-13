<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl" style="scroll-behavior: smooth;">

<head>
    {{-- Page Specific Head --}}
    @include('partials.app-head')
</head>
<body class="bg-background text-[#111111]">
    <div id="particles-js"></div>
    <div id="root" class="relative z-10"></div>
</body>
</html>