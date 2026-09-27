<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name') }}</title>

    <link rel="icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/favicon.ico">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-inertia::head>
        <meta data-inertia="description" name="description" content="Chekpoint Store is a demo gaming store page">
        <meta name="author" content="Adriansyah - Sekonso">
        <meta name="theme-color" content="c70036">
    </x-inertia::head>
</head>

<body class="dark">
    <x-inertia::app />
</body>

</html>