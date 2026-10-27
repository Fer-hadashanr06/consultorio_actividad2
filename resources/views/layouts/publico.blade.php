<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts._cabecera')
    <title>@yield('titulo', 'Consultorio odontológico')</title>
</head>
<body>
    <header class="barra">
        <div class="contenedor">
            <a class="marca" href="{{ route('inicio') }}"><span class="sello">@include('layouts._diente')</span> Consultorio odontológico</a>
            <nav aria-label="Principal">
                <a href="{{ route('inicio') }}#que-es">La odontología</a>
                <a href="{{ route('inicio') }}#especialidades">Especialidades</a>
                <a href="{{ route('inicio') }}#cuidado">Cuidado diario</a>
                <a href="{{ route('inicio') }}#preguntas">Preguntas</a>
                @auth
                    <a class="boton" href="{{ route('panel') }}">Ir al panel</a>
                @else
                    <a class="boton" href="{{ route('login') }}">Iniciar sesión</a>
                @endauth
            </nav>
        </div>
    </header>
    @yield('contenido')
    <footer class="pie-publico">
        <div class="contenedor">
            <span>Consultorio odontológico · Sistema de citas</span>
            <span>La información de esta página es orientativa y no reemplaza una consulta con tu odontólogo.</span>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
