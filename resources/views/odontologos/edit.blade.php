@extends('layouts.app')
@section('titulo', 'Editar odontólogo')
@section('contenido')
    <h1>Editar odontólogo</h1>
    <form class="tarjeta" method="POST" action="{{ route('odontologos.update', $odontologo) }}">
        @csrf
        @method('PUT')
        @include('odontologos._form')
    </form>
@endsection
