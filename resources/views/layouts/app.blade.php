<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Status Link')</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #f4f6f8; color: #172033; }
        header, main { max-width: 960px; margin: auto; padding: 1.5rem; }
        header { display: flex; align-items: center; justify-content: space-between; }
        .card { background: white; border: 1px solid #dde2e8; border-radius: .75rem; padding: 1.25rem; margin-bottom: 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        label { display: block; margin-top: 1rem; }
        input { box-sizing: border-box; width: 100%; padding: .7rem; margin-top: .3rem; }
        button { padding: .65rem 1rem; cursor: pointer; }
        .error { color: #b42318; }
    </style>
</head>
<body>
    @auth
        <header>
            <strong>Status Link</strong>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </header>
    @endauth
    <main>@yield('content')</main>
</body>
</html>
