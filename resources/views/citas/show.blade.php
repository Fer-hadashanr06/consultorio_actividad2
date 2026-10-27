@extends('layouts.app')
@section('titulo', 'Cita #' . $cita->id)
@section('contenido')
    <h1>Cita #{{ $cita->id }} <span class="estado {{ $cita->estado }}">{{ str_replace('_', ' ', $cita->estado) }}</span></h1>

    <div class="tarjeta">
        <div class="rejilla">
            <div><strong>Fecha:</strong> {{ $cita->inicio->format('d/m/Y') }}</div>
            <div><strong>Horario:</strong> {{ $cita->inicio->format('H:i') }} a {{ $cita->fin->format('H:i') }} ({{ $cita->bloques }} {{ $cita->bloques == 1 ? 'bloque' : 'bloques' }})</div>
            <div><strong>Paciente:</strong> <a href="{{ route('pacientes.show', $cita->paciente) }}">{{ $cita->paciente->nombre }} {{ $cita->paciente->apellido }}</a></div>
            <div><strong>Odontólogo:</strong> <a href="{{ route('odontologos.show', $cita->odontologo) }}">{{ $cita->odontologo->nombre }} {{ $cita->odontologo->apellido }}</a></div>
            <div><strong>Motivo:</strong> {{ $cita->motivo ?? '—' }}</div>
            @if ($cita->inicio_original)
                <div><strong>Fecha original:</strong> {{ $cita->inicio_original->format('d/m/Y H:i') }} ({{ $cita->reprogramaciones }} {{ $cita->reprogramaciones == 1 ? 'reprogramación' : 'reprogramaciones' }})</div>
            @endif
        </div>

        @if (in_array($cita->estado, ['agendada', 'confirmada', 'reprogramada']))
            <div class="acciones" style="margin-top: 14px;">
                @if (in_array($cita->estado, ['agendada', 'reprogramada']))
                    <form method="POST" action="{{ route('citas.confirmar', $cita) }}">
                        @csrf
                        <button class="boton verde" type="submit">Confirmar</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('citas.no-asistida', $cita) }}" onsubmit="return confirm('¿Marcar como no asistida? Se libera el bloque.')">
                    @csrf
                    <button class="boton rojo" type="submit">Marcar no asistida</button>
                </form>
            </div>
        @endif
    </div>

    @if (in_array($cita->estado, ['agendada', 'confirmada', 'reprogramada']))
        <h2>Reprogramar</h2>
        <form class="tarjeta fila" method="POST" action="{{ route('citas.reprogramar', $cita) }}">
            @csrf
            @method('PUT')
            <div>
                <label for="fecha">Nueva fecha</label>
                <input id="fecha" type="date" name="fecha" min="{{ now()->toDateString() }}" value="{{ old('fecha') }}" required>
            </div>
            <div>
                <label for="hora">Nueva hora</label>
                <input id="hora" type="time" name="hora" step="1800" value="{{ old('hora') }}" required>
            </div>
            <div>
                <label for="bloques">Duración</label>
                <select id="bloques" name="bloques">
                    <option value="">Igual que ahora</option>
                    @foreach ([1 => '30 minutos', 2 => '1 hora', 3 => '1 hora 30 min', 4 => '2 horas'] as $n => $texto)
                        <option value="{{ $n }}" @selected(old('bloques') == $n)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 0 0 auto;"><button class="boton" type="submit">Reprogramar</button></div>
        </form>
    @endif

    <h2>Tratamientos</h2>
    <table>
        <tr><th>Descripción</th><th class="num">Costo (Bs)</th></tr>
        @forelse ($cita->tratamientos as $t)
            <tr><td>{{ $t->descripcion }}</td><td class="num">{{ number_format($t->costo, 2) }}</td></tr>
        @empty
            <tr><td colspan="2" class="vacio">Todavía no se registraron tratamientos.</td></tr>
        @endforelse
    </table>
    @if (in_array($cita->estado, ['confirmada', 'atendida']))
        <form class="tarjeta fila" style="margin-top: 10px;" method="POST" action="{{ route('tratamientos.store', $cita) }}">
            @csrf
            <div style="flex: 3;">
                <label for="descripcion">Tratamiento realizado</label>
                <input id="descripcion" name="descripcion" value="{{ old('descripcion') }}" required>
            </div>
            <div>
                <label for="costo">Costo (Bs)</label>
                <input id="costo" type="number" step="0.01" min="0" name="costo" value="{{ old('costo') }}" required>
            </div>
            <div style="flex: 0 0 auto;"><button class="boton verde" type="submit">Registrar</button></div>
        </form>
    @else
        <p class="vacio">Solo se registran tratamientos en citas confirmadas o atendidas.</p>
    @endif

    @if ($cita->pagos->count())
        <h2>Pagos ligados a esta cita</h2>
        <table>
            <tr><th>Fecha</th><th>Método</th><th class="num">Monto (Bs)</th></tr>
            @foreach ($cita->pagos as $pago)
                <tr><td>{{ $pago->fecha_pago->format('d/m/Y') }}</td><td>{{ ucfirst($pago->metodo) }}</td><td class="num">{{ number_format($pago->monto, 2) }}</td></tr>
            @endforeach
        </table>
    @endif

    <p style="margin-top: 20px;"><a class="boton gris" href="{{ route('citas.index') }}">Volver a las citas</a></p>
@endsection
