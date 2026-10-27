<?php

namespace App\Models;

use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cita extends Model
{
    use HasFactory;

    public const BLOQUE_MINUTOS = 30;

    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'odontologo_id',
        'inicio',
        'bloques',
        'estado',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'fin' => 'datetime',
            'inicio_original' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function odontologo(): BelongsTo
    {
        return $this->belongsTo(Odontologo::class, 'odontologo_id');
    }

    public function tratamientos(): HasMany
    {
        return $this->hasMany(Tratamiento::class, 'cita_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cita_id');
    }

    protected static function booted(): void
    {
        static::saving(function (Cita $cita) {
            if ($cita->inicio == null) {
                throw new DomainException('La cita necesita una fecha y hora de inicio.');
            }

            $cita->bloques = $cita->bloques ?: 1;
            $cita->estado = $cita->estado ?: 'agendada';

            if ($cita->bloques < 1 || $cita->bloques > 4) {
                throw new DomainException('Una cita ocupa entre 1 y 4 bloques de 30 minutos.');
            }

            // La hora de fin siempre se calcula: inicio + N bloques de 30 minutos
            $cita->fin = $cita->inicio->copy()->addMinutes(self::BLOQUE_MINUTOS * $cita->bloques);

            // Solo se valida la agenda cuando algo relevante cambia
            $cambioAgenda = !$cita->exists
                || $cita->isDirty(['inicio', 'bloques', 'odontologo_id', 'paciente_id'])
                || ($cita->isDirty('estado') && $cita->getOriginal('estado') == 'no_asistida');

            // Una cita no asistida libera su bloque, por eso no se valida
            if ($cambioAgenda && $cita->estado != 'no_asistida') {
                $cita->validarAgenda();
            }
        });
    }

    private function validarAgenda(): void
    {
        $inicio = $this->inicio;
        $fin = $this->fin;

        // Regla 1: bloques de 30 minutos (hh:00 o hh:30)
        if ($inicio->minute % self::BLOQUE_MINUTOS != 0 || $inicio->second != 0) {
            throw new DomainException('La cita debe comenzar al inicio de un bloque de 30 minutos (hh:00 o hh:30).');
        }

        if ($inicio->toDateString() != $fin->toDateString()) {
            throw new DomainException('La cita debe terminar el mismo día en que comienza.');
        }

        $odontologo = Odontologo::find($this->odontologo_id);

        // Si el odontólogo no existe, la clave foránea de la BD rechazará el registro
        if ($odontologo == null) {
            return;
        }

        if (!$odontologo->activo) {
            throw new DomainException('El odontologo no esta activo.');
        }

        // Regla 2: solo dentro del horario de atención del odontólogo
        if (!$odontologo->atiendeEn($inicio, $fin)) {
            throw new DomainException('El odontologo no atiende ese dia u hora (fuera de su horario de atencion).');
        }

        // Regla 3: sin cruces para el odontólogo
        $cruceOdontologo = Cita::where('odontologo_id', $this->odontologo_id)
            ->where('estado', '!=', 'no_asistida')
            ->where('inicio', '<', $fin)
            ->where('fin', '>', $inicio)
            ->when($this->exists, fn ($consulta) => $consulta->where('id', '!=', $this->id))
            ->exists();

        if ($cruceOdontologo) {
            throw new DomainException('El odontologo ya tiene una cita en ese horario.');
        }

        // Regla 4: sin cruces para el paciente
        $crucePaciente = Cita::where('paciente_id', $this->paciente_id)
            ->where('estado', '!=', 'no_asistida')
            ->where('inicio', '<', $fin)
            ->where('fin', '>', $inicio)
            ->when($this->exists, fn ($consulta) => $consulta->where('id', '!=', $this->id))
            ->exists();

        if ($crucePaciente) {
            throw new DomainException('El paciente ya tiene otra cita en ese horario.');
        }
    }

    // Agendada o reprogramada -> confirmada
    public function confirmar(): self
    {
        if (!in_array($this->estado, ['agendada', 'reprogramada'])) {
            throw new DomainException('Solo se puede confirmar una cita agendada o reprogramada (estado actual: ' . $this->estado . ').');
        }

        if ($this->inicio->isPast()) {
            throw new DomainException('No se puede confirmar una cita que ya paso.');
        }

        $this->estado = 'confirmada';
        $this->save();

        return $this;
    }

    // Cambia fecha/hora (y opcionalmente duración u odontólogo) validando las mismas reglas
    public function reprogramar($nuevoInicio, ?int $bloques = null, ?int $odontologoId = null): self
    {
        if (!in_array($this->estado, ['agendada', 'confirmada', 'reprogramada'])) {
            throw new DomainException('No se puede reprogramar una cita ' . $this->estado . '.');
        }

        $nuevoInicio = Carbon::parse($nuevoInicio);

        if ($nuevoInicio->isPast()) {
            throw new DomainException('La nueva fecha debe ser futura.');
        }

        // Se guarda la fecha original solo la primera vez
        if ($this->inicio_original == null) {
            $this->inicio_original = $this->inicio;
        }

        $this->inicio = $nuevoInicio;
        $this->bloques = $bloques ?: $this->bloques;
        $this->odontologo_id = $odontologoId ?: $this->odontologo_id;
        $this->estado = 'reprogramada';
        $this->reprogramaciones = $this->reprogramaciones + 1;

        try {
            $this->save();
        } catch (\Throwable $e) {
            // Si la regla de negocio rechaza el cambio, la cita queda como estaba
            $this->refresh();
            throw $e;
        }

        return $this;
    }

    // Solo cuando la hora de la cita ya pasó; libera el bloque en la agenda
    public function marcarNoAsistida(): self
    {
        if (!in_array($this->estado, ['agendada', 'confirmada', 'reprogramada'])) {
            throw new DomainException('Solo se puede marcar como no asistida una cita pendiente (estado actual: ' . $this->estado . ').');
        }

        if ($this->inicio->isFuture()) {
            throw new DomainException('La cita aun no ocurre, no se puede marcar como no asistida.');
        }

        $this->estado = 'no_asistida';
        $this->save();

        return $this;
    }

    // Anota un tratamiento en el historial clínico del paciente y deja la cita como atendida
    public function registrarTratamiento(string $descripcion, float $costo): Tratamiento
    {
        if (!in_array($this->estado, ['confirmada', 'atendida'])) {
            throw new DomainException('Solo se registran tratamientos en citas confirmadas o atendidas (estado actual: ' . $this->estado . ').');
        }

        if ($costo < 0) {
            throw new DomainException('El costo del tratamiento no puede ser negativo.');
        }

        $historial = $this->paciente->historial()->firstOrCreate([]);

        $tratamiento = $this->tratamientos()->create([
            'historial_clinico_id' => $historial->id,
            'descripcion' => $descripcion,
            'costo' => $costo,
        ]);

        if ($this->estado != 'atendida') {
            $this->estado = 'atendida';
            $this->save();
        }

        return $tratamiento;
    }
}
