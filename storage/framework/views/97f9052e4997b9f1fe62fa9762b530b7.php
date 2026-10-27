<!DOCTYPE html>
<html lang="es">
<head>
    <?php echo $__env->make('layouts._cabecera', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <title>Iniciar sesión · Consultorio odontológico</title>
</head>
<body>
<div class="acceso">
    <section class="acceso-lado">
        <a class="marca" href="<?php echo e(route('inicio')); ?>"><span class="sello"><?php echo $__env->make('layouts._diente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span> Consultorio odontológico</a>
        <div>
            <h2>Todo el consultorio en un solo lugar</h2>
            <ul>
                <li>Agenda citas en bloques libres, sin cruces de horario.</li>
                <li>Consulta pacientes, historial clínico y estado de cuenta.</li>
                <li>Registra nuevos odontólogos y define su horario de atención.</li>
            </ul>
        </div>
        <span style="color:#9fb6c7;font-size:13px">Acceso solo para el personal autorizado.</span>
    </section>
    <section class="acceso-form">
        <form method="POST" action="<?php echo e(route('login.enviar')); ?>" novalidate>
            <?php echo csrf_field(); ?>
            <h1>Iniciar sesión</h1>
            <p class="nota">Ingresa con tu cuenta del consultorio.</p>
            <?php if($errors->any()): ?>
                <div class="alerta error" role="alert">
                    <ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mensaje): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($mensaje); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                </div>
            <?php endif; ?>
            <div class="campo">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="username" autofocus required>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <div class="recordar">
                <input id="recordar" type="checkbox" name="recordar" value="1">
                <label for="recordar">Mantener la sesión iniciada</label>
            </div>
            <button class="boton" type="submit">Entrar</button>
            <a class="volver" href="<?php echo e(route('inicio')); ?>">Volver al inicio</a>
        </form>
    </section>
</div>
</body>
</html>
<?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/auth/login.blade.php ENDPATH**/ ?>