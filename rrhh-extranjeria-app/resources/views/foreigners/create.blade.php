@extends('layouts.app')

@section('title', 'Crear Extranjero')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h2"><i class="bi bi-plus-circle"></i> Crear Extranjero</h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('foreigners.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('foreigners.store') }}" method="POST">
            @csrf

            <h5 class="mb-3">Datos Personales</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="passport" class="form-label">Pasaporte <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('passport') is-invalid @enderror" id="passport" name="passport" value="{{ old('passport') }}" required>
                    @error('passport')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="nie" class="form-label">NIE <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nie') is-invalid @enderror" id="nie" name="nie" value="{{ old('nie') }}" maxlength="9" required>
                    @error('nie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="niss" class="form-label">NISS</label>
                    <input type="text" class="form-control @error('niss') is-invalid @enderror" id="niss" name="niss" value="{{ old('niss') }}" maxlength="12">
                    @error('niss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="gender" class="form-label">Género <span class="text-danger">*</span></label>
                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                        <option value="">Seleccione...</option>
                        @foreach(\App\Enums\Gender::cases() as $gender)
                            <option value="{{ $gender->value }}" {{ old('gender') === $gender->value ? 'selected' : '' }}>{{ $gender->value }}</option>
                        @endforeach
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="birthdate" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate') }}" required>
                    @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="nationality" class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality" value="{{ old('nationality') }}" required>
                    @error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="marital_status" class="form-label">Estado Civil <span class="text-danger">*</span></label>
                    <select class="form-select @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status" required>
                        <option value="">Seleccione...</option>
                        @foreach(\App\Enums\MaritalStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('marital_status') === $status->value ? 'selected' : '' }}>{{ $status->value }}</option>
                        @endforeach
                    </select>
                    @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Datos Adicionales</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="father_name" class="form-label">Nombre del Padre</label>
                    <input type="text" class="form-control @error('father_name') is-invalid @enderror" id="father_name" name="father_name" value="{{ old('father_name') }}">
                    @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="mother_name" class="form-label">Nombre de la Madre</label>
                    <input type="text" class="form-control @error('mother_name') is-invalid @enderror" id="mother_name" name="mother_name" value="{{ old('mother_name') }}">
                    @error('mother_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="legal_guardian_name" class="form-label">Nombre del Tutor Legal</label>
                    <input type="text" class="form-control @error('legal_guardian_name') is-invalid @enderror" id="legal_guardian_name" name="legal_guardian_name" value="{{ old('legal_guardian_name') }}">
                    @error('legal_guardian_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="legal_guardian_identity_number" class="form-label">DNI del Tutor</label>
                    <input type="text" class="form-control @error('legal_guardian_identity_number') is-invalid @enderror" id="legal_guardian_identity_number" name="legal_guardian_identity_number" value="{{ old('legal_guardian_identity_number') }}">
                    @error('legal_guardian_identity_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="guardianship_title" class="form-label">Título de Tutela</label>
                    <input type="text" class="form-control @error('guardianship_title') is-invalid @enderror" id="guardianship_title" name="guardianship_title" value="{{ old('guardianship_title') }}">
                    @error('guardianship_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('foreigners.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
