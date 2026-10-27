<?php

namespace App\Http\Controllers;

use App\Models\Odontologo;

class InicioController extends Controller
{
    // Portada pública: explica qué es la odontología y presenta las especialidades del consultorio
    public function index()
    {
        $activos = Odontologo::where('activo', true)->orderBy('apellido')->get()->groupBy('especialidad');

        $especialidades = [
            'ortodoncia' => [
                'nombre' => 'Ortodoncia',
                'descripcion' => 'Corrige la posición de los dientes y de la mordida para masticar mejor y limpiar con más facilidad.',
                'casos' => ['Dientes apiñados o separados', 'Mordida cruzada o abierta', 'Brackets y retenedores'],
            ],
            'endodoncia' => [
                'nombre' => 'Endodoncia',
                'descripcion' => 'Trata el interior del diente cuando la pulpa se inflama o se infecta, para salvar la pieza y quitar el dolor.',
                'casos' => ['Dolor de muela intenso', 'Tratamiento de conducto', 'Reconstrucción después del conducto'],
            ],
            'odontopediatria' => [
                'nombre' => 'Odontopediatría',
                'descripcion' => 'Atiende a bebés, niños y adolescentes, con foco en prevenir caries y en que pierdan el miedo al consultorio.',
                'casos' => ['Primera visita y controles', 'Flúor y sellantes', 'Caries en dientes de leche'],
            ],
        ];

        return view('inicio', compact('activos', 'especialidades'));
    }
}
