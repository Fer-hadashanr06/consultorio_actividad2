<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            // CI boliviana: 7 dígitos + extensión del departamento
            'ci' => fake()->unique()->numerify('#######') . ' ' . fake()->randomElement(['TJ', 'LP', 'CB', 'SC', 'OR', 'PT', 'CH']),
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-70 years', '-3 years')->format('Y-m-d'),
            'telefono' => fake()->numerify(fake()->randomElement(['7#######', '6#######'])),
            'email' => fake()->unique()->safeEmail(),
            'direccion' => fake()->streetAddress(),
        ];
    }
}
