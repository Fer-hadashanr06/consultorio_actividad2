@extends('layouts.app')
@section('titulo', 'Nueva cita')
@section('contenido')
    <h1>Nueva cita</h1>

    <h2>1. Elige el odontólogo y la fecha</h2>
    <form class="tarjeta fila" method="GET" action="{{ route('citas.create') }}">
        <input type="hidden" name="paciente_id" value="{{ request('paciente_id') }}">
        <div>
            <label for="odontologo_id">Odontólogo</label>
            <select id="odontologo_id" name="odontologo_id" required>
                <option value="">Selecciona...</option>
                @foreach ($odontologos as $o)
                    <option value="{{ $o->id }}" @selected(request('odontologo_id') == $o->id)>{{ $o->nombre }} {{ $o->apellido }} ({{ $o->especialidad }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="fecha">Fecha</label>
            <input id="fecha" type="date" name="fecha" min="{{ now()->toDateString() }}" value="{{ request('fecha') }}" required>
        </div>
        <div style="flex: 0 0 auto;"><button class="boton" type="submit">Ver bloques libres</button></div>
    </form>

    @if ($libres !== null)
        <h2>2. Elige un bloque libre y el paciente</h2>
        @if (count($libres) == 0)
            <div class="tarjeta vacio">El Dr(a). {{ $odontologo->apellido }} no tiene bloques libres ese día (no atiende o está completo).</div>
        @else
            <form class="tarjeta" method="POST" action="{{ route('citas.store') }}">
                @csrf
                <input type="hidden" name="odontologo_id" value="{{ $odontologo->id }}">
                <input type="hidden" name="fecha" value="{{ request('fecha') }}">
                <p><strong>{{ $odontologo->nombre }} {{ $odontologo->apellido }}</strong> · {{ \Carbon\Carbon::parse(request('fecha'))->format('d/m/Y') }}</p>
                <label>Hora de inicio</label>
                <div class="bloques">
                    @foreach ($libres as $i => $hora)
                        <label><input type="radio" name="hora" value="{{ $hora }}" @checked(old('hora', $libres[0]) == $hora) required>{{ $hora }}</label>
                    @endforeach
                </div>
                <div class="rejilla" style="margin-top: 14px;">
                    <div>
                        <label for="paciente_id">Paciente</label>
                        <select id="paciente_id" name="paciente_id" required>
                            <option value="">Selecciona...</option>
                            @foreach ($pacientes as $p)
                                <option value="{{ $p->id }}" @selected(old('paciente_id', request('paciente_id')) == $p->id)>{{ $p->apellido }}, {{ $p->nombre }} ({{ $p->ci }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bloques">Duración</label>
                        <select id="bloques" name="bloques">
                            @foreach ([1 => '30 minutos', 2 => '1 hora', 3 => '1 hora 30 min', 4 => '2 horas'] as $n => $texto)
                                <option value="{{ $n }}" @selected(old('bloques', 1) == $n)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="motivo">Motivo</label>
                        <input id="motivo" name="motivo" value="{{ old('motivo') }}" placeholder="Control, dolor de muela...">
                    </div>
                </div>
                <p><button class="boton verde" type="submit">Agendar cita</button></p>
            </form>
        @endif
    @endif
@endsection
