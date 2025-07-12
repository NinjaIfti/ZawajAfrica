<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="adsterra-site-verification" content="adsterra-verification-code">

        <title>{{ config('app.name', 'ZawajAfrica') }}</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/fav.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/fav.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/fav.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/fav.png') }}">
        <meta name="msapplication-TileImage" content="{{ asset('images/fav.png') }}">
        <meta name="theme-color" content="#ffffff">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- FontAwesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Adsterra Script (only for eligible users) -->
        @php
            $adsterraService = app(\App\Services\AdsterraService::class);
            $user = auth()->user();
        @endphp
        @if($adsterraService->shouldShowAds($user) && $adsterraService->shouldShowAdsOnPage(request()))
            {!! $adsterraService->generateAdsterraScript() !!}
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
