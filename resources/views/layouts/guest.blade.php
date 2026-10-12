<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'SIAset') }} - @yield('title', 'Masuk')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            @yield('content')
        </div>
    </div>
</body>

</html>
