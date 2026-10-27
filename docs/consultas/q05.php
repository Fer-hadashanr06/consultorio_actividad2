// INTEGRIDAD - REGLAS DE NEGOCIO DE LA AGENDA (validadas por el modelo Cita): horario, bloques, cruces y odontólogo inactivo
$odontologo = Odontologo::where('especialidad', 'ortodoncia')->orderBy('id')->first();
$paciente = Paciente::orderBy('id')->first();
$domingo = now()->next(Carbon\Carbon::SUNDAY)->setTime(10, 0);
$laboral = now()->addDays(30)->startOfDay();
while ($odontologo->horarios()->where('dia_semana', $laboral->dayOfWeekIso)->doesntExist()) {
    $laboral->addDay();
}
$apertura = $odontologo->horarios()->where('dia_semana', $laboral->dayOfWeekIso)->orderBy('hora_inicio')->first();
$intentos = [
    'Domingo 10:00 (no atiende)' => $domingo,
    'Antes de abrir' => $laboral->copy()->setTimeFromTimeString($apertura->hora_inicio)->subMinutes(30),
    'A mitad de bloque (hh:15)' => $laboral->copy()->setTimeFromTimeString($apertura->hora_inicio)->addMinutes(15),
];
foreach ($intentos as $nombre => $inicio) {
    DB::beginTransaction();
    try {
        Cita::create(['paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id, 'inicio' => $inicio]);
        echo $nombre . ': se creo (no deberia)' . PHP_EOL;
    } catch (DomainException $e) {
        echo $nombre . ' -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
    }
    DB::rollBack();
}

// Cruces: mismo odontólogo en el mismo bloque y mismo paciente en dos consultorios a la vez
$existente = Cita::where('estado', '!=', 'no_asistida')->where('inicio', '>', now())->orderBy('id')->first();
echo 'Cita existente: ' . $existente->odontologo->apellido . ' ' . $existente->inicio->format('Y-m-d H:i') . PHP_EOL;
DB::beginTransaction();
try {
    Cita::create(['paciente_id' => Paciente::where('id', '!=', $existente->paciente_id)->orderBy('id')->first()->id, 'odontologo_id' => $existente->odontologo_id, 'inicio' => $existente->inicio]);
} catch (DomainException $e) {
    echo 'Mismo odontologo, mismo bloque -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
}
DB::rollBack();
DB::beginTransaction();
try {
    Cita::create(['paciente_id' => $existente->paciente_id, 'odontologo_id' => Odontologo::where('id', '!=', $existente->odontologo_id)->orderBy('id')->first()->id, 'inicio' => $existente->inicio]);
} catch (DomainException $e) {
    echo 'Mismo paciente, otro odontologo -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
}
DB::rollBack();

// Odontólogo inactivo
DB::beginTransaction();
try {
    $odontologo->update(['activo' => false]);
    Cita::create(['paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id, 'inicio' => $laboral->copy()->setTimeFromTimeString($apertura->hora_inicio)]);
} catch (DomainException $e) {
    echo 'Odontologo inactivo -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
}
DB::rollBack();
