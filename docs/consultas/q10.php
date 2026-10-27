// ESTADO DE CUENTA y SALDOS: tratamientos, pagos y deudas (relación hasManyThrough + agregaciones + Collections)
DB::beginTransaction();
try {
    $cita = Cita::where('estado', 'confirmada')->orderBy('id')->first();
    $paciente = $cita->paciente;
    $cita->registrarTratamiento('Limpieza dental', 150);
    $cita->registrarTratamiento('Obturacion con resina', 200);
    $paciente->registrarPago(250, 'tarjeta');
    $cuenta = $paciente->fresh()->estadoDeCuenta();
    echo 'Estado de cuenta: ' . $cuenta['paciente'] . ' (CI ' . $cuenta['ci'] . ')' . PHP_EOL;
    echo 'Tratamientos (los 2 ultimos):' . PHP_EOL;
    foreach (array_slice($cuenta['tratamientos'], -2) as $t) {
        echo ' - ' . $t['fecha'] . ' | ' . $t['odontologo'] . ' | ' . $t['descripcion'] . ' | Bs ' . number_format($t['costo'], 2) . PHP_EOL;
    }
    echo 'Pagos:' . PHP_EOL;
    foreach ($cuenta['pagos'] as $p) {
        echo ' - ' . $p['fecha'] . ' | ' . $p['metodo'] . ' | Bs ' . number_format($p['monto'], 2) . PHP_EOL;
    }
    echo 'Total tratamientos: Bs ' . number_format($cuenta['total_tratamientos'], 2) . PHP_EOL;
    echo 'Total pagos: Bs ' . number_format($cuenta['total_pagos'], 2) . PHP_EOL;
    echo 'Saldo: Bs ' . number_format($cuenta['saldo'], 2) . PHP_EOL;
    try {
        Cita::where('estado', 'no_asistida')->orderBy('id')->first()->registrarTratamiento('Intento en cita no asistida', 100);
    } catch (DomainException $e) {
        echo 'Tratamiento en cita no asistida -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
    }
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
}
DB::rollBack();

// Pacientes con saldo pendiente (agregaciones con withSum + Collections)
$deudores = Paciente::withSum('tratamientos', 'costo')->withSum('pagos', 'monto')->get()
    ->map(fn ($p) => ['paciente' => $p->nombre . ' ' . $p->apellido, 'saldo' => (float) $p->tratamientos_sum_costo - (float) $p->pagos_sum_monto])
    ->filter(fn ($fila) => $fila['saldo'] > 0)
    ->sortByDesc('saldo');
echo 'Pacientes con saldo pendiente: ' . $deudores->count() . ' | deuda total: Bs ' . number_format($deudores->sum('saldo'), 2) . PHP_EOL;
foreach ($deudores->take(5) as $fila) {
    echo ' - ' . $fila['paciente'] . ' | Bs ' . number_format($fila['saldo'], 2) . PHP_EOL;
}
