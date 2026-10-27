// AGREGACIONES: count, sum, avg y having directamente en SQL
echo '1) COUNT: citas por estado' . PHP_EOL;
foreach (Cita::selectRaw('estado, count(*) as total')->groupBy('estado')->orderByDesc('total')->get() as $f) {
    echo ' ' . $f->estado . ': ' . $f->total . PHP_EOL;
}

echo '2) SUM: pagos recibidos por metodo (Bs)' . PHP_EOL;
foreach (Pago::selectRaw('metodo, count(*) as pagos, sum(monto) as total')->groupBy('metodo')->orderByDesc('total')->get() as $f) {
    echo ' ' . $f->metodo . ': ' . $f->pagos . ' pagos | Bs ' . number_format($f->total, 2) . PHP_EOL;
}

echo '3) AVG: costo promedio de un tratamiento por especialidad (Bs)' . PHP_EOL;
$promedios = Tratamiento::join('citas', 'citas.id', '=', 'tratamientos.cita_id')
    ->join('odontologos', 'odontologos.id', '=', 'citas.odontologo_id')
    ->selectRaw('odontologos.especialidad, count(*) as cantidad, round(avg(tratamientos.costo), 2) as promedio, max(tratamientos.costo) as maximo')
    ->groupBy('odontologos.especialidad')->orderByDesc('promedio')->get();
foreach ($promedios as $f) {
    echo ' ' . $f->especialidad . ': ' . $f->cantidad . ' tratamientos | promedio Bs ' . $f->promedio . ' | maximo Bs ' . $f->maximo . PHP_EOL;
}

echo '4) HAVING: odontologos con 20 o mas citas atendidas' . PHP_EOL;
$minimo = 20;
$atendidas = Cita::join('odontologos', 'odontologos.id', '=', 'citas.odontologo_id')
    ->where('citas.estado', 'atendida')
    ->selectRaw("odontologos.nombre || ' ' || odontologos.apellido as odontologo, count(*) as atendidas")
    ->groupBy('odontologos.id', 'odontologos.nombre', 'odontologos.apellido')
    ->havingRaw('count(*) >= ?', [$minimo])
    ->orderByDesc('atendidas')->get();
foreach ($atendidas as $f) {
    echo ' ' . $f->odontologo . ': ' . $f->atendidas . ' atendidas' . PHP_EOL;
}
echo ' (' . $atendidas->count() . ' de ' . Odontologo::count() . ' odontologos cumplen)' . PHP_EOL;
