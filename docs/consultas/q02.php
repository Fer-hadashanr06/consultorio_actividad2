// RELACIONES + LÓGICA: agenda de un odontólogo (citas con su paciente) y bloques libres de 30 minutos
$odontologo = Odontologo::where('especialidad', 'ortodoncia')->orderBy('id')->first();
$fecha = now()->addDay()->startOfDay();
while ($odontologo->horarios()->where('dia_semana', $fecha->dayOfWeekIso)->doesntExist()) {
    $fecha->addDay();
}
echo 'Dr(a). ' . $odontologo->nombre . ' ' . $odontologo->apellido . ' - ' . $fecha->toDateString() . PHP_EOL;
$citas = $odontologo->citas()->with('paciente')->whereDate('inicio', $fecha->toDateString())->orderBy('inicio')->get();
foreach ($citas as $c) {
    echo ' ' . $c->inicio->format('H:i') . '-' . $c->fin->format('H:i') . ' | ' . $c->estado . ' | ' . $c->paciente->nombre . ' ' . $c->paciente->apellido . PHP_EOL;
}
$libres = $odontologo->bloquesLibres($fecha);
echo 'Bloques libres (' . count($libres) . '): ' . implode(', ', $libres) . PHP_EOL;
// Comprobación: ninguna hora ocupada aparece como libre
$ocupadas = $citas->where('estado', '!=', 'no_asistida')->map(fn ($c) => $c->inicio->format('H:i'));
echo 'Horas ocupadas que figuran como libres: ' . count(array_intersect($ocupadas->all(), $libres)) . PHP_EOL;
