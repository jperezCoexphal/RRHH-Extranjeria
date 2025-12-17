@extends('_layouts.app')

@section('title', 'Detalle Empleado')

@section('content')

<h1>Detalles del empleado: <em>{{ $employerDetail->legalName }}</em></h1>
<p>Datos</p>
<span>Nombre legal: <b>{{$employerDetail->legalName}}</b></span><br>
<span>Tipo Empleador: <b>{{$employerDetail->legalType}}</b></span><br>
<span>NIF: <b>{{$employerDetail->nif}}</b></span><br>
<span>Sector: <b>{{$employerDetail->activityMain}}</b></span><br>
<span>Contacto: <b>{{$employerDetail->phoneNumber}} - {{$employerDetail->email}}</b></span><br>

<a href="{{ route('employers.index') }}">Volver al listado</a>

@endsection