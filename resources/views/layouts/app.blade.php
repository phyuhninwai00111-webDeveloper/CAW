<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Attendance System') }} - @yield('title', 'Dashboard')</title>

        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <!-- Local jQuery ကို သုံးခြင်း -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
       {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
        <script src="{{ asset('js/ui.js') }}"></script>
        @stack('head')
    </head>
    <body>
        @auth
            @include('layouts.navigation')
        @endauth
        @yield('content')
        @stack('scripts')
    </body>
</html>
