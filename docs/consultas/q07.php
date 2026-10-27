// REGLAS DE NEGOCIO - CICLO DE VIDA DE UNA CITA: crear, confirmar, reprogramar y marcar como no asistida
DB::beginTransaction();
try {
    // 1) Crear una cita válida: la hora de fin se calcula sola
    $odontologo = Odontologo::where('especialidad', 'endodoncia')->orderBy('id')->first();
    $fecha = now()->addDays(20)->startOfDay();
    while (count($odontologo->bloquesLibres($fecha)) == 0) {
        $fecha->addDay();
    }
    $hora = $odontologo->bloquesLibres($fecha)[0];
    $paciente = Paciente::orderBy('id')->first();
    $cita = Cita::create(['paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id, 'inicio' => $fecha->toDateString() . ' ' . $hora, 'bloques' => 2]);
    echo '1) Cita creada: ' . $cita->inicio->format('Y-m-d H:i') . ' -> ' . $cita->fin->format('H:i') . ' (' . $cita->bloques . ' bloques, ' . $cita->estado . ')' . PHP_EOL;

    // 2) Confirmar (y rechazar una segunda confirmación)
    $cita->confirmar();
    echo '2) Estado tras confirmar: ' . $cita->fresh()->estado . PHP_EOL;
    try {
        $cita->confirmar();
    } catch (DomainException $e) {
        echo '   Segunda confirmacion -> RECHAZADA: ' . $e->getMessage() . PHP_EOL;
    }

    // 3) Reprogramar: guarda la fecha original y cuenta la reprogramación
    $nueva = $fecha->copy()->addDays(7);
    while (count($odontologo->bloquesLibres($nueva)) == 0) {
        $nueva->addDay();
    }
    $cita->reprogramar($nueva->toDateString() . ' ' . $odontologo->bloquesLibres($nueva)[0]);
    $cita = $cita->fresh();
    echo '3) Reprogramada a ' . $cita->inicio->format('Y-m-d H:i') . ' | estado: ' . $cita->estado . ' | original: ' . $cita->inicio_original->format('Y-m-d H:i') . ' | reprogramaciones: ' . $cita->reprogramaciones . PHP_EOL;
    try {
        $cita->reprogramar(now()->next(Carbon\Carbon::SUNDAY)->setTime(10, 0));
    } catch (DomainException $e) {
        echo '   Reprogramar a domingo -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
        echo '   La cita sigue en: ' . $cita->fresh()->inicio->format('Y-m-d H:i') . PHP_EOL;
    }

    // 4) No asistida: solo si ya pasó, y libera el bloque
    $fechaPasada = now()->subDays(30)->startOfDay();
    while (count($odontologo->bloquesLibres($fechaPasada)) == 0) {
        $fechaPasada->subDay();
    }
    $horaPasada = $odontologo->bloquesLibres($fechaPasada)[0];
    $pasada = Cita::create(['paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id, 'inicio' => $fechaPasada->toDateString() . ' ' . $horaPasada]);
    echo '4) Cita pasada ' . $pasada->inicio->format('Y-m-d H:i') . ' | bloque libre antes: ' . (in_array($horaPasada, $odontologo->bloquesLibres($fechaPasada)) ? 'si' : 'no') . PHP_EOL;
    $pasada->marcarNoAsistida();
    echo '   Estado: ' . $pasada->fresh()->estado . ' | bloque libre despues: ' . (in_array($horaPasada, $odontologo->bloquesLibres($fechaPasada)) ? 'si' : 'no') . PHP_EOL;
    try {
        Cita::where('estado', 'agendada')->where('inicio', '>', now())->orderBy('id')->first()->marcarNoAsistida();
    } catch (DomainException $e) {
        echo '   Marcar una cita futura -> RECHAZADO: ' . $e->getMessage() . PHP_EOL;
    }
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
}
DB::rollBack();
