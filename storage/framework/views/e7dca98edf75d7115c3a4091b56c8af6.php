<?php $__env->startSection('titulo', 'Panel'); ?>
<?php $__env->startSection('contenido'); ?>
    <div class="encabezado">
        <div>
            <h1>Hola, <?php echo e(\Illuminate\Support\Str::before(auth()->user()->name, ' ')); ?></h1>
            <p><?php echo e(now()->translatedFormat('l j \d\e F \d\e Y')); ?></p>
        </div>
        <div class="atajos">
            <a class="boton" href="<?php echo e(route('citas.create')); ?>">Registrar cita</a>
            <a class="boton gris" href="<?php echo e(route('pacientes.create')); ?>">Nuevo paciente</a>
            <a class="boton gris" href="<?php echo e(route('odontologos.create')); ?>">Registrar odontólogo</a>
        </div>
    </div>

    <div class="cifras">
        <a class="cifra" href="<?php echo e(route('citas.index', ['fecha' => now()->toDateString()])); ?>"><b><?php echo e($cifras['hoy']); ?></b><span>citas hoy</span></a>
        <a class="cifra <?php echo e($cifras['por_confirmar'] > 0 ? 'alerta-c' : ''); ?>" href="<?php echo e(route('citas.index', ['estado' => 'agendada'])); ?>"><b><?php echo e($cifras['por_confirmar']); ?></b><span>por confirmar</span></a>
        <a class="cifra" href="<?php echo e(route('pacientes.index')); ?>"><b><?php echo e($cifras['pacientes']); ?></b><span>pacientes registrados</span></a>
        <a class="cifra" href="<?php echo e(route('odontologos.index')); ?>"><b><?php echo e($cifras['odontologos']); ?></b><span>odontólogos activos</span></a>
    </div>

    <div class="columnas">
        <div>
            <h2>Agenda de hoy</h2>
            <table>
                <tr><th>Hora</th><th>Paciente</th><th>Odontólogo</th><th>Estado</th><th></th></tr>
                <?php $__empty_1 = true; $__currentLoopData = $citasHoy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($c->inicio->format('H:i')); ?>–<?php echo e($c->fin->format('H:i')); ?></td>
                        <td><?php echo e($c->paciente->nombre); ?> <?php echo e($c->paciente->apellido); ?></td>
                        <td>Dr(a). <?php echo e($c->odontologo->apellido); ?></td>
                        <td><span class="estado <?php echo e($c->estado); ?>"><?php echo e(str_replace('_', ' ', $c->estado)); ?></span></td>
                        <td><a class="boton chico" href="<?php echo e(route('citas.show', $c)); ?>">Ver</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="vacio">No hay citas para hoy. <a href="<?php echo e(route('citas.create')); ?>">Registrar una</a>.</td></tr>
                <?php endif; ?>
            </table>

            <h2>Próximas citas</h2>
            <table>
                <tr><th>Fecha</th><th>Paciente</th><th>Odontólogo</th><th></th></tr>
                <?php $__empty_1 = true; $__currentLoopData = $proximas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($c->inicio->format('d/m H:i')); ?></td>
                        <td><?php echo e($c->paciente->nombre); ?> <?php echo e($c->paciente->apellido); ?></td>
                        <td>Dr(a). <?php echo e($c->odontologo->apellido); ?></td>
                        <td><a class="boton chico" href="<?php echo e(route('citas.show', $c)); ?>">Ver</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="vacio">No hay citas próximas.</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <div>
            <h2>Últimos pacientes</h2>
            <div class="tarjeta">
                <ul class="lista-simple">
                    <?php $__empty_1 = true; $__currentLoopData = $recientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li>
                            <div><a href="<?php echo e(route('pacientes.show', $p)); ?>"><?php echo e($p->apellido); ?>, <?php echo e($p->nombre); ?></a><small>CI <?php echo e($p->ci); ?></small></div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="vacio">Todavía no hay pacientes.</li>
                    <?php endif; ?>
                </ul>
                <p style="margin:14px 0 0"><a class="boton chico gris" href="<?php echo e(route('pacientes.index')); ?>">Ver todos</a></p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/panel.blade.php ENDPATH**/ ?>