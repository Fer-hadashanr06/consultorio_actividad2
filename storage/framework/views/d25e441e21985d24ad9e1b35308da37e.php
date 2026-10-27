<?php $p = $paciente ?? null; ?>
<div class="rejilla">
    <div>
        <label for="ci">CI</label>
        <input id="ci" name="ci" value="<?php echo e(old('ci', $p?->ci)); ?>" placeholder="1234567 TJ" required>
    </div>
    <div>
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" value="<?php echo e(old('nombre', $p?->nombre)); ?>" required>
    </div>
    <div>
        <label for="apellido">Apellido</label>
        <input id="apellido" name="apellido" value="<?php echo e(old('apellido', $p?->apellido)); ?>" required>
    </div>
    <div>
        <label for="fecha_nacimiento">Fecha de nacimiento</label>
        <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="<?php echo e(old('fecha_nacimiento', $p?->fecha_nacimiento?->format('Y-m-d'))); ?>" required>
    </div>
    <div>
        <label for="telefono">Teléfono</label>
        <input id="telefono" name="telefono" value="<?php echo e(old('telefono', $p?->telefono)); ?>">
    </div>
    <div>
        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="<?php echo e(old('email', $p?->email)); ?>">
    </div>
    <div>
        <label for="direccion">Dirección</label>
        <input id="direccion" name="direccion" value="<?php echo e(old('direccion', $p?->direccion)); ?>">
    </div>
</div>
<p><button class="boton" type="submit">Guardar</button> <a class="boton gris" href="<?php echo e(route('pacientes.index')); ?>">Cancelar</a></p>
<?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/pacientes/_form.blade.php ENDPATH**/ ?>