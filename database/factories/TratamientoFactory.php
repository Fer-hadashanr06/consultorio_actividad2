<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Tratamiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TratamientoFactory extends Factory
{
    protected $model = Tratamiento::class;

    public function definition(): array
    {
        return [
            'cita_id' => Cita::factory()->atendida(),
            // El tratamiento se anota en el historial del paciente dueño de la cita
            'historial_clinico_id' => fn (array $atributos) => Cita::find($atributos['cita_id'])->paciente->historial->id,
            'descripcion' => fake()->randomElement([
                'Profilaxis y aplicacion de fluor',
                'Obturacion con resina',
                'Endodoncia unirradicular',
                'Control mensual de ortodoncia',
                'Sellantes de fosas y fisuras',
                'Retenedor removible',
            ]),
            'costo' => fake()->randomElement([80, 120, 150, 200, 250, 300, 450, 600, 900]),
        ];
    }
}
