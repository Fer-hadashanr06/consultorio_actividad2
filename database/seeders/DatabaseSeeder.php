<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\HistorialClinico;
use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use App\Models\Pago;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private array $catalogo = [
        'ortodoncia' => [
            ['Colocacion de brackets metalicos', 1800],
            ['Control mensual de ortodoncia', 200],
            ['Retenedor removible', 450],
            ['Estudio cefalometrico', 150],
        ],
        'endodoncia' => [
            ['Endodoncia unirradicular', 600],
            ['Endodoncia multirradicular', 900],
            ['Tratamiento de conducto de urgencia', 350],
            ['Reconstruccion post-endodoncia', 300],
        ],
        'odontopediatria' => [
            ['Profilaxis y aplicacion de fluor', 120],
            ['Sellantes de fosas y fisuras', 80],
            ['Pulpotomia', 250],
            ['Obturacion con resina', 150],
        ],
    ];

    public function run(): void
    {
        fake()->seed(2026);

        // Cuenta para iniciar sesión en el panel (cambiar la contraseña antes de usar en producción)
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@consultorio.com',
            'password' => 'admin12345',
        ]);

        $plantillas = [
            ['ortodoncia', [1, 2, 3, 4, 5], [['08:00', '12:00'], ['14:00', '18:00']]],
            ['ortodoncia', [1, 3, 5], [['09:00', '13:00']]],
            ['endodoncia', [1, 2, 3, 4, 5], [['08:30', '12:30'], ['15:00', '19:00']]],
            ['endodoncia', [2, 4, 6], [['08:00', '12:00']]],
            ['odontopediatria', [1, 2, 3, 4, 5], [['14:00', '18:00']]],
            ['odontopediatria', [2, 3, 4], [['08:00', '12:00'], ['14:00', '17:00']]],
        ];

        foreach ($plantillas as [$especialidad, $dias, $tramos]) {
            $odontologo = Odontologo::factory()->create([
                'especialidad' => $especialidad,
            ]);

            foreach ($dias as $dia) {
                foreach ($tramos as [$desde, $hasta]) {
                    HorarioAtencion::factory()->for($odontologo)->create([
                        'dia_semana' => $dia,
                        'hora_inicio' => $desde,
                        'hora_fin' => $hasta,
                    ]);
                }
            }
        }

        // 100 pacientes; cada uno crea su historial clínico automáticamente.
        Paciente::factory()->create([
            'ci' => '1234567 TJ',
            'nombre' => 'Carlos',
            'apellido' => 'Mamani',
        ]);

        Paciente::factory()->count(99)->create();

        // Generar citas durante 42 días, sin incluir el día actual.
        $odontologos = Odontologo::orderBy('id')->get();
        $pacientes = Paciente::orderBy('id')->get()->all();
        $hoy = now()->startOfDay();

        $creadas = 0;
        $rechazadas = 0;

        foreach (range(-24, 18) as $desplazamiento) {
            if ($desplazamiento === 0) {
                continue;
            }

            $fecha = $hoy->copy()->addDays($desplazamiento);

            foreach ($odontologos as $odontologo) {
                $libres = $odontologo->bloquesLibres($fecha);

                if (count($libres) === 0) {
                    continue;
                }

                $cantidad = min(
                    count($libres),
                    fake()->numberBetween(2, 5)
                );

                foreach (fake()->randomElements($libres, $cantidad) as $hora) {
                    $inicio = $fecha->toDateString() . ' ' . $hora;
                    $paciente = fake()->randomElement($pacientes);

                    try {
                        if ($desplazamiento < 0) {
                            $this->crearCitaPasada(
                                $odontologo,
                                $paciente,
                                $inicio
                            );
                        } else {
                            $this->crearCitaFutura(
                                $odontologo,
                                $paciente,
                                $inicio
                            );
                        }

                        $creadas++;
                    } catch (DomainException $e) {
                        $rechazadas++;
                    }
                }
            }
        }

        // Crear pagos según los tratamientos registrados.
        foreach (Paciente::orderBy('id')->get() as $paciente) {
            $total = $paciente->estadoDeCuenta()['total_tratamientos'];

            if ($total <= 0) {
                continue;
            }

            $metodo = fake()->randomElement([
                'efectivo',
                'tarjeta',
                'transferencia',
            ]);

            $azar = fake()->numberBetween(1, 100);

            if ($azar <= 50) {
                Pago::factory()->for($paciente)->create([
                    'monto' => $total,
                    'metodo' => $metodo,
                ]);
            } elseif ($azar <= 80) {
                Pago::factory()->for($paciente)->create([
                    'monto' => round($total / 2, 2),
                    'metodo' => $metodo,
                ]);
            }
        }

        $tablas = [
            ['odontologos', Odontologo::count()],
            ['horarios_atencion', HorarioAtencion::count()],
            ['pacientes', Paciente::count()],
            ['historiales_clinicos', HistorialClinico::count()],
            ['citas', Cita::count()],
            ['tratamientos', Tratamiento::count()],
            ['pagos', Pago::count()],
        ];

        $totalRegistros = array_sum(array_column($tablas, 1));

        $this->command->info(
            "Citas creadas: {$creadas} | rechazadas: {$rechazadas}"
        );

        $this->command->table(
            ['Tabla', 'Registros'],
            $tablas
        );

        $this->command->info(
            "TOTAL DE REGISTROS: {$totalRegistros}"
        );
    }

    private function crearCitaPasada(
        Odontologo $odontologo,
        Paciente $paciente,
        string $inicio
    ): void {
        $atendida = fake()->numberBetween(1, 100) <= 75;

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'odontologo_id' => $odontologo->id,
            'inicio' => $inicio,
            'estado' => $atendida ? 'confirmada' : 'agendada',
            'motivo' => 'Consulta de ' . $odontologo->especialidad,
        ]);

        if (!$atendida) {
            $cita->marcarNoAsistida();
            return;
        }

        $elegidos = fake()->randomElements(
            $this->catalogo[$odontologo->especialidad],
            fake()->numberBetween(1, 2)
        );

        foreach ($elegidos as [$descripcion, $costo]) {
            $cita->registrarTratamiento($descripcion, $costo);
        }
    }

    private function crearCitaFutura(
        Odontologo $odontologo,
        Paciente $paciente,
        string $inicio
    ): void {
        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'odontologo_id' => $odontologo->id,
            'inicio' => $inicio,
            'estado' => 'agendada',
            'motivo' => 'Consulta de ' . $odontologo->especialidad,
        ]);

        $azar = fake()->numberBetween(1, 100);

        try {
            if ($azar <= 40) {
                $cita->confirmar();
            } elseif ($azar <= 55) {
                $cita->reprogramar(
                    Carbon::parse($inicio)->addWeek()
                );
            }
        } catch (DomainException $e) {
            // Si no se puede confirmar o reprogramar, queda agendada.
        }
    }
}
