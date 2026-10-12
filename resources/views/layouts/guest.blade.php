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
    <script>
        (function() {
            const params = new URLSearchParams(window.location.search);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            const param = params.get('theme');
            let theme = 'auto';
            try {
                if (param === 'dark' || param === 'light' || param === 'auto') {
                    localStorage.setItem('tabler-theme', param);
                    theme = param;
                } else {
                    theme = localStorage.getItem('tabler-theme') || 'auto';
                }
            } catch (e) {}
            const resolved = theme === 'auto' ? (prefersDark.matches ? 'dark' : 'light') : theme;
            document.documentElement.setAttribute('data-bs-theme', resolved);
        })();
    </script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            @yield('content')
        </div>
    </div>
</body>

</html>
