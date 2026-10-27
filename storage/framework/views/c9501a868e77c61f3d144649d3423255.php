<!DOCTYPE html>
<html lang="es">
<head>
    <?php echo $__env->make('layouts._cabecera', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <title><?php echo $__env->yieldContent('titulo', 'Consultorio odontológico'); ?></title>
</head>
<body>
    <header class="barra">
        <div class="contenedor">
            <a class="marca" href="<?php echo e(route('inicio')); ?>"><span class="sello"><?php echo $__env->make('layouts._diente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span> Consultorio odontológico</a>
            <nav aria-label="Principal">
                <a href="<?php echo e(route('inicio')); ?>#que-es">La odontología</a>
                <a href="<?php echo e(route('inicio')); ?>#especialidades">Especialidades</a>
                <a href="<?php echo e(route('inicio')); ?>#cuidado">Cuidado diario</a>
                <a href="<?php echo e(route('inicio')); ?>#preguntas">Preguntas</a>
                <?php if(auth()->guard()->check()): ?>
                    <a class="boton" href="<?php echo e(route('panel')); ?>">Ir al panel</a>
                <?php else: ?>
                    <a class="boton" href="<?php echo e(route('login')); ?>">Iniciar sesión</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <?php echo $__env->yieldContent('contenido'); ?>
    <footer class="pie-publico">
        <div class="contenedor">
            <span>Consultorio odontológico · Sistema de citas</span>
            <span>La información de esta página es orientativa y no reemplaza una consulta con tu odontólogo.</span>
        </div>
    </footer>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/layouts/publico.blade.php ENDPATH**/ ?>