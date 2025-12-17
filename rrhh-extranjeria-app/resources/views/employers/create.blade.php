@extends('_layouts.app')

@section('title', 'Crear Empleado')

@section('content')
<form method="POST" action="{{ route('employers.store') }}">
    @csrf

    <h2>📋 Detalles Generales del Empleador (Tabla `employers`)</h2>

    <div class="form-group">
        <label for="legal_type">Tipo Legal</label>
        {{-- Guardamos el tipo legal para usarlo en la lógica condicional --}}
        @php
            $selectedType = 'legal_type';
        @endphp
        
        <select name="legal_type" id="legal_type" >
            <option value="" {{ $selectedType === null ? 'selected' : '' }}>Selecciona el Tipo</option>
            <option value="COMPANY" {{ $selectedType === 'COMPANY' ? 'selected' : '' }}>Empresa</option>
            <option value="FREELANCER" {{ $selectedType === 'FREELANCER' ? 'selected' : '' }}>Autónomo</option>
        </select>
        @error('legal_type') <span>{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="nif">NIF/CIF</label>
        <input type="text" name="nif" id="nif" maxlength="9" required value="N1234567P">
        @error('nif') <span>{{ $message }}</span> @enderror
    </div>
    
    {{-- ... Otros campos de la tabla `employers` (legal_name, activity_main, etc.) ... --}}
    
    <div class="form-group">
        <label for="legal_name">Razón Social / Nombre Legal</label>
        <input type="text" name="legal_name" id="legal_name" required value="Cooperativa Yondale">
        @error('legal_name') <span>{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="activity_main">Actividad Principal</label>
        <input type="text" name="activity_main" id="activity_main" required value="Agricultura">
        @error('activity_main') <span>{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="cnae">CNAE (Opcional)</label>
        <input type="text" name="cnae" id="cnae" maxlength="5" value="0113">
        @error('cnae') <span>{{ $message }}</span> @enderror
    </div>
    
    {{-- ... Otros campos (phone_number, email, is_partner) ... --}}
    
    <div class="form-group">
        <label for="phone_number">Teléfono (Opcional)</label>
        <input type="tel" name="phone_number" id="phone_number" value="950505050">
    </div>

    <div class="form-group">
        <label for="email">Email (Opcional)</label>
        <input type="email" name="email" id="email" value="yondale@mail.com">
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_partner" value="1" {{ 'is_partner' ? 'checked' : '' }}>
            ¿Es un socio/colaborador?
        </label>
    </div>

    <hr>

    {{-- *************************************************************** --}}
    {{--              VISUALIZACIÓN CONDICIONAL CON BLADE: FREELANCER    --}}
    {{-- *************************************************************** --}}
        <div id="freelancer_fields">
            <h3>👤 Datos Específicos de Autónomo (Tabla `freelancers`)</h3>

            <div class="form-group">
                <label for="birthdate">Fecha de Nacimiento</label>
                <input type="date" name="birthdate" id="birthdate" value="" required>
                @error('birthdate') <span>{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="niss">NISS</label>
                <input type="text" name="niss" id="niss" maxlength="12" value="289876543298" required>
                @error('niss') <span>{{ $message }}</span> @enderror
            </div>
        </div>
    

    {{-- *************************************************************** --}}
    {{--              VISUALIZACIÓN CONDICIONAL CON BLADE: COMPANY                --}}
    {{-- *************************************************************** --}}
  
        <div id="company_fields">
            <h3>🏢 Datos Específicos de Empresa (Tabla `companies`)</h3>

            <div class="form-group">
                <label for="rep_nie_dni_pass">NIE/DNI/Pasaporte del Representante</label>
                <input type="text" name="rep_nie_dni_pass" id="rep_nie_dni_pass" value="87654321M" required>
                @error('rep_nie_dni_pass') <span>{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="rep_title">Cargo del Representante</label>
                <input type="text" name="rep_title" id="rep_title" value="Chief Executive Eggy" required>
                @error('rep_title') <span>{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="legal_form">Forma Legal</label>
                <select name="legal_form" id="legal_form" required>
                    <option value="">Selecciona la forma legal</option>
                    @foreach (['SL', 'SRL', 'Co-Op', 'SC', 'SCR', 'S-Com', 'SLNE', 'S-Prof', 'AIE', 'UTE', 'SGR'] as $form)
                        <option value="{{ $form }}" {{ old('legal_form') == $form ? 'selected' : '' }}>{{ $form }}</option>
                    @endforeach
                </select>
                @error('legal_form') <span>{{ $message }}</span> @enderror
            </div>
        </div>
   


    <button type="submit">Crear Empleador</button>
</form>

@endsection