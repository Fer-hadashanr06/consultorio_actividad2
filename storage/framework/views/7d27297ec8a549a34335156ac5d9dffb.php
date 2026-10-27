<?php $__env->startSection('titulo', 'Nueva cita'); ?>
<?php $__env->startSection('contenido'); ?>
    <h1>Nueva cita</h1>

    <h2>1. Elige el odontólogo y la fecha</h2>
    <form class="tarjeta fila" method="GET" action="<?php echo e(route('citas.create')); ?>">
        <input type="hidden" name="paciente_id" value="<?php echo e(request('paciente_id')); ?>">
        <div>
            <label for="odontologo_id">Odontólogo</label>
            <select id="odontologo_id" name="odontologo_id" required>
                <option value="">Selecciona...</option>
                <?php $__currentLoopData = $odontologos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($o->id); ?>" <?php if(request('odontologo_id') == $o->id): echo 'selected'; endif; ?>><?php echo e($o->nombre); ?> <?php echo e($o->apellido); ?> (<?php echo e($o->especialidad); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label for="fecha">Fecha</label>
            <input id="fecha" type="date" name="fecha" min="<?php echo e(now()->toDateString()); ?>" value="<?php echo e(request('fecha')); ?>" required>
        </div>
        <div style="flex: 0 0 auto;"><button class="boton" type="submit">Ver bloques libres</button></div>
    </form>

    <?php if($libres !== null): ?>
        <h2>2. Elige un bloque libre y el paciente</h2>
        <?php if(count($libres) == 0): ?>
            <div class="tarjeta vacio">El Dr(a). <?php echo e($odontologo->apellido); ?> no tiene bloques libres ese día (no atiende o está completo).</div>
        <?php else: ?>
            <form class="tarjeta" method="POST" action="<?php echo e(route('citas.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="odontologo_id" value="<?php echo e($odontologo->id); ?>">
                <input type="hidden" name="fecha" value="<?php echo e(request('fecha')); ?>">
                <p><strong><?php echo e($odontologo->nombre); ?> <?php echo e($odontologo->apellido); ?></strong> · <?php echo e(\Carbon\Carbon::parse(request('fecha'))->format('d/m/Y')); ?></p>
                <label>Hora de inicio</label>
                <div class="bloques">
                    <?php $__currentLoopData = $libres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $hora): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label><input type="radio" name="hora" value="<?php echo e($hora); ?>" <?php if(old('hora', $libres[0]) == $hora): echo 'checked'; endif; ?> required><?php echo e($hora); ?></label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="rejilla" style="margin-top: 14px;">
                    <div>
                        <label for="paciente_id">Paciente</label>
                        <select id="paciente_id" name="paciente_id" required>
                            <option value="">Selecciona...</option>
                            <?php $__currentLoopData = $pacientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php if(old('paciente_id', request('paciente_id')) == $p->id): echo 'selected'; endif; ?>><?php echo e($p->apellido); ?>, <?php echo e($p->nombre); ?> (<?php echo e($p->ci); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="bloques">Duración</label>
                        <select id="bloques" name="bloques">
                            <?php $__currentLoopData = [1 => '30 minutos', 2 => '1 hora', 3 => '1 hora 30 min', 4 => '2 horas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $texto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($n); ?>" <?php if(old('bloques', 1) == $n): echo 'selected'; endif; ?>><?php echo e($texto); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="motivo">Motivo</label>
                        <input id="motivo" name="motivo" value="<?php echo e(old('motivo')); ?>" placeholder="Control, dolor de muela...">
                    </div>
                </div>
                <p><button class="boton verde" type="submit">Agendar cita</button></p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\serra\Documents\Universidad\Tecnologia web I\Proyectofinal_nueva_interfaz_v2\consultorio_actividad2\resources\views/citas/create.blade.php ENDPATH**/ ?>