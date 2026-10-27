@extends('layouts.app')
@section('titulo', 'Citas')
@section('contenido')
    <div class="encabezado">
        <h1>Citas</h1>
        <a class="boton" href="{{ route('citas.create') }}">Registrar cita</a>
    </div>
    <form class="tarjeta fila" method="GET" action="{{ route('citas.index') }}">
        <div>
            <label for="fecha">Fecha</label>
            <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}">
        </div>
        <div>
            <label for="odontologo_id">Odontólogo</label>
            <select id="odontologo_id" name="odontologo_id">
                <option value="">Todos</option>
                @foreach ($odontologos as $o)
                    <option value="{{ $o->id }}" @selected(request('odontologo_id') == $o->id)>{{ $o->nombre }} {{ $o->apellido }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') == $estado)>{{ str_replace('_', ' ', ucfirst($estado)) }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex: 0 0 auto;">
            <button class="boton" type="submit">Filtrar</button>
            <a class="boton gris" href="{{ route('citas.create') }}">Nueva cita</a>
        </div>
    </form>
    <table>
        <tr><th>Fecha y hora</th><th>Paciente</th><th>Odontólogo</th><th>Estado</th><th></th></tr>
        @forelse ($citas as $c)
            <tr>
                <td>{{ $c->inicio->format('d/m/Y H:i') }} - {{ $c->fin->format('H:i') }}</td>
                <td>{{ $c->paciente->nombre }} {{ $c->paciente->apellido }}</td>
                <td>{{ $c->odontologo->nombre }} {{ $c->odontologo->apellido }}<br><small>{{ ucfirst($c->odontologo->especialidad) }}</small></td>
                <td><span class="estado {{ $c->estado }}">{{ str_replace('_', ' ', $c->estado) }}</span></td>
                <td><a class="boton chico" href="{{ route('citas.show', $c) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="vacio">No hay citas con esos filtros.</td></tr>
        @endforelse
    </table>
    {{ $citas->links() }}
@endsection
