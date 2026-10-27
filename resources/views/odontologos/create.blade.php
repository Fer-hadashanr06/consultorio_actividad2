@extends('layouts.app')
@section('titulo', 'Nuevo odontólogo')
@section('contenido')
    <h1>Nuevo odontólogo</h1>
    <form class="tarjeta" method="POST" action="{{ route('odontologos.store') }}">
        @csrf
        @include('odontologos._form')
    </form>
@endsection
