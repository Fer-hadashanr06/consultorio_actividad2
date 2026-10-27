<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts._cabecera')
    <title>Iniciar sesión · Consultorio odontológico</title>
</head>
<body>
<div class="acceso">
    <section class="acceso-lado">
        <a class="marca" href="{{ route('inicio') }}"><span class="sello">@include('layouts._diente')</span> Consultorio odontológico</a>
        <div>
            <h2>Todo el consultorio en un solo lugar</h2>
            <ul>
                <li>Agenda citas en bloques libres, sin cruces de horario.</li>
                <li>Consulta pacientes, historial clínico y estado de cuenta.</li>
                <li>Registra nuevos odontólogos y define su horario de atención.</li>
            </ul>
        </div>
        <span style="color:#9fb6c7;font-size:13px">Acceso solo para el personal autorizado.</span>
    </section>
    <section class="acceso-form">
        <form method="POST" action="{{ route('login.enviar') }}" novalidate>
            @csrf
            <h1>Iniciar sesión</h1>
            <p class="nota">Ingresa con tu cuenta del consultorio.</p>
            @if ($errors->any())
                <div class="alerta error" role="alert">
                    <ul>@foreach ($errors->all() as $mensaje)<li>{{ $mensaje }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="campo">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" autofocus required>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <div class="recordar">
                <input id="recordar" type="checkbox" name="recordar" value="1">
                <label for="recordar">Mantener la sesión iniciada</label>
            </div>
            <button class="boton" type="submit">Entrar</button>
            <a class="volver" href="{{ route('inicio') }}">Volver al inicio</a>
        </form>
    </section>
</div>
</body>
</html>
