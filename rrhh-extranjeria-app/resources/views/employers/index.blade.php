@extends('_layouts.app')

@section('title', 'employers')
@section('content')
<h1>Lista de Employers</h1>
<form action="{{ route('employers.create') }}" >
    <button type="submit">Nuevo Empleador/Cliente</button>
</form>

<ul>
    @foreach ($employers as $employer)
        <li>
            {{$employer->legal_name}} | {{$employer->legal_type}} | 
            <a href="{{ route('employer.show',$employer->id) }}">Ver Detalles</a>
            <a href="{{ route('employer.edit', $employer->id) }}">Editar</a>
            <a href="#">Eliminar</a>
        </li>
    @endforeach
</ul>
@endsection