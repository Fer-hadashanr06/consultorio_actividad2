@extends('layouts.app')
@section('titulo', 'Panel')
@section('contenido')
    <div class="encabezado">
        <div>
            <h1>Hola, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
            <p>{{ now()->translatedFormat('l j \d\e F \d\e Y') }}</p>
        </div>
        <div class="atajos">
            <a class="boton" href="{{ route('citas.create') }}">Registrar cita</a>
            <a class="boton gris" href="{{ route('pacientes.create') }}">Nuevo paciente</a>
            <a class="boton gris" href="{{ route('odontologos.create') }}">Registrar odontólogo</a>
        </div>
    </div>

    <div class="cifras">
        <a class="cifra" href="{{ route('citas.index', ['fecha' => now()->toDateString()]) }}"><b>{{ $cifras['hoy'] }}</b><span>citas hoy</span></a>
        <a class="cifra {{ $cifras['por_confirmar'] > 0 ? 'alerta-c' : '' }}" href="{{ route('citas.index', ['estado' => 'agendada']) }}"><b>{{ $cifras['por_confirmar'] }}</b><span>por confirmar</span></a>
        <a class="cifra" href="{{ route('pacientes.index') }}"><b>{{ $cifras['pacientes'] }}</b><span>pacientes registrados</span></a>
        <a class="cifra" href="{{ route('odontologos.index') }}"><b>{{ $cifras['odontologos'] }}</b><span>odontólogos activos</span></a>
    </div>

    <div class="columnas">
        <div>
            <h2>Agenda de hoy</h2>
            <table>
                <tr><th>Hora</th><th>Paciente</th><th>Odontólogo</th><th>Estado</th><th></th></tr>
                @forelse ($citasHoy as $c)
                    <tr>
                        <td>{{ $c->inicio->format('H:i') }}–{{ $c->fin->format('H:i') }}</td>
                        <td>{{ $c->paciente->nombre }} {{ $c->paciente->apellido }}</td>
                        <td>Dr(a). {{ $c->odontologo->apellido }}</td>
                        <td><span class="estado {{ $c->estado }}">{{ str_replace('_', ' ', $c->estado) }}</span></td>
                        <td><a class="boton chico" href="{{ route('citas.show', $c) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vacio">No hay citas para hoy. <a href="{{ route('citas.create') }}">Registrar una</a>.</td></tr>
                @endforelse
            </table>

            <h2>Próximas citas</h2>
            <table>
                <tr><th>Fecha</th><th>Paciente</th><th>Odontólogo</th><th></th></tr>
                @forelse ($proximas as $c)
                    <tr>
                        <td>{{ $c->inicio->format('d/m H:i') }}</td>
                        <td>{{ $c->paciente->nombre }} {{ $c->paciente->apellido }}</td>
                        <td>Dr(a). {{ $c->odontologo->apellido }}</td>
                        <td><a class="boton chico" href="{{ route('citas.show', $c) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">No hay citas próximas.</td></tr>
                @endforelse
            </table>
        </div>

        <div>
            <h2>Últimos pacientes</h2>
            <div class="tarjeta">
                <ul class="lista-simple">
                    @forelse ($recientes as $p)
                        <li>
                            <div><a href="{{ route('pacientes.show', $p) }}">{{ $p->apellido }}, {{ $p->nombre }}</a><small>CI {{ $p->ci }}</small></div>
                        </li>
                    @empty
                        <li class="vacio">Todavía no hay pacientes.</li>
                    @endforelse
                </ul>
                <p style="margin:14px 0 0"><a class="boton chico gris" href="{{ route('pacientes.index') }}">Ver todos</a></p>
            </div>
        </div>
    </div>
@endsection
