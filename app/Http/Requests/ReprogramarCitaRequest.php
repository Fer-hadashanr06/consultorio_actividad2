<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReprogramarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'bloques' => ['nullable', 'integer', 'between:1,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.after_or_equal' => 'La nueva fecha no puede ser anterior a hoy.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'bloques.between' => 'La cita puede durar de 1 a 4 bloques de 30 minutos.',
        ];
    }
}
