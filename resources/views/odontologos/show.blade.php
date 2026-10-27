@extends('layouts.app')
@section('titulo', $odontologo->nombre . ' ' . $odontologo->apellido)
@section('contenido')
    <h1>{{ $odontologo->nombre }} {{ $odontologo->apellido }}</h1>
    <div class="tarjeta">
        <div class="rejilla">
            <div><strong>Especialidad:</strong> {{ ucfirst($odontologo->especialidad) }}</div>
            <div><strong>Correo:</strong> {{ $odontologo->email }}</div>
            <div><strong>Estado:</strong> {{ $odontologo->activo ? 'Activo' : 'Inactivo' }}</div>
        </div>
        <div class="acciones" style="margin-top: 14px;">
            <a class="boton" href="{{ route('citas.create', ['odontologo_id' => $odontologo->id]) }}">Agendar cita</a>
            <a class="boton gris" href="{{ route('odontologos.edit', $odontologo) }}">Editar</a>
        <form method="POST" action="{{ route('odontologos.destroy', $odontologo) }}" onsubmit="return confirm('¿Eliminar a este odontólogo y su horario?')">
            @csrf
            @method('DELETE')
            <button class="boton rojo" type="submit">Eliminar</button>
        </form>
        </div>
    </div>

    <h2>Horario de atención</h2>
    <table>
        <tr><th>Día</th><th>Desde</th><th>Hasta</th><th></th></tr>
        @forelse ($horarios as $h)
            <tr>
                <td>{{ $h->nombreDia() }}</td>
                <td>{{ substr($h->hora_inicio, 0, 5) }}</td>
                <td>{{ substr($h->hora_fin, 0, 5) }}</td>
                <td>
                    <form method="POST" action="{{ route('horarios.destroy', $h) }}" onsubmit="return confirm('¿Quitar este tramo?')">
                        @csrf
                        @method('DELETE')
                        <button class="boton chico rojo" type="submit">Quitar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="vacio">Todavía no tiene horario de atención.</td></tr>
        @endforelse
    </table>
    <form class="tarjeta fila" style="margin-top: 10px;" method="POST" action="{{ route('horarios.store', $odontologo) }}">
        @csrf
        <div>
            <label for="dia_semana">Día</label>
            <select id="dia_semana" name="dia_semana">
                @foreach ($dias as $numero => $nombre)
                    <option value="{{ $numero }}" @selected(old('dia_semana') == $numero)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="hora_inicio">Desde</label>
            <input id="hora_inicio" type="time" name="hora_inicio" value="{{ old('hora_inicio', '08:00') }}" required>
        </div>
        <div>
            <label for="hora_fin">Hasta</label>
            <input id="hora_fin" type="time" name="hora_fin" value="{{ old('hora_fin', '12:00') }}" required>
        </div>
        <div style="flex: 0 0 auto;"><button class="boton verde" type="submit">Agregar tramo</button></div>
    </form>

    <h2>Próximas citas</h2>
    <table>
        <tr><th>Fecha</th><th>Paciente</th><th>Estado</th><th></th></tr>
        @forelse ($proximasCitas as $c)
            <tr>
                <td>{{ $c->inicio->format('d/m/Y H:i') }} - {{ $c->fin->format('H:i') }}</td>
                <td>{{ $c->paciente->nombre }} {{ $c->paciente->apellido }}</td>
                <td><span class="estado {{ $c->estado }}">{{ str_replace('_', ' ', $c->estado) }}</span></td>
                <td><a class="boton chico" href="{{ route('citas.show', $c) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="vacio">Sin citas próximas.</td></tr>
        @endforelse
    </table>
@endsection
