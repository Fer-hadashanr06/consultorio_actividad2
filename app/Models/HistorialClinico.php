<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistorialClinico extends Model
{
    use HasFactory;

    protected $table = 'historiales_clinicos';

    protected $fillable = [
        'paciente_id',
        'antecedentes',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Tratamientos anotados en el historial (uno por cada realizado en una cita)
    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'historial_clinico_id');
    }
}
