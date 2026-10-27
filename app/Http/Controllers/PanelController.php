<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Odontologo;
use App\Models\Paciente;

class PanelController extends Controller
{
    // Resumen del día para el personal del consultorio
    public function index()
    {
        $hoy = now()->toDateString();

        $citasHoy = Cita::with(['paciente', 'odontologo'])
            ->whereDate('inicio', $hoy)
            ->where('estado', '!=', 'no_asistida')
            ->orderBy('inicio')
            ->get();

        $proximas = Cita::with(['paciente', 'odontologo'])
            ->where('inicio', '>', now()->endOfDay())
            ->whereIn('estado', ['agendada', 'confirmada', 'reprogramada'])
            ->orderBy('inicio')
            ->take(6)
            ->get();

        $porConfirmar = Cita::where('inicio', '>=', now())
            ->whereIn('estado', ['agendada', 'reprogramada'])
            ->count();

        $cifras = [
            'hoy' => $citasHoy->count(),
            'por_confirmar' => $porConfirmar,
            'pacientes' => Paciente::count(),
            'odontologos' => Odontologo::where('activo', true)->count(),
        ];

        $recientes = Paciente::latest('id')->take(5)->get();

        return view('panel', compact('citasHoy', 'proximas', 'cifras', 'recientes'));
    }
}
