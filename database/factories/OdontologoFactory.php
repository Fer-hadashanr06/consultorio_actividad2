<?php

namespace Database\Factories;

use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use Illuminate\Database\Eloquent\Factories\Factory;

class OdontologoFactory extends Factory
{
    protected $model = Odontologo::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'especialidad' => fake()->randomElement(['ortodoncia', 'endodoncia', 'odontopediatria']),
            'activo' => true,
        ];
    }

    // Odontólogo que atiende de lunes a viernes de 08:00 a 12:00 y de 14:00 a 18:00
    public function conHorarioCompleto(): static
    {
        return $this->afterCreating(function (Odontologo $odontologo) {
            foreach (range(1, 5) as $dia) {
                foreach ([['08:00:00', '12:00:00'], ['14:00:00', '18:00:00']] as [$desde, $hasta]) {
                    HorarioAtencion::factory()->for($odontologo)->create([
                        'dia_semana' => $dia,
                        'hora_inicio' => $desde,
                        'hora_fin' => $hasta,
                    ]);
                }
            }
        });
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
