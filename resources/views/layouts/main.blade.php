<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <title>RSH</title>
    @vite('resources/css/app.css')
    @livewireStyles()
    @stack('styles')
</head>

<body>
    @yield('content')
    @livewireScripts
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>