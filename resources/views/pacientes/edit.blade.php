@extends('layouts.app')
@section('titulo', 'Editar paciente')
@section('contenido')
    <h1>Editar paciente</h1>
    <form class="tarjeta" method="POST" action="{{ route('pacientes.update', $paciente) }}">
        @csrf
        @method('PUT')
        @include('pacientes._form')
    </form>
@endsection
