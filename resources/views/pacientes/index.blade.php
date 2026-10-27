@extends('layouts.app')
@section('titulo', 'Pacientes')
@section('contenido')
    <div class="encabezado">
        <h1>Pacientes</h1>
        <a class="boton" href="{{ route('pacientes.create') }}">Nuevo paciente</a>
    </div>
    <form class="fila tarjeta" method="GET" action="{{ route('pacientes.index') }}">
        <div>
            <label for="q">Buscar por CI, nombre o apellido</label>
            <input id="q" name="q" value="{{ $buscar }}">
        </div>
        <div style="flex: 0 0 auto;">
            <button class="boton" type="submit">Buscar</button>
        </div>
    </form>
    <table>
        <tr><th>CI</th><th>Paciente</th><th>Teléfono</th><th class="num">Citas</th><th></th></tr>
        @forelse ($pacientes as $p)
            <tr>
                <td>{{ $p->ci }}</td>
                <td>{{ $p->apellido }}, {{ $p->nombre }}</td>
                <td>{{ $p->telefono ?? '—' }}</td>
                <td class="num">{{ $p->citas_count }}</td>
                <td class="acciones">
                    <a class="boton chico" href="{{ route('pacientes.show', $p) }}">Ver</a>
                    <a class="boton chico gris" href="{{ route('pacientes.edit', $p) }}">Editar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="vacio">No hay pacientes que coincidan.</td></tr>
        @endforelse
    </table>
    {{ $pacientes->links() }}
@endsection
