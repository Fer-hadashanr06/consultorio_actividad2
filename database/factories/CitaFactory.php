<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Odontologo;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaFactory extends Factory
{
    protected $model = Cita::class;

    public function definition(): array
    {
        return [
            'paciente_id' => Paciente::factory(),
            // Odontólogo nuevo que atiende de lunes a viernes (así la cita siempre cae dentro de su horario)
            'odontologo_id' => Odontologo::factory()->conHorarioCompleto(),
            'inicio' => $this->horaValida(now()->addWeek()),
            'bloques' => 1,
            'estado' => 'agendada',
            'motivo' => fake()->randomElement([
                'Control general',
                'Dolor de muela',
                'Limpieza dental',
                'Revision de ortodoncia',
                'Sensibilidad dental',
                'Evaluacion de conducto',
            ]),
        ];
    }

    // Día laborable de la semana de $referencia, hora de inicio de un bloque de 30 minutos
    // (08:00-11:30 o 14:00-17:30, que están dentro del horario completo)
    private function horaValida(Carbon $referencia): Carbon
    {
        return $referencia->copy()
            ->startOfWeek()
            ->addDays(fake()->numberBetween(0, 4))
            ->setTime(fake()->randomElement([8, 9, 10, 11, 14, 15, 16, 17]), fake()->randomElement([0, 30]), 0);
    }

    public function confirmada(): static
    {
        return $this->state(['estado' => 'confirmada']);
    }

    // Cita de la semana pasada ya atendida
    public function atendida(): static
    {
        return $this->state(fn () => [
            'inicio' => $this->horaValida(now()->subWeek()),
            'estado' => 'atendida',
        ]);
    }

    // Cita de la semana pasada a la que el paciente no asistió
    public function noAsistida(): static
    {
        return $this->state(fn () => [
            'inicio' => $this->horaValida(now()->subWeek()),
            'estado' => 'no_asistida',
        ]);
    }

    public function reprogramada(): static
    {
        return $this->state(function (array $atributos) {
            return [
                'estado' => 'reprogramada',
                'inicio_original' => $atributos['inicio']->copy()->subWeek(),
                'reprogramaciones' => 1,
            ];
        });
    }
}
