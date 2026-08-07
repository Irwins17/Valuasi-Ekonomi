<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Valuasi Ekonomi') }}</title>
    <meta name="description" content="Platform transparansi data valuasi ekonomi untuk kebijakan berkelanjutan.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Only the weights the stylesheets actually ask for. 300 and 900 were
         being fetched on every page load without a single rule using them. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Only the route table, not Ziggy's @routes directive: that also inlines
         the ~21 KB route() helper into every single page, uncached, even though
         ziggy-js is already an npm dependency. app.jsx imports the helper so it
         ships once inside a cached bundle.

         The table is deliberately NOT narrowed to a guest-only group. Inertia
         navigates by XHR, so this <script> only ever runs on the first document
         load: a visitor who lands on /login as a guest would keep that guest
         table after signing in, and every admin route() call would then throw
         "route is not in the route list". --}}
    <script>window.Ziggy = {!! json_encode(new \Tighten\Ziggy\Ziggy) !!};</script>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
