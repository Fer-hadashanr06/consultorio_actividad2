<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'paciente_id' => Paciente::factory(),
            'cita_id' => null,
            'monto' => fake()->randomElement([50, 100, 150, 200, 300, 500]),
            'fecha_pago' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'metodo' => fake()->randomElement(['efectivo', 'tarjeta', 'transferencia']),
        ];
    }
}
