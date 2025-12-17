@extends('_layouts.app')

@section('title', 'Editar Empleado')

@section('content')

<h1>Edit Employer: {{$employerToEdit->legalName}}</h1>

<form method="POST" action="{{ route('employer.update', 1) }}">
    @csrf
    @method('PUT')
    <h3>DATOS</h3>
    <label for="legalName"><b>Nombre Legal: </b></label>
    <input type="text" name="legalName" value="{{ $employerToEdit->legalName }}"><br><br>
    <label for="nif"><b>NIF/CIF: </b></label>
    <input type="text" name="nif" value="{{ $employerToEdit->nif }}"><br><br>
    <label for="activityMain"><b>Area Actividad: </b></label>
    <input type="text" name="activityMain" value="{{ $employerToEdit->activityMain }}"><br><br>
    <label for="cnae"><b>Código CNAE: </b></label>
    <input type="text" name="cnae" value="{{ $employerToEdit->cnae }}"><br><br>
    <label for="phoneNumber"><b>Teléfono: </b></label>
    <input type="text" name="phoneNumber" value="{{ $employerToEdit->phoneNumber }}">
    <label for="email"><b>email: </b></label>
    <input type="text" name="email" value="{{ $employerToEdit->email }}"><br><br>
    <label>
        <input type="checkbox" name="isPartner" value="{{ $employerToEdit->isPartner }}"
        {{ old($employerToEdit->isPartner) ? 'checked' : '' }}>
        ¿es un socio/colaborador?
    </label>

    <h1>{{$employerToEdit->repTitle}}</h1>

    @if ($employerToEdit->legalType == 'COMPANY')
    {{-- *************************************************************** --}}
    {{--                             COMPANY                             --}}
    {{-- *************************************************************** --}}
        
    <div id="company_fields">
        <h3>🏢 Datos Específicos de Empresa</h3>
    </div>

    <label for="repNieDniPass">NIE/DNI/PASS del Representante</label>
    <input type="text" name="repNieDniPass" id="repNieDniPass" value="{{ $employerToEdit->repNieDniPass }}"><br><br>
    <label for="repTitle">Título del Representante</label>
    <input type="text" name="repTitle" id="repTitle" value="{{ $employerToEdit->repTitle }}"><br><br>
    
    <label for="legalForm">Forma Legal</label>
    <select name="legalForm" id="legalForm" required>
        <option value="{{ $employerToEdit->legalForm }}">{{ $employerToEdit->legalForm }}</option>
        @foreach (['SL', 'SRL', 'Co-Op', 'SC', 'SCR', 'S-Com', 'SLNE', 'S-Prof', 'AIE', 'UTE', 'SGR'] as $form)
        <option value="{{ $form }}" {{ old('legalForm') == $form ? 'selected' : '' }}>{{ $form }}</option>
        @endforeach
    </select>
    <br><br>
    

    @elseif($employerToEdit->legalType === 'FREELANCER')
    {{-- *************************************************************** --}}
    {{--                            FRERLANCER                           --}}
    {{-- *************************************************************** --}}

    <div id="freelancer_fields">
        <h3>👤 Datos Específicos de Autónomo</h3>
    </div>

    @endif

    <button type="submbit">Actualizar</button>
</form>

@endsection