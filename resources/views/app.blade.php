<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? env('APP_NAME') }}</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-inertia::head />
</head>

<body class="dark">
    <x-inertia::app />
</body>

</html>
