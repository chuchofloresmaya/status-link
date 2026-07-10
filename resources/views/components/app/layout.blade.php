@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title }} · Status Link</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="min-h-screen lg:flex">
    <x-app.sidebar />
    <div class="min-w-0 flex-1 lg:pl-64">
        <x-app.topbar :title="$title" />
        <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <x-ui.alert>{{ session('success') }}</x-ui.alert>
            @endif
            @if ($errors->any())
                <x-ui.alert type="error">Revisa los campos marcados antes de continuar.</x-ui.alert>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
