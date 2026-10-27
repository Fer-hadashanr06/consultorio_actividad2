<?php

namespace Database\Factories;

use App\Models\HistorialClinico;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialClinicoFactory extends Factory
{
    protected $model = HistorialClinico::class;

    public function definition(): array
    {
        return [
            // Todo paciente nuevo ya recibe su historial por el evento "created" del modelo.
            // Aquí se crea el paciente sin ese evento para que este factory sea quien genere el historial
            // (la relación es 1 a 1: un segundo historial para el mismo paciente violaría el UNIQUE).
            'paciente_id' => fn () => Paciente::withoutEvents(fn () => Paciente::factory()->create())->id,
            'antecedentes' => fake()->optional(0.7)->randomElement([
                'Sin antecedentes de relevancia.',
                'Alergia a la penicilina.',
                'Hipertension controlada.',
                'Diabetes tipo 2 en tratamiento.',
                'Bruxismo nocturno.',
                'Extraccion de terceros molares en 2022.',
                'Sensibilidad dental al frio.',
            ]),
        ];
    }
}
