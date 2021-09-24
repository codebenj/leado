@php
$config = [
    'appName' => config('app.name'),
    'appEnv' => config('app.env'),
    'locale' => $locale = app()->getLocale(),
    'locales' => config('app.locales'),
    'githubAuth' => config('services.github.client_id'),
    'PUSHER_APP_KEY' => env('PUSHER_APP_KEY', '1bcb509b13f88258d5de'),
    'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER', 'ap4')
];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }} ">
<head>

  <meta charset="utf-8">
  <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0, shrink-to-fit=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }} {{config('app.env')}}</title>
  {{-- Favicons --}}
  <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/app-assets/img/ico/apple-touch-icon-60x60.png">
  <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/app-assets/img/ico/apple-touch-icon-76x76.png">
  <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/app-assets/img/ico/apple-touch-icon-120x120.png">
  <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/app-assets/img/ico/apple-touch-icon-152x152.png">
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/img/ico/tralead-icon-16.png">
  <link rel="shortcut icon" type="image/png" href="/app-assets/img/ico/tralead-icon-32.png">


  {{-- Stylesheets --}}
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/fonts/simple-line-icons/style.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/fonts/sf-ui/style.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/css/app.css">
  <link rel="stylesheet" type="text/css" href="{{ mix('dist/css/app.css') }}">
  <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCTuOVB720DTWz74jYjK1lAUKZZFdZMpGI&libraries=places"></script> -->
</head>
<body>
  <div id="app"></div>

  {{-- Global configuration object --}}
  <script>
    window.config = @json($config);
  </script>

  {{-- Load the application scripts --}}

  @if (config('app.env') == 'develop')
  <script src="{{ URL::to('/socket.io/socket.io.js') }}"></script>
  @endif
  <script src="{{ mix('dist/js/app.js') }}"></script>
</body>
</html>
