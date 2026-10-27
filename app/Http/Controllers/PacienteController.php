<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Paciente;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->query('q');

        $pacientes = Paciente::withCount('citas')
            ->when($buscar, function ($consulta) use ($buscar) {
                $consulta->where(function ($q) use ($buscar) {
                    $q->where('ci', 'like', "%$buscar%")
                        ->orWhere('nombre', 'like', "%$buscar%")
                        ->orWhere('apellido', 'like', "%$buscar%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'buscar'));
    }

    public function create()
    {
        return view('pacientes.create');
    }

    public function store(StorePacienteRequest $request)
    {
        $paciente = Paciente::create($request->validated());

        return redirect()->route('pacientes.show', $paciente)->with('exito', 'Paciente registrado. Su historial clínico fue creado.');
    }

    public function show(Paciente $paciente)
    {
        $cuenta = $paciente->estadoDeCuenta();
        $citas = $paciente->citas()->with('odontologo')->orderByDesc('inicio')->take(10)->get();

        return view('pacientes.show', compact('paciente', 'cuenta', 'citas'));
    }

    public function edit(Paciente $paciente)
    {
        return view('pacientes.edit', compact('paciente'));
    }

    public function update(UpdatePacienteRequest $request, Paciente $paciente)
    {
        $paciente->update($request->validated());

        return redirect()->route('pacientes.show', $paciente)->with('exito', 'Datos del paciente actualizados.');
    }

    public function destroy(Paciente $paciente)
    {
        try {
            DB::transaction(function () use ($paciente) {
                $paciente->delete();
            });
        } catch (QueryException $e) {
            return back()->withErrors([
                'regla' => 'No se puede eliminar al paciente porque tiene citas, tratamientos o pagos registrados.'
            ]);
        }

        return redirect()->route('pacientes.index')
            ->with('exito', 'Paciente eliminado.');
    }
}
