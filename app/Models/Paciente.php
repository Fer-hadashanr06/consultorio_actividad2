<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'ci',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
    ];

    protected function casts(): array
    {
        return ['fecha_nacimiento' => 'date'];
    }

    // Todo paciente nuevo recibe su historial clínico vacío
    protected static function booted(): void
    {
        static::created(function (Paciente $paciente) {
            $paciente->historial()->create([]);
        });
    }

    public function historial(): HasOne
    {
        return $this->hasOne(HistorialClinico::class, 'paciente_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'paciente_id');
    }

    // Tratamientos del paciente a través de su historial clínico
    public function tratamientos(): HasManyThrough
    {
        return $this->hasManyThrough(Tratamiento::class, HistorialClinico::class, 'paciente_id', 'historial_clinico_id');
    }

    public function registrarPago(float $monto, string $metodo = 'efectivo', ?int $citaId = null): Pago
    {
        if ($monto <= 0) {
            throw new DomainException('El monto del pago debe ser mayor a cero.');
        }

        return $this->pagos()->create([
            'monto' => $monto,
            'metodo' => $metodo,
            'cita_id' => $citaId,
            'fecha_pago' => now()->toDateString(),
        ]);
    }

    // Estado de cuenta: detalle de tratamientos y pagos con sus totales y el saldo
    public function estadoDeCuenta(): array
    {
        $tratamientos = $this->tratamientos()
            ->with('cita.odontologo')
            ->orderBy('tratamientos.id')
            ->get();

        $pagos = $this->pagos()->orderBy('id')->get();

        $totalTratamientos = round((float) $tratamientos->sum('costo'), 2);
        $totalPagos = round((float) $pagos->sum('monto'), 2);

        return [
            'paciente' => $this->nombre . ' ' . $this->apellido,
            'ci' => $this->ci,
            'tratamientos' => $tratamientos->map(function ($t) {
                return [
                    'fecha' => $t->cita->inicio->format('Y-m-d H:i'),
                    'odontologo' => $t->cita->odontologo->nombre . ' ' . $t->cita->odontologo->apellido,
                    'descripcion' => $t->descripcion,
                    'costo' => (float) $t->costo,
                ];
            })->all(),
            'pagos' => $pagos->map(function ($p) {
                return [
                    'fecha' => $p->fecha_pago->format('Y-m-d'),
                    'metodo' => $p->metodo,
                    'monto' => (float) $p->monto,
                ];
            })->all(),
            'total_tratamientos' => $totalTratamientos,
            'total_pagos' => $totalPagos,
            'saldo' => round($totalTratamientos - $totalPagos, 2),
        ];
    }
}
