<!DOCTYPE html>
<html lang="es">
<head>
    <?php echo $__env->make('layouts._cabecera', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <title><?php echo $__env->yieldContent('titulo', 'Panel'); ?> · Consultorio odontológico</title>
</head>
<body>
<?php
    $iconos = [
        'panel' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'citas' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'pacientes' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.870M16 3.130a4 4 0 0 1 0 7.750"/>',
        'odontologos' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
        'mas' => '<path d="M12 5v14M5 12h14"/>',
        'salir' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    ];
    $icono = fn ($n) => new \Illuminate\Support\HtmlString('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $iconos[$n] . '</svg>');
?>
<div class="panel-app">
    <aside class="lateral">
        <a class="marca" href="<?php echo e(route('inicio')); ?>"><span class="sello"><?php echo $__env->make('layouts._diente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span> Consultorio</a>
        <nav aria-label="Panel">
            <a href="<?php echo e(route('panel')); ?>" class="<?php echo e(request()->routeIs('panel') ? 'activo' : ''); ?>"><?php echo e($icono('panel')); ?> Panel</a>
            <a href="<?php echo e(route('citas.index')); ?>" class="<?php echo e(request()->routeIs('citas.index', 'citas.show') ? 'activo' : ''); ?>"><?php echo e($icono('citas')); ?> Citas</a>
            <a href="<?php echo e(route('pacientes.index')); ?>" class="<?php echo e(request()->routeIs('pacientes.*') ? 'activo' : ''); ?>"><?php echo e($icono('pacientes')); ?> Pacientes</a>
            <a href="<?php echo e(route('odontologos.index')); ?>" class="<?php echo e(request()->routeIs('odontologos.*') ? 'activo' : ''); ?>"><?php echo e($icono('odontologos')); ?> Odontólogos</a>
        </nav>
        <div class="nuevo"><a class="boton" href="<?php echo e(route('citas.create')); ?>"><?php echo e($icono('mas')); ?> Agendar cita</a></div>
        <div class="sesion">
            <div><b><?php echo e(auth()->user()->name); ?></b><span><?php echo e(auth()->user()->email); ?></span></div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"><?php echo e($icono('salir')); ?> Cerrar sesión</button>
            </form>
        </div>
    </aside>
    <main class="area">
        <div class="area-interior">
            <?php if(session('exito')): ?>
                <div class="alerta exito" role="status"><?php echo e(session('exito')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alerta error" role="alert">
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mensaje): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($mensaje); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('contenido'); ?>
        </div>
    </main>
</div>
</body>
</html>
<?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/layouts/app.blade.php ENDPATH**/ ?>