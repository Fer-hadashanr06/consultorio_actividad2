// INTEGRIDAD - CLAVES FORÁNEAS: restrictOnDelete, cascadeOnDelete y nullOnDelete
$pg = fn ($e) => trim(preg_replace("/\s+/", " ", $e->errorInfo[2] ?? $e->getMessage()));

// 1) Insertar una cita con un paciente que no existe (el modelo no lo revisa; lo rechaza la clave foránea)
$odontologo = Odontologo::where('especialidad', 'endodoncia')->orderBy('id')->first();
$fecha = now()->addDays(40)->startOfDay();
while (count($odontologo->bloquesLibres($fecha)) == 0) {
    $fecha->addDay();
}
DB::beginTransaction();
try {
    Cita::create(['paciente_id' => 999999, 'odontologo_id' => $odontologo->id, 'inicio' => $fecha->toDateString() . ' ' . $odontologo->bloquesLibres($fecha)[0]]);
    echo '1) cita con paciente inexistente: se guardo (no deberia)' . PHP_EOL;
} catch (Illuminate\Database\QueryException $e) {
    echo '1) Cita con paciente inexistente -> RECHAZADO: ' . $pg($e) . PHP_EOL;
}
DB::rollBack();

// 2) Borrar un paciente que ya tiene citas (RESTRICT)
DB::beginTransaction();
try {
    $conCitas = Paciente::has('citas')->orderBy('id')->first();
    $conCitas->delete();
    echo '2) borrar paciente con citas: se borro (no deberia)' . PHP_EOL;
} catch (Illuminate\Database\QueryException $e) {
    echo '2) Borrar paciente con citas -> RECHAZADO: ' . $pg($e) . PHP_EOL;
}
DB::rollBack();

// 3) Borrar un odontólogo sin citas: sus horarios se borran en cascada (CASCADE)
DB::beginTransaction();
$nuevo = Odontologo::create(['nombre' => 'Temporal', 'apellido' => 'Prueba', 'email' => 'temporal@prueba.test', 'especialidad' => 'ortodoncia']);
$nuevo->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);
$nuevo->horarios()->create(['dia_semana' => 2, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);
echo '3) Horarios del odontologo temporal antes de borrarlo: ' . HorarioAtencion::where('odontologo_id', $nuevo->id)->count() . PHP_EOL;
$nuevo->delete();
echo '   Horarios despues de borrarlo (cascade): ' . HorarioAtencion::where('odontologo_id', $nuevo->id)->count() . PHP_EOL;
DB::rollBack();

// 4) Borrar una cita que tiene un pago asociado: el pago se conserva y su cita_id queda en NULL (SET NULL)
DB::beginTransaction();
$odontologo = Odontologo::where('especialidad', 'ortodoncia')->orderBy('id')->first();
$fecha = now()->addDays(50)->startOfDay();
while (count($odontologo->bloquesLibres($fecha)) == 0) {
    $fecha->addDay();
}
$paciente = Paciente::orderBy('id')->first();
$cita = Cita::create(['paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id, 'inicio' => $fecha->toDateString() . ' ' . $odontologo->bloquesLibres($fecha)[0]]);
$pago = $paciente->registrarPago(100, 'efectivo', $cita->id);
echo '4) Pago #' . $pago->id . ' ligado a la cita #' . $cita->id . PHP_EOL;
$cita->delete();
echo '   Tras borrar la cita, el pago existe: ' . (Pago::find($pago->id) ? 'si' : 'no') . ' | cita_id = ' . var_export(Pago::find($pago->id)->cita_id, true) . PHP_EOL;
DB::rollBack();
