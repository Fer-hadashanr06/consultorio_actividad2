<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioAtencion extends Model
{
    use HasFactory;

    protected $table = 'horarios_atencion';

    protected $fillable = [
        'odontologo_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    public const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function odontologo(): BelongsTo
    {
        return $this->belongsTo(Odontologo::class, 'odontologo_id');
    }

    public function nombreDia(): string
    {
        return self::DIAS[$this->dia_semana];
    }
}
