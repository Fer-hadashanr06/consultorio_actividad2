// INTEGRIDAD - UNICIDAD: la base de datos rechaza duplicados (UNIQUE)
$pg = fn ($e) => trim(preg_replace("/\s+/", " ", $e->errorInfo[2] ?? $e->getMessage()));
$paciente = Paciente::orderBy('id')->first();
$odontologo = Odontologo::orderBy('id')->first();
$horario = $odontologo->horarios()->orderBy('id')->first();
$intentos = [
    'CI repetido' => fn () => Paciente::create(['ci' => $paciente->ci, 'nombre' => 'Prueba', 'apellido' => 'Repetido', 'fecha_nacimiento' => '2000-01-01']),
    'Email de paciente repetido' => fn () => Paciente::create(['ci' => '9999999 TJ', 'nombre' => 'Prueba', 'apellido' => 'Repetido', 'fecha_nacimiento' => '2000-01-01', 'email' => $paciente->email]),
    'Email de odontologo repetido' => fn () => Odontologo::create(['nombre' => 'Prueba', 'apellido' => 'Repetido', 'email' => $odontologo->email, 'especialidad' => 'ortodoncia']),
    'Segundo historial para el mismo paciente (1 a 1)' => fn () => HistorialClinico::create(['paciente_id' => $paciente->id]),
    'Mismo tramo horario duplicado' => fn () => HorarioAtencion::create(['odontologo_id' => $odontologo->id, 'dia_semana' => $horario->dia_semana, 'hora_inicio' => $horario->hora_inicio, 'hora_fin' => '23:00']),
];
foreach ($intentos as $nombre => $accion) {
    DB::beginTransaction();
    try {
        $accion();
        echo $nombre . ': se guardo (no deberia)' . PHP_EOL;
    } catch (Illuminate\Database\UniqueConstraintViolationException $e) {
        echo $nombre . ' -> RECHAZADO: ' . $pg($e) . PHP_EOL;
    } catch (Throwable $e) {
        echo $nombre . ' -> ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    }
    DB::rollBack();
}
