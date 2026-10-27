// INTEGRIDAD - RESTRICCIONES DE LA BASE DE DATOS (CHECK y EXCLUDE de PostgreSQL)
// Se inserta con DB::table() para saltarse el modelo: aquí solo puede responder la base de datos
$restriccion = function ($e) {
    preg_match('/constraint "([^"]+)"/', $e->errorInfo[2] ?? $e->getMessage(), $m);
    return $m[1] ?? trim(preg_replace("/\s+/", " ", $e->getMessage()));
};
$fmt = fn ($f) => $f->format('Y-m-d H:i:s');
$sello = ['created_at' => now(), 'updated_at' => now()];
$odontologo = Odontologo::where('especialidad', 'ortodoncia')->orderBy('id')->first();
$paciente = Paciente::orderBy('id')->first();
$otroPaciente = Paciente::where('id', '!=', $paciente->id)->orderBy('id')->first();
$atendida = Cita::where('estado', 'atendida')->orderBy('id')->first();
$futura = Cita::where('estado', '!=', 'no_asistida')->where('inicio', '>', now())->orderBy('id')->first();
$otroOdontologo = Odontologo::where('id', '!=', $futura->odontologo_id)->orderBy('id')->first();
$lejano = now()->addDays(90)->setTime(9, 0, 0);   // un día lejano, sin citas

// Inserta una cita directamente en la tabla
$cita = fn ($pac, $odo, $inicio, $bloques, $fin, $estado = 'agendada') => DB::table('citas')->insert([
    'paciente_id' => $pac, 'odontologo_id' => $odo, 'inicio' => $fmt($inicio),
    'bloques' => $bloques, 'fin' => $fmt($fin), 'estado' => $estado,
] + $sello);
$mas = fn ($f, $min) => $f->copy()->addMinutes($min);

$intentos = [
    'Cita de 9 bloques (maximo 4)' => fn () => $cita($paciente->id, $odontologo->id, $lejano, 9, $mas($lejano, 270)),
    'Fin distinto de inicio + bloques' => fn () => $cita($paciente->id, $odontologo->id, $lejano, 1, $mas($lejano, 120)),
    'Inicio a las hh:15 (no es bloque)' => fn () => $cita($paciente->id, $odontologo->id, $mas($lejano, 15), 1, $mas($lejano, 45)),
    'Estado que no existe' => fn () => $cita($paciente->id, $odontologo->id, $lejano, 1, $mas($lejano, 30), 'inventado'),
    'Cruce del odontologo (EXCLUDE)' => fn () => $cita($otroPaciente->id, $futura->odontologo_id, $futura->inicio, 1, $mas($futura->inicio, 30)),
    'Cruce del paciente (EXCLUDE)' => fn () => $cita($futura->paciente_id, $otroOdontologo->id, $futura->inicio, 1, $mas($futura->inicio, 30)),
    'Pago con monto 0' => fn () => DB::table('pagos')->insert(
        ['paciente_id' => $paciente->id, 'monto' => 0, 'fecha_pago' => now()->toDateString(), 'metodo' => 'efectivo'] + $sello),
    'Pago con metodo que no existe' => fn () => DB::table('pagos')->insert(
        ['paciente_id' => $paciente->id, 'monto' => 50, 'fecha_pago' => now()->toDateString(), 'metodo' => 'bitcoin'] + $sello),
    'Tratamiento con costo negativo' => fn () => DB::table('tratamientos')->insert(
        ['cita_id' => $atendida->id, 'historial_clinico_id' => $atendida->paciente->historial->id, 'descripcion' => 'Prueba', 'costo' => -5] + $sello),
    'Horario con hora_fin <= hora_inicio' => fn () => DB::table('horarios_atencion')->insert(
        ['odontologo_id' => $odontologo->id, 'dia_semana' => 7, 'hora_inicio' => '12:00', 'hora_fin' => '08:00'] + $sello),
    'Horario en el dia 9 de la semana' => fn () => DB::table('horarios_atencion')->insert(
        ['odontologo_id' => $odontologo->id, 'dia_semana' => 9, 'hora_inicio' => '08:00', 'hora_fin' => '12:00'] + $sello),
];
foreach ($intentos as $nombre => $accion) {
    DB::beginTransaction();
    try {
        $accion();
        echo $nombre . ': se guardo (no deberia)' . PHP_EOL;
    } catch (Illuminate\Database\QueryException $e) {
        echo $nombre . ' -> RECHAZADO por: ' . $restriccion($e) . PHP_EOL;
    }
    DB::rollBack();
}
