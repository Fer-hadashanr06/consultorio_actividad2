<?php $__env->startSection('titulo', 'Odontólogos'); ?>
<?php $__env->startSection('contenido'); ?>
    <div class="encabezado">
        <div><h1>Odontólogos</h1><p>Registra nuevos odontólogos y define su horario de atención.</p></div>
        <a class="boton" href="<?php echo e(route('odontologos.create')); ?>">Registrar odontólogo</a>
    </div>
    <table>
        <tr><th>Odontólogo</th><th>Especialidad</th><th>Correo</th><th class="num">Tramos</th><th class="num">Citas</th><th>Estado</th><th></th></tr>
        <?php $__empty_1 = true; $__currentLoopData = $odontologos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($o->nombre); ?> <?php echo e($o->apellido); ?></td>
                <td><?php echo e(ucfirst($o->especialidad)); ?></td>
                <td><?php echo e($o->email); ?></td>
                <td class="num"><?php echo e($o->horarios_count); ?></td>
                <td class="num"><?php echo e($o->citas_count); ?></td>
                <td><?php echo e($o->activo ? 'Activo' : 'Inactivo'); ?></td>
                <td class="acciones">
                    <a class="boton chico" href="<?php echo e(route('odontologos.show', $o)); ?>">Ver</a>
                    <a class="boton chico gris" href="<?php echo e(route('odontologos.edit', $o)); ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="vacio">No hay odontólogos registrados.</td></tr>
        <?php endif; ?>
    </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/odontologos/index.blade.php ENDPATH**/ ?>