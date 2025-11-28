<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="adsterra-site-verification" content="adsterra-verification-code">

        <title>{{ config('app.name', 'ZawajAfrica') }}</title>
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="ZawajAfrica">
        
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
        
        <!-- Social Bar Script for zawajafrica.online -->
        <script>
            // Load social bar script with error handling
            (function() {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = '//pl27099042.effectivegatecpm.com/40/25/2a/40252a1397d95eb269852aea67a5c58f.js';
                script.onerror = function() {
                    console.log('Social bar script blocked by ad blocker - this is normal in development');
                };
                document.head.appendChild(script);
            })();
        </script>

        <!-- Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('ServiceWorker registration successful');
                            
                            // Force update check on every page load
                            registration.update();
                            
                            // Check for updates periodically
                            setInterval(function() {
                                registration.update();
                            }, 60000); // Check every minute
                            
                            // Listen for updates
                            registration.addEventListener('updatefound', function() {
                                const newWorker = registration.installing;
                                newWorker.addEventListener('statechange', function() {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // New service worker available, reload to activate
                                        console.log('New service worker available, reloading...');
                                        window.location.reload();
                                    }
                                });
                            });
                        })
                        .catch(function(err) {
                            console.log('ServiceWorker registration failed: ', err);
                        });
                    
                    // Force all existing service workers to update
                    navigator.serviceWorker.getRegistrations().then(function(registrations) {
                        registrations.forEach(function(registration) {
                            registration.update();
                        });
                    });
                });
            }
        </script>

        <script type="text/javascript">
            window.googleTranslateElementInit = function() {
                window.dispatchEvent(new CustomEvent('google-translate-ready'));
            };
        </script>

        <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    </body>
</html>
