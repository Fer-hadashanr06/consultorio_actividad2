<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReprogramarCitaRequest;
use App\Http\Requests\StoreCitaRequest;
use App\Models\Cita;
use App\Models\Odontologo;
use App\Models\Paciente;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        $citas = Cita::with(['paciente', 'odontologo'])
            ->when($request->query('fecha'), fn ($q, $fecha) => $q->whereDate('inicio', $fecha))
            ->when($request->query('odontologo_id'), fn ($q, $id) => $q->where('odontologo_id', $id))
            ->when($request->query('estado'), fn ($q, $estado) => $q->where('estado', $estado))
            ->orderByDesc('inicio')
            ->paginate(20)
            ->withQueryString();

        $odontologos = Odontologo::orderBy('apellido')->get();
        $estados = ['agendada', 'confirmada', 'reprogramada', 'atendida', 'no_asistida'];

        return view('citas.index', compact('citas', 'odontologos', 'estados'));
    }

    // Paso 1: elegir odontólogo y fecha; paso 2: elegir uno de los bloques libres
    public function create(Request $request)
    {
        $pacientes = Paciente::orderBy('apellido')->orderBy('nombre')->get();
        $odontologos = Odontologo::where('activo', true)->orderBy('apellido')->get();

        $libres = null;
        $odontologo = null;
        if ($request->filled('odontologo_id') && $request->filled('fecha')) {
            $odontologo = Odontologo::find($request->query('odontologo_id'));
            if ($odontologo) {
                $libres = $odontologo->bloquesLibres(Carbon::parse($request->query('fecha')));
            }
        }

        return view('citas.create', compact('pacientes', 'odontologos', 'odontologo', 'libres'));
    }

    public function store(StoreCitaRequest $request)
    {
        $datos = $request->validated();

        try {
            $cita = Cita::create([
                'paciente_id' => $datos['paciente_id'],
                'odontologo_id' => $datos['odontologo_id'],
                'inicio' => $datos['fecha'] . ' ' . $datos['hora'],
                'bloques' => $datos['bloques'],
                'motivo' => $datos['motivo'] ?? null,
            ]);
        } catch (DomainException $e) {
            // Regla de negocio del modelo (horario, bloques, cruces, odontólogo activo)
            return back()->withInput()->withErrors(['regla' => $e->getMessage()]);
        } catch (QueryException $e) {
            // Restricción de la base de datos (por ejemplo, dos personas agendando el mismo bloque a la vez)
            return back()->withInput()->withErrors(['regla' => 'No se pudo agendar: el horario ya no está disponible.']);
        }

        return redirect()->route('citas.show', $cita)->with('exito', 'Cita agendada.');
    }

    public function show(Cita $cita)
    {
        $cita->load(['paciente', 'odontologo', 'tratamientos', 'pagos']);

        return view('citas.show', compact('cita'));
    }

    public function confirmar(Cita $cita)
    {
        return $this->cambiarEstado($cita, fn () => $cita->confirmar(), 'Cita confirmada.');
    }

    public function reprogramar(ReprogramarCitaRequest $request, Cita $cita)
    {
        $datos = $request->validated();

        return $this->cambiarEstado(
            $cita,
            fn () => $cita->reprogramar($datos['fecha'] . ' ' . $datos['hora'], $datos['bloques'] ?? null),
            'Cita reprogramada.'
        );
    }

    public function noAsistida(Cita $cita)
    {
        return $this->cambiarEstado($cita, fn () => $cita->marcarNoAsistida(), 'La cita se marcó como no asistida y el bloque quedó libre.');
    }

    // Ejecuta el cambio y muestra la regla de negocio que lo rechace
    private function cambiarEstado(Cita $cita, callable $accion, string $mensaje)
    {
        try {
            $accion();
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['regla' => $e->getMessage()]);
        } catch (QueryException $e) {
            return back()->withInput()->withErrors(['regla' => 'No se pudo guardar el cambio: el horario ya no está disponible.']);
        }

        return redirect()->route('citas.show', $cita)->with('exito', $mensaje);
    }
}
