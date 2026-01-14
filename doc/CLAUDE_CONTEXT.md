# RRHH-Extranjeria - Contexto para Claude

## Descripcion del Proyecto
Aplicacion web para gestion de expedientes de extranjeria (permisos de trabajo para extranjeros en Espana). Desarrollada en Laravel 11 con Livewire Volt, Bootstrap 5 y MySQL.

## Stack Tecnologico
- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Livewire Volt (componentes anonimos), Bootstrap 5, Blade
- **Base de datos:** MySQL
- **Paquetes clave:** PhpSpreadsheet (Excel), FPDI/FPDF (PDFs)

## Estructura de la Aplicacion

### Modelos Principales
- `Employer` - Empleadores (empresas o autonomos que contratan)
- `Foreigner` - Trabajadores extranjeros
- `InmigrationFile` - Expedientes de extranjeria (vincula employer + foreigner)
- `Address` - Direcciones (polimorfica, usada por Employer, Foreigner, InmigrationFile)
- `User` - Usuarios del sistema (gestores)

### Enums Importantes
- `LegalForm` - Formas juridicas (SA, SL, Autonomo, etc.)
- `ApplicationType` - Tipos de solicitud (EX-00 a EX-30, formularios oficiales)
- `ImmigrationFileStatus` - Estados del expediente (borrador, presentado, favorable, denegado, etc.)

### Servicios
- `EmployerService` - CRUD de empleadores con direcciones
- `ForeignerService` - CRUD de extranjeros
- `ExcelImportService` - Importacion masiva desde Excel
- `DocumentGenerationService` - Generacion de PDFs (Modelo EX, contratos, memorias)

### Rutas Principales
```
/employers - Gestion de empleadores
/foreigners - Gestion de extranjeros
/inmigration-files - Gestion de expedientes
/documents - Generacion de documentos PDF
```

## Estado Actual (Enero 2026)

### Funcionalidades Completadas
1. **CRUD completo** de Employers, Foreigners, InmigrationFiles
2. **Sistema de autenticacion** basico (login/register/logout)
3. **Importacion Excel** desde archivo historico (`doc/EXPEDIENTES EXTRANJERIA...xlsx`)
   - Comando: `php artisan excel:import`
   - Analisis: `php artisan excel:analyze`
4. **Seeders geograficos** (paises, provincias, municipios de Espana)

### Ultimos Cambios Realizados
- Creado `ExcelImportService` con mapeo de columnas por año (2017-2022 vs 2023-2025)
- Migraciones para hacer nullable campos requeridos (passport, nie, ccc, cnae, job_title, etc.)
- Añadido metodo `checkAvailability()` en `DocumentGenerationService`

### Problemas Conocidos / Pendientes
1. **DocumentGenerationService**: Los metodos `generateDocumentPack()` y `generateModeloEX()`
   esperan parametros diferentes a como los llama la vista `documents/show.blade.php`
2. **Emails duplicados**: Restriccion UNIQUE en `employers.email` causa errores cuando
   multiples empleadores usan el mismo email (ej: email de la asesoria)
3. **fiscal_name duplicados**: Similar al anterior, nombres fiscales repetidos

## Archivos Clave

### Vistas Livewire Volt (resources/views/livewire/)
- `employers/` - index, create, edit, show
- `foreigners/` - index, create, edit, show
- `inmigration-files/` - index, create, edit, show
- `documents/` - index, show (generacion PDFs)
- `auth/` - login, register

### DTOs (app/DTOs/)
- `EmployerDTO`, `FreelancerDTO`, `CompanyDTO` - Para creacion/actualizacion
- `DocumentPackDTO` - Para generacion de documentos

### Repositorios (app/Repositories/)
Patron Repository implementado para todas las entidades principales.

## Comandos Utiles
```bash
# Importar datos del Excel
php artisan excel:import

# Importar solo una hoja especifica (0-8)
php artisan excel:import --sheet=8

# Analizar estructura del Excel
php artisan excel:analyze

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders geograficos
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=ProvinceSeeder
php artisan db:seed --class=MunicipalitySeeder
```

## Base de Datos
- Schema completo en `doc/schema.sql`
- Diagrama UML en `doc/UML-desing.mwb` (MySQL Workbench)

## Notas para Continuar
- La aplicacion usa **Livewire Volt** con componentes anonimos (clase PHP + Blade en mismo archivo)
- Las rutas usan **Route Model Binding** (ej: `mount(Employer $employer)`)
- Los campos de formulario estan en español (comercial_name, fiscal_name, etc.)
