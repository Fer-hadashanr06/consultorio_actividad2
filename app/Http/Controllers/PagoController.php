<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Paciente;
use DomainException;

class PagoController extends Controller
{
    public function store(StorePagoRequest $request, Paciente $paciente)
    {
        $datos = $request->validated();

        try {
            $paciente->registrarPago((float) $datos['monto'], $datos['metodo'], $datos['cita_id'] ?? null);
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['regla' => $e->getMessage()]);
        }

        return back()->with('exito', 'Pago registrado.');
    }
}
