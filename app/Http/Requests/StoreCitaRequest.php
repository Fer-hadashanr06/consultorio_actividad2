<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'odontologo_id' => ['required', 'exists:odontologos,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'bloques' => ['required', 'integer', 'between:1,4'],
            'motivo' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'odontologo_id.exists' => 'El odontólogo seleccionado no existe.',
            'fecha.after_or_equal' => 'La fecha de la cita no puede ser anterior a hoy.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'bloques.between' => 'La cita puede durar de 1 a 4 bloques de 30 minutos.',
        ];
    }
}
