<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Estudio al Día</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <script src="{{ asset('assets/app.js') }}" defer></script>
</head>
<body>
    @auth
        <header class="site-header">
            <div class="shell header-inner">
                <x-brand :href="route('tasks.index')" />
                <nav class="navigation" aria-label="Principal">
                    <a href="{{ route('tasks.index') }}" @class(['nav-link', 'active' => request()->routeIs('tasks.*')]) @if(request()->routeIs('tasks.*')) aria-current="page" @endif>
                        <x-icon name="list" /> Mis tareas
                    </a>
                    <a href="{{ route('account.show') }}" @class(['nav-link', 'active' => request()->routeIs('account.show')]) @if(request()->routeIs('account.show')) aria-current="page" @endif>
                        <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span> Mi cuenta
                    </a>
                </nav>
            </div>
        </header>
    @endauth

    @yield('content')
</body>
</html>
