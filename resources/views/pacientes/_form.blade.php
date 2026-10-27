@php $p = $paciente ?? null; @endphp
<div class="rejilla">
    <div>
        <label for="ci">CI</label>
        <input id="ci" name="ci" value="{{ old('ci', $p?->ci) }}" placeholder="1234567 TJ" required>
    </div>
    <div>
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $p?->nombre) }}" required>
    </div>
    <div>
        <label for="apellido">Apellido</label>
        <input id="apellido" name="apellido" value="{{ old('apellido', $p?->apellido) }}" required>
    </div>
    <div>
        <label for="fecha_nacimiento">Fecha de nacimiento</label>
        <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $p?->fecha_nacimiento?->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label for="telefono">Teléfono</label>
        <input id="telefono" name="telefono" value="{{ old('telefono', $p?->telefono) }}">
    </div>
    <div>
        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email', $p?->email) }}">
    </div>
    <div>
        <label for="direccion">Dirección</label>
        <input id="direccion" name="direccion" value="{{ old('direccion', $p?->direccion) }}">
    </div>
</div>
<p><button class="boton" type="submit">Guardar</button> <a class="boton gris" href="{{ route('pacientes.index') }}">Cancelar</a></p>
