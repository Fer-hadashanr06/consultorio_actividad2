<?php $__env->startSection('titulo', 'Nuevo paciente'); ?>
<?php $__env->startSection('contenido'); ?>
    <h1>Nuevo paciente</h1>
    <form class="tarjeta" method="POST" action="<?php echo e(route('pacientes.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('pacientes._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/pacientes/create.blade.php ENDPATH**/ ?>