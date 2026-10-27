@extends('layouts.app')
@section('titulo', $paciente->nombre . ' ' . $paciente->apellido)
@section('contenido')
    <h1>{{ $paciente->nombre }} {{ $paciente->apellido }}</h1>

    <div class="tarjeta">
        <div class="rejilla">
            <div><strong>CI:</strong> {{ $paciente->ci }}</div>
            <div><strong>Nacimiento:</strong> {{ $paciente->fecha_nacimiento->format('d/m/Y') }}</div>
            <div><strong>Teléfono:</strong> {{ $paciente->telefono ?? '—' }}</div>
            <div><strong>Correo:</strong> {{ $paciente->email ?? '—' }}</div>
            <div><strong>Dirección:</strong> {{ $paciente->direccion ?? '—' }}</div>
            <div><strong>Antecedentes:</strong> {{ $paciente->historial?->antecedentes ?? 'Sin registrar' }}</div>
        </div>
        <div class="acciones" style="margin-top: 14px;">
            <a class="boton" href="{{ route('citas.create', ['paciente_id' => $paciente->id]) }}">Agendar cita</a>
            <a class="boton gris" href="{{ route('pacientes.edit', $paciente) }}">Editar</a>
        <form method="POST" action="{{ route('pacientes.destroy', $paciente) }}" onsubmit="return confirm('¿Eliminar a este paciente?')">
            @csrf
            @method('DELETE')
            <button class="boton rojo" type="submit">Eliminar</button>
        </form>
        </div>
    </div>

    <h2>Estado de cuenta</h2>
    <table>
        <tr><th>Fecha</th><th>Odontólogo</th><th>Tratamiento</th><th class="num">Costo (Bs)</th></tr>
        @forelse ($cuenta['tratamientos'] as $t)
            <tr>
                <td>{{ $t['fecha'] }}</td><td>{{ $t['odontologo'] }}</td><td>{{ $t['descripcion'] }}</td>
                <td class="num">{{ number_format($t['costo'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="vacio">Todavía no tiene tratamientos.</td></tr>
        @endforelse
    </table>
    <table style="margin-top: 10px;">
        <tr><th>Fecha de pago</th><th>Método</th><th class="num">Monto (Bs)</th></tr>
        @forelse ($cuenta['pagos'] as $pago)
            <tr><td>{{ $pago['fecha'] }}</td><td>{{ ucfirst($pago['metodo']) }}</td><td class="num">{{ number_format($pago['monto'], 2) }}</td></tr>
        @empty
            <tr><td colspan="3" class="vacio">Todavía no tiene pagos.</td></tr>
        @endforelse
    </table>
    <div class="tarjeta" style="margin-top: 10px;">
        Total tratamientos: <strong>Bs {{ number_format($cuenta['total_tratamientos'], 2) }}</strong> ·
        Total pagos: <strong>Bs {{ number_format($cuenta['total_pagos'], 2) }}</strong> ·
        Saldo: <span class="{{ $cuenta['saldo'] > 0 ? 'saldo-debe' : 'saldo-ok' }}">Bs {{ number_format($cuenta['saldo'], 2) }}</span>
    </div>

    <h2>Registrar pago</h2>
    <form class="tarjeta fila" method="POST" action="{{ route('pagos.store', $paciente) }}">
        @csrf
        <div>
            <label for="monto">Monto (Bs)</label>
            <input id="monto" type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" required>
        </div>
        <div>
            <label for="metodo">Método</label>
            <select id="metodo" name="metodo">
                @foreach (['efectivo', 'tarjeta', 'transferencia'] as $metodo)
                    <option value="{{ $metodo }}" @selected(old('metodo') == $metodo)>{{ ucfirst($metodo) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="cita_id">Cita (opcional)</label>
            <select id="cita_id" name="cita_id">
                <option value="">Sin cita</option>
                @foreach ($citas as $c)
                    <option value="{{ $c->id }}" @selected(old('cita_id') == $c->id)>{{ $c->inicio->format('d/m/Y H:i') }} - {{ $c->odontologo->apellido }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex: 0 0 auto;"><button class="boton verde" type="submit">Registrar pago</button></div>
    </form>

    <h2>Últimas citas</h2>
    <table>
        <tr><th>Fecha</th><th>Odontólogo</th><th>Estado</th><th></th></tr>
        @forelse ($citas as $c)
            <tr>
                <td>{{ $c->inicio->format('d/m/Y H:i') }}</td>
                <td>{{ $c->odontologo->nombre }} {{ $c->odontologo->apellido }}</td>
                <td><span class="estado {{ $c->estado }}">{{ str_replace('_', ' ', $c->estado) }}</span></td>
                <td><a class="boton chico" href="{{ route('citas.show', $c) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="vacio">Sin citas.</td></tr>
        @endforelse
    </table>
@endsection
