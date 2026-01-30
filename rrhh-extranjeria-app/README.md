# RRHH Extranjeria

Sistema de gestion de expedientes de extranjeria para la tramitacion de autorizaciones de trabajo y residencia en Espana. Permite gestionar empleadores, trabajadores extranjeros, expedientes de inmigracion, generacion automatica de documentos y control de requisitos mediante checklists.

## Stack Tecnologico

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Livewire 3 / Volt, Bootstrap 5.3, SCSS
- **Base de datos**: MySQL
- **PDF**: pdftk (AcroForm), DomPDF (Blade fallback)
- **Excel**: PhpSpreadsheet
- **Build**: Vite 7

## Funcionalidades

### Gestion de Empleadores
- CRUD completo de empleadores (personas fisicas y juridicas)
- Soporte para autonomos (freelancers) y empresas (companies) con datos especificos
- Filtrado por nombre, NIF, forma juridica y asociacion
- Direccion postal con vinculacion geografica (pais, provincia, municipio)

### Gestion de Trabajadores Extranjeros
- CRUD completo con datos personales, documentacion (pasaporte, NIE, NISS) y datos familiares
- Datos adicionales: padre, madre, tutor legal
- Relaciones familiares entre extranjeros (conyuge, pareja, menores, ascendientes)
- Filtrado por nombre, NIE, pasaporte, genero y nacionalidad

### Expedientes de Inmigracion
- Creacion y gestion de expedientes vinculando empleador + trabajador
- 31 tipos de solicitud (modelos EX-00 a EX-30 del Ministerio del Interior)
- Datos laborales: puesto, fechas, salario, jornada, periodo de prueba
- Direccion del centro de trabajo (polimorfica)
- Campanas anuales (formato 2025-2026)

### Maquina de Estados
Los expedientes siguen un flujo de trabajo controlado con 8 estados:

```
Borrador -> Pendiente Revision -> Listo -> Presentado -> Favorable
                                                      -> Denegado
                                       -> Requerido ---^
                                                    -> Archivado
```

- Transiciones validadas por el enum `ImmigrationFileStatus`
- Los estados finales (Favorable, Denegado, Archivado) bloquean ediciones
- Los requisitos obligatorios pendientes impiden avanzar de estado

### Sistema de Checklist / Requisitos
- Plantillas de requisitos (`RequirementTemplate`) configurables por tipo de solicitud y estado
- Generacion automatica de requisitos al cambiar de estado
- Eliminacion automatica de requisitos no aplicables al retroceder de estado
- Requisitos manuales ad-hoc desde el expediente o la vista de checklist
- Cada requisito tiene: entidad objetivo, fecha limite, obligatoriedad, observaciones
- 5 entidades objetivo: Trabajador, Empleador, Representante Legal, Datos Laborales, General
- Edicion inline de requisitos y observaciones desde ambas vistas
- Resumen del checklist con porcentaje de completitud

### Generacion de Documentos PDF
- Doble flujo de generacion:
  1. **AcroForm**: relleno automatico de campos en PDFs editables via pdftk
  2. **Blade/DomPDF**: renderizado desde plantillas Blade como fallback
- Repositorio de PDFs oficiales (Modelo EX-03, EX-10, EX-26, Contrato, Memoria)
- Mapeo de campos PDF a datos del sistema (expediente, trabajador, empleador, direcciones)
- Gestion de plantillas: subida, extraccion de campos, mapeo visual, descarga
- Generacion de packs de documentos en ZIP

### Importacion desde Excel
- Importacion masiva de empleadores, extranjeros y expedientes desde Excel
- Soporte para multiples formatos de columnas (2017-2022 y 2023-2025)
- Comando artisan: `php artisan excel:import`
- Modo dry-run para validacion previa
- Analisis de estructura Excel: `php artisan excel:analyze`

### Perfil de Usuario
- Vista de perfil con edicion de datos personales
- Cambio de contrasena con validacion de contrasena actual
- Eliminacion de cuenta (soft delete)

## Arquitectura

```
app/
├── Console/Commands/       # Comandos artisan (importacion Excel)
├── DTOs/                   # Data Transfer Objects (10)
├── Enums/                  # Enums PHP (10)
├── Http/Controllers/       # Controladores (6)
├── Models/                 # Modelos Eloquent (15)
├── Repositories/
│   ├── Contracts/          # Interfaces de repositorio (6)
│   └── Eloquent/           # Implementaciones Eloquent (6)
├── Services/               # Servicios de negocio (10)
└── Providers/              # Service Providers

resources/
├── views/
│   ├── layouts/            # Layout principal + sidebar + topbar
│   ├── livewire/           # Componentes Volt (28 vistas)
│   │   ├── auth/           # Login, Registro
│   │   ├── checklist/      # Vista de requisitos
│   │   ├── employers/      # CRUD empleadores
│   │   ├── foreigners/     # CRUD extranjeros
│   │   ├── inmigration-files/  # CRUD expedientes
│   │   ├── pdf-templates/  # Gestion plantillas PDF
│   │   ├── profile/        # Perfil de usuario
│   │   ├── requirement-templates/  # Plantillas de requisitos
│   │   └── templates/      # Plantillas (legacy)
│   └── documents/          # Vistas Blade para DomPDF
├── pdf/                    # Repositorio de PDFs oficiales (5 archivos)
└── scss/                   # Estilos SCSS
```

### Patrones de Diseno
- **Repository Pattern**: abstraccion de acceso a datos con interfaces e implementaciones Eloquent
- **DTO Pattern**: objetos inmutables para transferencia de datos entre capas
- **Service Layer**: logica de negocio encapsulada en servicios
- **State Machine**: control de transiciones de estado via enum
- **Pipeline Pattern**: filtrado dinamico en consultas
- **Polymorphic Relations**: direcciones vinculables a multiples entidades
- **Livewire Volt**: componentes de pagina completa con PHP + Blade inline

## Instalacion

### Requisitos Previos
- PHP 8.2+
- Composer
- Node.js / npm
- MySQL
- pdftk (para procesamiento de PDFs AcroForm)

### Configuracion

```bash
# Clonar repositorio
git clone <repo-url>
cd rrhh-extranjeria-app

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# DB_DATABASE=rrhh_extranjeria
# DB_USERNAME=...
# DB_PASSWORD=...

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm run dev
```

### Seeders Disponibles
- `UserSeeder`: usuario administrador
- `CountrySeeder`: 195 paises
- `ProvinceSeeder`: 52 provincias espanolas
- `MunicipalitySeeder`: 845 municipios
- `EmployerSeeder`: 10 empleadores de ejemplo
- `ForeignerSeeder`: 15 trabajadores de ejemplo
- `InmigrationFileSeeder`: 15 expedientes de ejemplo con direcciones laborales

### Comandos Artisan
```bash
# Importar datos desde Excel
php artisan excel:import [archivo] [--sheet=N] [--dry-run]

# Analizar estructura de archivo Excel
php artisan excel:analyze [archivo]
```

## Dependencias Principales

| Paquete | Version | Uso |
|---------|---------|-----|
| laravel/framework | ^12.0 | Framework base |
| livewire/livewire | ^3.7 | Componentes reactivos |
| livewire/volt | ^1.10 | Componentes Volt inline |
| barryvdh/laravel-dompdf | ^3.1 | Generacion PDF desde Blade |
| phpoffice/phpspreadsheet | ^5.4 | Lectura de archivos Excel |
| spatie/pdf-to-text | ^1.54 | Extraccion de texto de PDF |
