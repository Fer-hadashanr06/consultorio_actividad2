<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOdontologoRequest;
use App\Http\Requests\UpdateOdontologoRequest;
use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use Illuminate\Database\QueryException;

class OdontologoController extends Controller
{
    public function index()
    {
        $odontologos = Odontologo::withCount(['horarios', 'citas'])
            ->orderBy('especialidad')
            ->orderBy('apellido')
            ->get();

        return view('odontologos.index', compact('odontologos'));
    }

    public function create()
    {
        return view('odontologos.create');
    }

    public function store(StoreOdontologoRequest $request)
    {
        $odontologo = Odontologo::create($request->validated());

        return redirect()->route('odontologos.show', $odontologo)->with('exito', 'Odontólogo registrado. Ahora agrega su horario de atención.');
    }

    public function show(Odontologo $odontologo)
    {
        $horarios = $odontologo->horarios()->orderBy('dia_semana')->orderBy('hora_inicio')->get();
        $proximasCitas = $odontologo->citas()->with('paciente')
            ->where('inicio', '>=', now())
            ->where('estado', '!=', 'no_asistida')
            ->orderBy('inicio')
            ->take(10)
            ->get();
        $dias = HorarioAtencion::DIAS;

        return view('odontologos.show', compact('odontologo', 'horarios', 'proximasCitas', 'dias'));
    }

    public function edit(Odontologo $odontologo)
    {
        return view('odontologos.edit', compact('odontologo'));
    }

    public function update(UpdateOdontologoRequest $request, Odontologo $odontologo)
    {
        $odontologo->update($request->validated());

        return redirect()->route('odontologos.show', $odontologo)->with('exito', 'Datos del odontólogo actualizados.');
    }

    public function destroy(Odontologo $odontologo)
    {
        try {
            $odontologo->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['regla' => 'No se puede eliminar al odontólogo porque tiene citas registradas. Puedes marcarlo como inactivo.']);
        }

        return redirect()->route('odontologos.index')->with('exito', 'Odontólogo eliminado junto con su horario de atención.');
    }
}
