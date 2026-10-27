<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOdontologoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // El checkbox "activo" llega solo cuando está marcado
    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->boolean('activo')]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:odontologos,email'],
            'especialidad' => ['required', Rule::in(['ortodoncia', 'endodoncia', 'odontopediatria'])],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ya existe un odontólogo con ese correo.',
            'especialidad.in' => 'La especialidad debe ser ortodoncia, endodoncia u odontopediatría.',
        ];
    }
}
