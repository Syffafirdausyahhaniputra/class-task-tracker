<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>@yield('title', 'Class Task Tracker')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <main class="page-shell">
        @yield('content')
    </main>
</body>
</html>
