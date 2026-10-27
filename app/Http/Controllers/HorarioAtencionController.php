<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHorarioRequest;
use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class HorarioAtencionController extends Controller
{
    public function store(StoreHorarioRequest $request, Odontologo $odontologo)
    {
        $datos = $request->validated();

        $duplicado = $odontologo->horarios()
            ->where('dia_semana', $datos['dia_semana'])
            ->where('hora_inicio', $datos['hora_inicio'])
            ->exists();

        if ($duplicado) {
            return back()->withInput()->withErrors([
                'regla' => 'Ese día ya tiene un tramo que comienza a la misma hora.'
            ]);
        }

        try {
            DB::transaction(function () use ($odontologo, $datos) {
                $odontologo->horarios()->create($datos);
            });
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->withErrors([
                'regla' => 'Ese día ya tiene un tramo que comienza a la misma hora.'
            ]);
        }

        return back()->with('exito', 'Tramo de atención agregado.');
    }

    public function destroy(HorarioAtencion $horario)
    {
        $horario->delete();

        return back()->with('exito', 'Tramo de atención eliminado.');
    }
}
