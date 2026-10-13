<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
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
    <div class="page">
        <x-header />
        <x-navbar />

        <div class="page-wrapper">
            <main class="page-body">
                <div class="container-xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('script')

</body>

</html>
