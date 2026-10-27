@extends('layouts.app')
@section('titulo', 'Nuevo paciente')
@section('contenido')
    <h1>Nuevo paciente</h1>
    <form class="tarjeta" method="POST" action="{{ route('pacientes.store') }}">
        @csrf
        @include('pacientes._form')
    </form>
@endsection
