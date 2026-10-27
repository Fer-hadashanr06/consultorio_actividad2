@php $o = $odontologo ?? null; @endphp
<div class="rejilla">
    <div>
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $o?->nombre) }}" required>
    </div>
    <div>
        <label for="apellido">Apellido</label>
        <input id="apellido" name="apellido" value="{{ old('apellido', $o?->apellido) }}" required>
    </div>
    <div>
        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email', $o?->email) }}" required>
    </div>
    <div>
        <label for="especialidad">Especialidad</label>
        <select id="especialidad" name="especialidad">
            @foreach (['ortodoncia', 'endodoncia', 'odontopediatria'] as $esp)
                <option value="{{ $esp }}" @selected(old('especialidad', $o?->especialidad) == $esp)>{{ ucfirst($esp) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>&nbsp;</label>
        <label style="font-weight: normal;">
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $o?->activo ?? true))> Activo (puede recibir citas)
        </label>
    </div>
</div>
<p><button class="boton" type="submit">Guardar</button> <a class="boton gris" href="{{ route('odontologos.index') }}">Cancelar</a></p>
