<?php

namespace Database\Factories;

use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use Illuminate\Database\Eloquent\Factories\Factory;

class HorarioAtencionFactory extends Factory
{
    protected $model = HorarioAtencion::class;

    public function definition(): array
    {
        // Tramos de atención típicos de un consultorio (mañana o tarde)
        $tramo = fake()->randomElement([
            ['08:00:00', '12:00:00'],
            ['09:00:00', '13:00:00'],
            ['14:00:00', '18:00:00'],
            ['15:00:00', '19:00:00'],
        ]);

        return [
            'odontologo_id' => Odontologo::factory(),
            'dia_semana' => fake()->numberBetween(1, 5), // lunes a viernes
            'hora_inicio' => $tramo[0],
            'hora_fin' => $tramo[1],
        ];
    }
}
