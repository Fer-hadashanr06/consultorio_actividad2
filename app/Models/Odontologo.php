<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Odontologo extends Model
{
    use HasFactory;

    protected $table = 'odontologos';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'especialidad',
        'activo',
    ];

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioAtencion::class, 'odontologo_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'odontologo_id');
    }

    // Tratamientos realizados por el odontólogo, a través de sus citas
    public function tratamientos(): HasManyThrough
    {
        return $this->hasManyThrough(Tratamiento::class, Cita::class, 'odontologo_id', 'cita_id');
    }

    // ¿El horario de atención cubre completamente el rango [inicio, fin]?
    public function atiendeEn(CarbonInterface $inicio, CarbonInterface $fin): bool
    {
        return $this->horarios()
            ->where('dia_semana', $inicio->dayOfWeekIso)
            ->where('hora_inicio', '<=', $inicio->format('H:i:s'))
            ->where('hora_fin', '>=', $fin->format('H:i:s'))
            ->exists();
    }

    // Horas de inicio (HH:MM) de los bloques de 30 minutos libres en una fecha
    public function bloquesLibres(CarbonInterface $fecha): array
    {
        $dia = $fecha->toDateString();

        $ocupadas = $this->citas()
            ->where('estado', '!=', 'no_asistida')
            ->whereDate('inicio', $dia)
            ->get();

        $libres = [];

        foreach ($this->horarios()->where('dia_semana', $fecha->dayOfWeekIso)->orderBy('hora_inicio')->get() as $tramo) {
            $cursor = Carbon::parse($dia . ' ' . $tramo->hora_inicio);
            $limite = Carbon::parse($dia . ' ' . $tramo->hora_fin);

            while ($cursor->copy()->addMinutes(Cita::BLOQUE_MINUTOS)->lte($limite)) {
                $finBloque = $cursor->copy()->addMinutes(Cita::BLOQUE_MINUTOS);

                $ocupado = $ocupadas->contains(function ($cita) use ($cursor, $finBloque) {
                    return $cita->inicio->lt($finBloque) && $cita->fin->gt($cursor);
                });

                if (!$ocupado) {
                    $libres[] = $cursor->format('H:i');
                }

                $cursor->addMinutes(Cita::BLOQUE_MINUTOS);
            }
        }

        return $libres;
    }
}
