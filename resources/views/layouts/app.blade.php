<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
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

</body>

</html>
