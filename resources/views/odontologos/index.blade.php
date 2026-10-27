@extends('layouts.app')
@section('titulo', 'Odontólogos')
@section('contenido')
    <div class="encabezado">
        <div><h1>Odontólogos</h1><p>Registra nuevos odontólogos y define su horario de atención.</p></div>
        <a class="boton" href="{{ route('odontologos.create') }}">Registrar odontólogo</a>
    </div>
    <table>
        <tr><th>Odontólogo</th><th>Especialidad</th><th>Correo</th><th class="num">Tramos</th><th class="num">Citas</th><th>Estado</th><th></th></tr>
        @forelse ($odontologos as $o)
            <tr>
                <td>{{ $o->nombre }} {{ $o->apellido }}</td>
                <td>{{ ucfirst($o->especialidad) }}</td>
                <td>{{ $o->email }}</td>
                <td class="num">{{ $o->horarios_count }}</td>
                <td class="num">{{ $o->citas_count }}</td>
                <td>{{ $o->activo ? 'Activo' : 'Inactivo' }}</td>
                <td class="acciones">
                    <a class="boton chico" href="{{ route('odontologos.show', $o) }}">Ver</a>
                    <a class="boton chico gris" href="{{ route('odontologos.edit', $o) }}">Editar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">No hay odontólogos registrados.</td></tr>
        @endforelse
    </table>
@endsection
