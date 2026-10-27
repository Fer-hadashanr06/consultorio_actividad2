<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'metodo' => ['required', Rule::in(['efectivo', 'tarjeta', 'transferencia'])],
            // La cita, si se indica, debe ser una cita de este mismo paciente
            'cita_id' => ['nullable', Rule::exists('citas', 'id')->where('paciente_id', $this->route('paciente')->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.gt' => 'El monto del pago debe ser mayor a cero.',
            'metodo.in' => 'El método debe ser efectivo, tarjeta o transferencia.',
            'cita_id.exists' => 'La cita indicada no pertenece a este paciente.',
        ];
    }
}
