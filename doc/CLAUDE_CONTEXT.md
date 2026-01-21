# RRHH-Extranjeria - Contexto para Claude

## Descripcion del Proyecto
Aplicacion web para gestion de expedientes de extranjeria (permisos de trabajo para extranjeros en Espana). Desarrollada en Laravel 11 con Livewire Volt, Bootstrap 5 y MySQL.

## Stack Tecnologico
- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Livewire Volt (componentes anonimos), Bootstrap 5, SCSS, Blade
- **Base de datos:** MySQL
- **Paquetes clave:**
  - PhpSpreadsheet (importacion Excel)
  - DomPDF (generacion PDFs desde Blade views)
  - Livewire Volt (componentes reactivos)
  - pdftk (rellenado de PDFs AcroForms)

## Estructura de la Aplicacion

### Modelos Principales
- `Employer` - Empleadores (empresas o autonomos que contratan)
  - `Company` - Datos adicionales para empresas (representante, cargo)
  - `Freelancer` - Datos adicionales para autonomos (nombre, NISS)
- `Foreigner` - Trabajadores extranjeros
  - `ForeignerExtraData` - Datos adicionales (padre, madre, contacto)
- `InmigrationFile` - Expedientes de extranjeria (vincula employer + foreigner)
- `Address` - Direcciones (polimorfica, usada por Employer, Foreigner, InmigrationFile)
- `User` - Usuarios del sistema (gestores/editores)
- `RequirementTemplate` - Plantillas de requisitos por tipo de solicitud
- `FileRequirement` - Requisitos especificos de cada expediente
- `PdfTemplate` - Plantillas PDF editables (AcroForms) con mapeo de campos

### Modelos Geograficos
- `Country` - Paises (con codigos ISO)
- `Province` - Provincias de Espana
- `Municipality` - Municipios de Espana

### Enums Importantes (app/Enums/)
- `LegalForm` - Formas juridicas (SA, SL, SLU, COOP, EI, CB, SC)
- `ApplicationType` - Tipos de solicitud (EX_00 a EX_18, formularios oficiales)
- `ImmigrationFileStatus` - Estados del expediente (BORRADOR, PENDIENTE_REVISION, LISTO, PRESENTADO, REQUERIDO, FAVORABLE, DENEGADO, ARCHIVADO)
- `Gender` - Genero (H = Hombre, M = Mujer)
- `MaritalStatus` - Estado civil (Sol, Cas, Viu, Div, Sep)
- `WorkingDayType` - Tipo de jornada (COMPLETA, PARCIAL, CONTINUA, PARTIDA)
- `TargetEntity` - Entidad objetivo para requisitos (EMPLEADOR, EXTRANJERO, EXPEDIENTE)
- `PdfTemplateType` - Tipos de plantilla PDF (MODELO_EX, CONTRATO, MEMORIA)

### Servicios (app/Services/)
- `EmployerService` - CRUD de empleadores con Company/Freelancer y direcciones
- `ForeignerService` - CRUD de extranjeros
- `InmigrationFileService` - CRUD de expedientes
- `ChecklistService` - Gestion de requisitos (checklist) de expedientes
- `DocumentGenerationService` - Orquestacion de generacion de PDFs (plantillas + fallback Blade)
- `PdfGeneratorService` - Generacion de PDFs desde Blade views con DomPDF
- `PdfTemplateService` - CRUD de plantillas PDF AcroForms
- `PdfFieldExtractorService` - Extraccion de campos de PDFs con pdftk
- `PdfTemplateFillerService` - Rellenado de PDFs AcroForms con datos
- `ExcelImportService` - Importacion masiva desde Excel historico

### Controladores (app/Http/Controllers/)
- `DocumentController` - Generacion y descarga de packs de documentos (ZIP)
- `ChecklistController` - API endpoints para resumen y proximos requisitos
- `EmployerController`, `ForeignerController`, `InmigrationFileController` - CRUD

### Repositorios (app/Repositories/)
Patron Repository implementado:
- Contracts: `EmployerRepository`, `ForeignerRepository`, `InmigrationFileRepository`, `FileRequirementRepository`, `RequirementTemplateRepository`, `PdfTemplateRepository`
- Eloquent: Implementaciones con Eloquent ORM

## Rutas Principales (routes/web.php)

```
/v1/                          - Dashboard
/v1/employers                 - Gestion de empleadores (CRUD)
/v1/foreigners                - Gestion de extranjeros (CRUD)
/v1/inmigration-files         - Gestion de expedientes (CRUD)
/v1/documents/{id}/generate   - Generar pack de documentos (ZIP)
/v1/documents/{id}/generate-ex - Generar solo Modelo EX
/v1/checklist/{id}            - Vista de checklist del expediente
/v1/pdf-templates             - Plantillas PDF AcroForms (CRUD + mapeo)
/v1/templates                 - Repositorio de archivos PDF
/v1/requirement-templates     - Plantillas de requisitos
```

## Sistema de Documentos

### Generacion On-the-fly con Prioridad de Plantillas
Los documentos se generan en caliente cada vez que se solicitan:
1. `DocumentGenerationService::generateDocument()` busca plantilla PDF predeterminada
2. Si existe plantilla con mapeo → `PdfTemplateFillerService` rellena el PDF AcroForm
3. Si no existe → Fallback a generacion desde Blade con DomPDF
4. Se empaquetan en un ZIP temporal y se descargan

### Plantillas PDF AcroForms (resources/pdf/)
Sistema de plantillas editables con campos de formulario:
- Subida de PDFs editables (AcroForms)
- Deteccion automatica de campos con `pdftk dump_data_fields`
- Mapeo visual de campos PDF a datos del sistema
- Soporte para checkboxes condicionales (ej: sexo M/H)
- Rellenado con `pdftk fill_form` + FDF

### Plantillas Blade Fallback (resources/views/documents/)
- `modelo-ex.blade.php` - Formulario EX oficial
- `contrato.blade.php` - Contrato de trabajo
- `memoria.blade.php` - Memoria del empleador

## Sistema de Mapeo de Campos PDF

### DocumentPackDTO - Datos Disponibles para Mapeo
El DTO centraliza todos los datos para generacion de documentos:

**Trabajador (worker):**
- `nombre_completo`, `nombre`, `apellidos`, `primer_apellido`, `segundo_apellido`
- `pasaporte`, `nie`, `nie_letra_inicial`, `nie_numero`, `nie_letra_final`, `niss`
- `sexo`, `estado_civil`, `nacionalidad`, `pais_nacimiento`, `lugar_nacimiento`
- `fecha_nacimiento`, `fecha_nacimiento_dia`, `fecha_nacimiento_mes`, `fecha_nacimiento_anio`
- `nombre_padre`, `nombre_madre`, `telefono`, `email`
- `tutor_nombre`, `tutor_documento`, `tutor_titulo`
- `trabajador_direccion`, `trabajador_direccion_calle`, `trabajador_direccion_numero`, `trabajador_direccion_piso_puerta`, `trabajador_direccion_codigo_postal`, `trabajador_direccion_municipio`, `trabajador_direccion_provincia`

**Empleador (employer):**
- `razon_social`, `nombre_comercial`, `nif`, `ccc`, `cnae`, `forma_juridica`
- `email`, `telefono`
- `representante_nombre`, `representante_documento`, `representante_cargo`
- `empleador_direccion`, `empleador_direccion_calle`, `empleador_direccion_numero`, `empleador_direccion_piso_puerta`, `empleador_direccion_codigo_postal`, `empleador_direccion_municipio`, `empleador_direccion_provincia`

**Expediente/Trabajo (job):**
- `expediente_codigo`, `expediente_titulo`, `campana`, `tipo_solicitud`
- `puesto_trabajo`, `tipo_jornada`, `horas_semanales`
- `fecha_inicio`, `fecha_inicio_dia`, `fecha_inicio_mes`, `fecha_inicio_anio`
- `fecha_fin`, `fecha_fin_dia`, `fecha_fin_mes`, `fecha_fin_anio`
- `salario`, `salario_numero`
- `periodo_prueba`, `periodo_prueba_dias`
- `direccion_trabajo`, `direccion_trabajo_calle`, `direccion_trabajo_numero`, `direccion_trabajo_piso_puerta`, `direccion_trabajo_codigo_postal`, `direccion_trabajo_municipio`, `direccion_trabajo_provincia`

**Representante Legal (representative):**
- `gestor_nombre`, `gestor_email`, `fecha_generacion`, `hora_generacion`

### Mapeo de Checkboxes
Para campos enum como sexo (M/H), el mapeo soporta `checked_when`:
```json
{
  "campo_pdf_sexo_h": {
    "source": "worker",
    "field": "sexo",
    "type": "checkbox",
    "checked_when": "H",
    "checked_value": "Yes"
  }
}
```

## Sistema de Checklist

### Flujo
1. `RequirementTemplate` define requisitos genericos por `ApplicationType`
2. Al crear un expediente, se generan `FileRequirement` desde las plantillas
3. La vista `checklist/show.blade.php` permite:
   - Ver progreso general y por entidad (empleador/extranjero/expediente)
   - Filtrar por entidad y estado
   - Marcar como completado/pendiente
   - Agregar nuevos requisitos
   - Eliminar requisitos
   - Regenerar desde plantillas

## Estructura de Navegacion (Sidebar)

```
GESTION
  - Dashboard
  - Expedientes
  - Empleadores
  - Extranjeros

DOCUMENTOS
  - Plantillas PDF (AcroForms con mapeo)
  - Repositorio (archivos PDF originales)

CHECKLISTS
  - Requisitos

CONFIGURACION
  - Usuarios (pendiente)
  - Ajustes (pendiente)
```

## Archivos Clave

### Vistas Livewire Volt (resources/views/livewire/)
```
auth/
  - login.blade.php, register.blade.php
employers/
  - index.blade.php, create.blade.php, edit.blade.php, show.blade.php
foreigners/
  - index.blade.php, create.blade.php, edit.blade.php, show.blade.php
inmigration-files/
  - index.blade.php, create.blade.php, edit.blade.php, show.blade.php
checklist/
  - show.blade.php (vista completa con progreso y gestion)
requirement-templates/
  - index.blade.php, create.blade.php, edit.blade.php
pdf-templates/
  - index.blade.php, create.blade.php (wizard 3 pasos), edit.blade.php, show.blade.php
templates/
  - index.blade.php, show.blade.php, create.blade.php, edit.blade.php
dashboard.blade.php
```

### DTOs (app/DTOs/)
- `EmployerDTO`, `FreelancerDTO`, `CompanyDTO` - Creacion/actualizacion empleadores
- `ForeignerDTO`, `ForeignerExtraDataDTO` - Creacion/actualizacion extranjeros
- `InmigrationFileDTO` - Creacion/actualizacion expedientes
- `FileRequirementDTO` - Requisitos de expediente
- `DocumentPackDTO` - Datos combinados para generacion de documentos
- `PdfTemplateDTO` - Creacion/actualizacion plantillas PDF
- `PdfFieldDTO` - Campos extraidos de PDF

### Excepciones (app/Exceptions/)
- `DocumentGenerationException` - Errores en generacion de documentos
- `PdfExtractionException` - Errores extrayendo campos de PDF
- `PdfGenerationException` - Errores rellenando PDFs

## Comandos Utiles

```bash
# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Seeders
php artisan db:seed                              # Todos
php artisan db:seed --class=DemoDataSeeder       # Solo demo data
php artisan db:seed --class=CountrySeeder        # Solo paises
php artisan db:seed --class=ProvinceSeeder       # Solo provincias
php artisan db:seed --class=MunicipalitySeeder   # Solo municipios

# Importar Excel historico
php artisan excel:import
php artisan excel:import --sheet=8               # Hoja especifica
php artisan excel:analyze                        # Analizar estructura

# Desarrollo
npm run dev                                      # Vite dev server
npm run build                                    # Build produccion
php artisan serve                                # Servidor Laravel
```

## Configuracion Especial

### pdftk (config/services.php)
```php
'pdftk' => [
    'path' => env('PDFTK_PATH', 'pdftk'),
],
```

En `.env`:
```
PDFTK_PATH=C:/Program Files (x86)/PDFtk Server/bin/pdftk.exe
```

### Disk para Plantillas PDF (config/filesystems.php)
```php
'pdf_templates' => [
    'driver' => 'local',
    'root' => resource_path('pdf'),
],
```

## Base de Datos
- Schema completo en `doc/schema.sql`
- Diagrama UML en `doc/UML-desing.mwb` (MySQL Workbench)
- Imagen del diagrama en `doc/UML-desing.png`

## Notas Tecnicas

### Livewire Volt
- Componentes anonimos: clase PHP + Blade en mismo archivo
- Uso de `#[Layout('layouts.app')]` y `#[Title('...')]`
- Propiedades reactivas con `wire:model.live`
- Navegacion SPA con `wire:navigate`

### Route Model Binding
Las rutas usan binding automatico: `mount(Employer $employer)`

### Relacion Polimorfica de Direcciones
`Address` usa `MorphTo` con `addressable_type` y `addressable_id`:
- `App\Models\Employer` → direccion del empleador
- `App\Models\Foreigner` → direccion del trabajador
- `App\Models\InmigrationFile` → direccion del lugar de trabajo

**Importante:** Los campos de direccion en DocumentPackDTO usan prefijos unicos para evitar colisiones en `array_merge()`:
- `trabajador_direccion_*` para extranjero
- `empleador_direccion_*` para empleador
- `direccion_trabajo_*` para lugar de trabajo

### Campanas
Sistema de campanas agricolas (ej: "2025-2026") para filtrar expedientes por temporada.

## Estado Actual (Enero 2026)

### Funcionalidades Completadas
1. **CRUD completo** de Employers, Foreigners, InmigrationFiles
2. **Sistema de autenticacion** (login/register/logout con Livewire Volt)
3. **Dashboard** con estadisticas por campana, expedientes recientes, acciones rapidas
4. **Generacion de documentos** con sistema dual (plantillas PDF + fallback Blade)
5. **Sistema de plantillas PDF AcroForms** con deteccion y mapeo de campos
6. **Sistema de checklist** con progreso visual y gestion de requisitos
7. **Importacion Excel** desde archivo historico
8. **Seeders completos** (geograficos + demo data)
9. **Plantillas de requisitos** por tipo de solicitud

### Datos Demo (DemoDataSeeder)
- **Empleadores:** Fresas del Condado SL, Coop. Citricos de Huelva, Invernaderos Perez
- **Extranjeros:** Ahmed El Amrani (Marruecos), Moussa Diallo (Senegal), Chioma Okafor (Nigeria)
- **Expedientes:** 3 expedientes con diferentes estados y tipos de solicitud

## Proximos Pasos / Pendientes
1. **Categorizacion de requisitos** - Agrupar por tipo de solicitud y seccion
2. **Gestion de usuarios** - Roles y permisos
3. **Ajustes del sistema** - Configuracion general
4. **Notificaciones** - Alertas de vencimientos y requisitos pendientes
5. **Reportes** - Generacion de informes estadisticos
