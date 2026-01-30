# Claude Code - Contexto del Proyecto RRHH Extranjeria

Este archivo proporciona contexto para que Claude Code pueda trabajar eficientemente en este proyecto.

## Descripcion General

Aplicacion Laravel 12 para gestionar expedientes de extranjeria en Espana. Gestiona empleadores, trabajadores extranjeros, expedientes de inmigracion con maquina de estados, checklists de requisitos automatizados y generacion de documentos PDF.

## Stack

- PHP 8.2+ / Laravel 12
- Livewire 3 + Volt (componentes inline PHP + Blade)
- Bootstrap 5.3 + SCSS (Vite 7)
- MySQL
- pdftk (AcroForm PDF) + DomPDF (Blade fallback)
- PhpSpreadsheet (importacion Excel)

## Estructura del Proyecto

```
app/
  Console/Commands/          # excel:analyze, excel:import
  DTOs/                      # 10 DTOs (inmutables, fromRequest/toArray)
  Enums/                     # 10 enums PHP (ApplicationType, ImmigrationFileStatus, TargetEntity, etc.)
  Http/Controllers/          # 6 controladores (ChecklistController, DocumentController, etc.)
  Models/                    # 15 modelos Eloquent
  Repositories/
    Contracts/               # 6 interfaces
    Eloquent/                # 6 implementaciones
  Services/                  # 10 servicios de negocio
  Providers/

resources/views/
  layouts/                   # app.blade.php + partials (sidebar, topbar, alerts)
  livewire/                  # 28 componentes Volt organizados por modulo
  documents/                 # Vistas Blade para DomPDF (modelo-ex, contrato, memoria)

resources/pdf/               # 5 PDFs oficiales AcroForm (EX-03, EX-10, EX-26, Contrato, Memoria)

database/
  migrations/                # 9 migraciones consolidadas
  seeders/                   # 8 seeders (geo, empleadores, extranjeros, expedientes)
```

## Patrones y Convenciones

### Arquitectura
- **Controlador -> Servicio -> Repositorio -> Modelo**: flujo estandar de datos
- **DTOs**: se usan para pasar datos entre capas. Tienen metodos `fromRequest()`, `fromTemplate()`, `toArray()`
- **Repository Pattern**: interfaces en `Contracts/`, implementaciones en `Eloquent/`. Registrados en `AppServiceProvider`
- **Enums PHP**: todos tienen metodo `values()` y `label()`. Muchos tienen logica adicional (transiciones, descripciones)

### Componentes Livewire Volt
Todos los componentes siguen esta estructura:

```php
<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('layouts.app')]
#[Title('Titulo - RRHH Extranjeria')]
class extends Component {
    // Propiedades publicas = wire:model
    public string $field = '';

    public function mount(Model $model): void { /* cargar datos */ }
    public function rules(): array { /* validacion */ }
    public function save(): void { /* accion principal */ }
    public function with(): array { /* datos para la vista */ }
}; ?>

<div>
    @section('page-title', 'Titulo')
    {{-- Breadcrumb + contenido Bootstrap --}}
</div>
```

### Rutas
- Todas las rutas protegidas bajo `/v1` con middleware `auth`
- Se usa `Volt::route()` para componentes Livewire
- Convenciones de nombres: `modulo.accion` (employers.index, employers.show, etc.)
- Archivo: `routes/web.php`

### Vistas Blade
- Layout 8/4 columnas (col-lg-8 contenido principal + col-lg-4 sidebar)
- Cards Bootstrap con card-header + card-body
- Formularios: `wire:submit="method"`, `wire:model="prop"`, clases `@error() is-invalid @enderror`
- Feedback: `session()->flash('success', '...')` renderizado por `layouts/partials/alerts.blade.php`
- Iconos: Bootstrap Icons (`bi bi-*`)

## Modelos Principales y Relaciones

### User
- Campos: first_name, last_name, legal_name, dni, phone_number, email, password
- Relacion: `inmigrationFiles()` HasMany

### Employer
- Campos: legal_form (enum), fiscal_name, nif, ccc, cnae, email, phone, is_associated
- Subtipos: `freelancer()` HasOne, `company()` HasOne (segun legal_form)
- `address()` MorphOne Address

### Foreigner
- Campos: first_name, last_name, passport, nie, niss, gender, birthdate, marital_status, nationality_id, birth_country_id
- `extraData()` HasOne ForeignerExtraData
- `address()` MorphOne Address

### InmigrationFile
- Campos: campaign, file_code, file_title, application_type, status, job_title, start_date, end_date, salary, working_day_type, working_hours, probation_period
- FKs: editor_id (User), employer_id, foreigner_id
- `requirements()` HasMany FileRequirement
- `workAddress()` MorphOne Address

### FileRequirement
- Campos: name, description, target_entity, observation, due_date, is_completed, is_mandatory, completed_at, notified_at
- FKs: inmigration_file_id, requirement_template_id (nullable)

### RequirementTemplate
- Campos: name, description, target_entity, application_type, trigger_status, days_to_expire, is_mandatory
- Scopes: `forApplicationType()`, `triggeredByStatus()`

### PdfTemplate
- Campos: name, document_type, field_mapping (JSON), extracted_fields (JSON), storage_path
- Metodo clave: `hasCompleteMapping()`, `getUnmappedFields()`

### Address (polimorfica)
- `addressable()` MorphTo (vinculable a Employer, Foreigner, InmigrationFile)
- FKs: country_id, province_id, municipality_id

## Enums Clave

### ImmigrationFileStatus
```
BORRADOR -> PENDIENTE_REVISION -> LISTO -> PRESENTADO -> FAVORABLE / DENEGADO
                                        -> REQUERIDO -> (vuelve a LISTO o ARCHIVADO)
```
- `canTransitionTo(status)`: valida transicion
- `isFinal()`: true para FAVORABLE, DENEGADO, ARCHIVADO
- `isEditable()`: true para BORRADOR, PENDIENTE_REVISION, REQUERIDO

### TargetEntity
- WORKER, EMPLOYER, REPRESENTATIVE, LABOR, GENERAL
- Indica quien debe aportar documentacion en los requisitos

### ApplicationType
- 31 tipos (EX_00 a EX_30): modelos oficiales del Ministerio del Interior
- `description()`: descripcion del tipo de solicitud
- `templateName()`: nombre de la plantilla asociada

## Servicios Principales

### ChecklistService
Gestiona el ciclo de vida de requisitos de un expediente:
- `processStatusChange()`: cambia estado + genera/elimina requisitos automaticamente
- `ensureRequirementsForCurrentStatus()`: genera requisitos para expedientes sin flujo (seeders)
- `completeRequirement()`, `deleteRequirement()`, `updateRequirement()`, `addManualRequirement()`
- `getChecklistSummary()`: resumen con conteos y porcentaje de completitud
- Valida que los requisitos obligatorios esten completos antes de avanzar estado

### DocumentGenerationService
Genera documentos PDF para expedientes:
- `generateDocumentPack()`: ZIP con todos los documentos
- `generateModeloEX()`: documento individual
- Doble flujo: AcroForm (pdftk) con fallback a Blade (DomPDF)

### InmigrationFileService
- CRUD de expedientes con Pipeline de filtros
- `changeStatus()`: delega en ChecklistService

### PdfTemplateService
- CRUD de plantillas PDF
- Extrae campos de AcroForms via pdftk
- Gestiona mapeo de campos PDF -> datos del sistema

## Migraciones (9 consolidadas)

1. `0001_01_01_000000` - users, password_reset_tokens, sessions
2. `0001_01_01_000001` - cache, cache_locks
3. `0001_01_01_000002` - jobs, job_batches, failed_jobs
4. `2025_12_10_000001` - countries, provinces, municipalities
5. `2025_12_12_091608` - employers, freelancers, companies
6. `2025_12_17_114733` - foreigners, foreigners_extra_data, foreigner_relationships
7. `2025_12_18_000001` - addresses (polimorfica)
8. `2025_12_18_000002` - requirement_templates, inmigration_files, file_requirements
9. `2026_01_22_000001` - pdf_templates

Las migraciones estan consolidadas: todos los campos finales estan en las migraciones base (sin ALTER TABLE separados).

## Seeders

Ejecutar con `php artisan migrate:fresh --seed`. Orden:
UserSeeder -> CountrySeeder -> ProvinceSeeder -> MunicipalitySeeder -> EmployerSeeder -> ForeignerSeeder -> InmigrationFileSeeder

## Datos Geograficos

- 195 paises (CountrySeeder)
- 52 provincias espanolas
- 845 municipios espanoles
- Jerarquia: Country -> Province -> Municipality
- Las direcciones referencian los tres niveles

## Repositorio de PDFs

`resources/pdf/` contiene 5 formularios oficiales AcroForm:
- Modelo EX-03 (autorizacion de trabajo por cuenta ajena)
- Modelo EX-10 (residencia por circunstancias excepcionales)
- Modelo EX-26 (modificacion de residencia/estancia)
- Contrato de Trabajo Indefinido
- Memoria Justificativa

## Notas para Desarrollo

- Los componentes Volt combinan PHP y Blade en un solo archivo `.blade.php`
- Las validaciones usan `$this->validate()` de Livewire con reglas en `rules()` o inline
- Flash messages via `session()->flash('success'|'error', $msg)`
- Soft deletes habilitado en: User, Employer, Foreigner, InmigrationFile, RequirementTemplate, PdfTemplate
- El campo `representantive_identity_number` en Company tiene un typo historico (falta una 'e')
- pdftk debe estar instalado en el sistema para procesamiento de PDFs AcroForm
- Los filtros de listados usan el Pipeline Pattern (clases en `app/Filters/`)
