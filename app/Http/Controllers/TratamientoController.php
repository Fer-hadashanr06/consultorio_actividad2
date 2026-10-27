<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTratamientoRequest;
use App\Models\Cita;
use DomainException;

class TratamientoController extends Controller
{
    public function store(StoreTratamientoRequest $request, Cita $cita)
    {
        try {
            $cita->registrarTratamiento($request->validated('descripcion'), (float) $request->validated('costo'));
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['regla' => $e->getMessage()]);
        }

        return back()->with('exito', 'Tratamiento registrado en el historial clínico del paciente.');
    }
}
