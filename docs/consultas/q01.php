// RELACIONES: odontólogos con su horario de atención (hasMany / belongsTo) usando carga anticipada
$odontologos = Odontologo::with('horarios')->orderBy('especialidad')->orderBy('apellido')->get();
foreach ($odontologos as $o) {
    echo $o->nombre . ' ' . $o->apellido . ' (' . $o->especialidad . ') - ' . $o->horarios->count() . ' tramos' . PHP_EOL;
    foreach ($o->horarios->sortBy([['dia_semana', 'asc'], ['hora_inicio', 'asc']])->take(3) as $h) {
        echo '   ' . $h->nombreDia() . ' ' . substr($h->hora_inicio, 0, 5) . '-' . substr($h->hora_fin, 0, 5) . PHP_EOL;
    }
}
// La relación inversa: un horario pertenece a un odontólogo
$h = HorarioAtencion::with('odontologo')->orderBy('id')->first();
echo 'Horario #' . $h->id . ' (' . $h->nombreDia() . ') pertenece a ' . $h->odontologo->apellido . PHP_EOL;
