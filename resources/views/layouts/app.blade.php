<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts._cabecera')
    <title>@yield('titulo', 'Panel') · Consultorio odontológico</title>
</head>
<body>
@php
    $iconos = [
        'panel' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'citas' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'pacientes' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.870M16 3.130a4 4 0 0 1 0 7.750"/>',
        'odontologos' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
        'mas' => '<path d="M12 5v14M5 12h14"/>',
        'salir' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    ];
    $icono = fn ($n) => new \Illuminate\Support\HtmlString('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $iconos[$n] . '</svg>');
@endphp
<div class="panel-app">
    <aside class="lateral">
        <a class="marca" href="{{ route('inicio') }}"><span class="sello">@include('layouts._diente')</span> Consultorio</a>
        <nav aria-label="Panel">
            <a href="{{ route('panel') }}" class="{{ request()->routeIs('panel') ? 'activo' : '' }}">{{ $icono('panel') }} Panel</a>
            <a href="{{ route('citas.index') }}" class="{{ request()->routeIs('citas.index', 'citas.show') ? 'activo' : '' }}">{{ $icono('citas') }} Citas</a>
            <a href="{{ route('pacientes.index') }}" class="{{ request()->routeIs('pacientes.*') ? 'activo' : '' }}">{{ $icono('pacientes') }} Pacientes</a>
            <a href="{{ route('odontologos.index') }}" class="{{ request()->routeIs('odontologos.*') ? 'activo' : '' }}">{{ $icono('odontologos') }} Odontólogos</a>
        </nav>
        <div class="nuevo"><a class="boton" href="{{ route('citas.create') }}">{{ $icono('mas') }} Agendar cita</a></div>
        <div class="sesion">
            <div><b>{{ auth()->user()->name }}</b><span>{{ auth()->user()->email }}</span></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">{{ $icono('salir') }} Cerrar sesión</button>
            </form>
        </div>
    </aside>
    <main class="area">
        <div class="area-interior">
            @if (session('exito'))
                <div class="alerta exito" role="status">{{ session('exito') }}</div>
            @endif
            @if ($errors->any())
                <div class="alerta error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $mensaje)
                            <li>{{ $mensaje }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('contenido')
        </div>
    </main>
</div>
</body>
</html>
