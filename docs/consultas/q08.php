// COLLECTIONS: groupBy, countBy, map, filter, sortByDesc, pluck y sum sobre los resultados de Eloquent
$citas = Cita::with('odontologo')->get();

// 1) groupBy + countBy: citas por especialidad y estado
echo '1) Citas por especialidad y estado' . PHP_EOL;
foreach ($citas->groupBy(fn ($c) => $c->odontologo->especialidad) as $especialidad => $grupo) {
    echo ' ' . $especialidad . ' (' . $grupo->count() . '): ' . $grupo->countBy('estado')->sortKeys()->map(fn ($n, $estado) => $estado . '=' . $n)->implode(', ') . PHP_EOL;
}

// 2) filter + sortByDesc + take: pacientes con más citas no asistidas
echo '2) Pacientes con mas citas no asistidas' . PHP_EOL;
$pacientes = Paciente::withCount(['citas as no_asistidas' => fn ($q) => $q->where('estado', 'no_asistida')])->get()
    ->filter(fn ($p) => $p->no_asistidas >= 2)
    ->sortByDesc('no_asistidas')
    ->take(5);
foreach ($pacientes as $p) {
    echo ' ' . $p->nombre . ' ' . $p->apellido . ': ' . $p->no_asistidas . ' no asistidas' . PHP_EOL;
}

// 3) map + sum: ingresos por tratamientos de cada odontólogo
echo '3) Tratamientos por odontologo (Bs)' . PHP_EOL;
Odontologo::with('tratamientos')->get()
    ->map(fn ($o) => ['nombre' => $o->nombre . ' ' . $o->apellido, 'cantidad' => $o->tratamientos->count(), 'total' => $o->tratamientos->sum('costo')])
    ->sortByDesc('total')
    ->each(fn ($f) => print(' ' . $f['nombre'] . ' | ' . $f['cantidad'] . ' tratamientos | Bs ' . number_format($f['total'], 2) . PHP_EOL));

// 4) filter + pluck: citas reprogramadas más de una vez
$varias = $citas->filter(fn ($c) => $c->reprogramaciones >= 1);
echo '4) Citas reprogramadas: ' . $varias->count() . ' | odontologos involucrados: ' . $varias->pluck('odontologo.apellido')->unique()->sort()->implode(', ') . PHP_EOL;
