@extends('layouts.app')

@section('title', 'Crear Empleador')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h2">
            <i class="bi bi-plus-circle"></i> Crear Empleador
        </h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('employers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('employers.store') }}" method="POST" id="employerForm">
            @csrf

            <h5 class="mb-3">Datos Generales</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="legal_form" class="form-label">Forma Legal <span class="text-danger">*</span></label>
                    <select class="form-select @error('legal_form') is-invalid @enderror" id="legal_form" name="legal_form" required onchange="toggleFormType()">
                        <option value="">Seleccione...</option>
                        @foreach(\App\Enums\LegalForm::cases() as $legalForm)
                            <option value="{{ $legalForm->value }}" {{ old('legal_form') === $legalForm->value ? 'selected' : '' }}>
                                {{ $legalForm->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('legal_form')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="is_associated" class="form-label">Asociado <span class="text-danger">*</span></label>
                    <select class="form-select @error('is_associated') is-invalid @enderror" id="is_associated" name="is_associated" required>
                        <option value="1" {{ old('is_associated') == '1' ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ old('is_associated') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('is_associated')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fiscal_name" class="form-label">Nombre Fiscal <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('fiscal_name') is-invalid @enderror" id="fiscal_name" name="fiscal_name" value="{{ old('fiscal_name') }}" required>
                    @error('fiscal_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="comercial_name" class="form-label">Nombre Comercial</label>
                    <input type="text" class="form-control @error('comercial_name') is-invalid @enderror" id="comercial_name" name="comercial_name" value="{{ old('comercial_name') }}">
                    @error('comercial_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="nif" class="form-label">NIF <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nif') is-invalid @enderror" id="nif" name="nif" value="{{ old('nif') }}" maxlength="9" required>
                    @error('nif')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="ccc" class="form-label">CCC <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('ccc') is-invalid @enderror" id="ccc" name="ccc" value="{{ old('ccc') }}" maxlength="11" required>
                    @error('ccc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="cnae" class="form-label">CNAE <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('cnae') is-invalid @enderror" id="cnae" name="cnae" value="{{ old('cnae') }}" maxlength="4" required>
                    @error('cnae')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- Freelancer Fields -->
            <div id="freelancerFields" style="display: none;">
                <h5 class="mb-3">Datos del Autónomo</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="niss" class="form-label">NISS <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('niss') is-invalid @enderror" id="niss" name="niss" value="{{ old('niss') }}" maxlength="12">
                        @error('niss')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="birthdate" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate') }}">
                        @error('birthdate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Company Fields -->
            <div id="companyFields" style="display: none;">
                <h5 class="mb-3">Datos del Representante</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="representative_name" class="form-label">Nombre del Representante <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('representative_name') is-invalid @enderror" id="representative_name" name="representative_name" value="{{ old('representative_name') }}">
                        @error('representative_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="representative_title" class="form-label">Cargo del Representante <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('representative_title') is-invalid @enderror" id="representative_title" name="representative_title" value="{{ old('representative_title') }}">
                        @error('representative_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="representantive_identity_number" class="form-label">DNI del Representante <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('representantive_identity_number') is-invalid @enderror" id="representantive_identity_number" name="representantive_identity_number" value="{{ old('representantive_identity_number') }}" maxlength="9">
                        @error('representantive_identity_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('employers.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleFormType() {
        const legalForm = document.getElementById('legal_form').value;
        const freelancerFields = document.getElementById('freelancerFields');
        const companyFields = document.getElementById('companyFields');

        if (legalForm === 'Empresario Individual (Autónomo)' || 
            legalForm === 'Emprendedor de Responsabilidad Limitada') {
            freelancerFields.style.display = 'block';
            companyFields.style.display = 'none';
        } else if (legalForm !== '') {
            freelancerFields.style.display = 'none';
            companyFields.style.display = 'block';
        } else {
            freelancerFields.style.display = 'none';
            companyFields.style.display = 'none';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleFormType();
    });
</script>
@endpush
