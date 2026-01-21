# RRHH Extranjeria

Sistema de gestion de expedientes de extranjeria para la tramitacion de permisos de trabajo en España.

## Tabla de Contenidos

- [Descripcion](#descripcion)
- [Tecnologias](#tecnologias)
- [Requisitos](#requisitos)
- [Instalacion](#instalacion)
- [Configuracion](#configuracion)
- [Uso](#uso)
- [Arquitectura](#arquitectura)
- [Desarrollo](#desarrollo)
- [Documentacion](#documentacion)

## Descripcion

RRHH Extranjeria es una aplicacion web para gestionar expedientes de extranjeria, facilitando el seguimiento de solicitudes de permisos de trabajo para trabajadores extranjeros.

### Funcionalidades principales

- **Gestion de empleadores** - Empresas y autonomos con sus datos fiscales y direcciones
- **Registro de extranjeros** - Trabajadores con documentacion, nacionalidad y datos personales
- **Expedientes de inmigracion** - Vinculacion empleador-trabajador con tipo de solicitud y estado
- **Generacion de documentos** - PDFs oficiales (Modelos EX, contratos, memorias) con plantillas AcroForms
- **Sistema de checklist** - Control de requisitos con progreso visual por entidad
- **Importacion de datos** - Carga masiva desde archivos Excel historicos

## Tecnologias

| Categoria | Tecnologia |
|-----------|------------|
| Backend | PHP 8.2+ / Laravel 12 |
| Frontend | Livewire Volt / Bootstrap 5 / SCSS |
| Base de datos | MySQL 8.0+ |
| PDF Generation | DomPDF (Blade) + pdftk (AcroForms) |
| Build tools | Vite |
| Testing | PHPUnit / Pest |

## Requisitos

### Software
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18
- MySQL >= 8.0
- pdftk Server (para rellenado de PDFs AcroForms)

### Extensiones PHP
```
BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD
```

## Instalacion

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd rrhh-extranjeria/rrhh-extranjeria-app
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos

Editar `.env` con los datos de conexion:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rrhh_extranjeria
DB_USERNAME=<usuario>
DB_PASSWORD=<password>
```

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 6. Compilar assets

```bash
npm run build
```

### 7. Iniciar servidor

```bash
php artisan serve
```

La aplicacion estara disponible en `http://localhost:8000`

## Configuracion

### pdftk (Rellenado de PDFs)

Instalar [PDFtk Server](https://www.pdflabs.com/tools/pdftk-server/) y configurar en `.env`:

```env
# Windows
PDFTK_PATH=C:/Program Files (x86)/PDFtk Server/bin/pdftk.exe

# Linux/Mac
PDFTK_PATH=/usr/bin/pdftk
```

### Credenciales por defecto

Despues de ejecutar los seeders:

| Campo | Valor |
|-------|-------|
| Email | `admin@rrhh-extranjeria.local` |
| Password | `password` |

## Uso

### Flujo de trabajo tipico

```
1. Registrar empleador    → Empresa o autonomo que contratara
2. Registrar extranjero   → Trabajador que solicitara el permiso
3. Crear expediente       → Vincular empleador + extranjero + tipo solicitud
4. Gestionar checklist    → Marcar requisitos completados
5. Generar documentos     → Descargar pack de PDFs para presentar
```

### Rutas principales

| Ruta | Descripcion |
|------|-------------|
| `/v1/` | Dashboard |
| `/v1/employers` | Gestion de empleadores |
| `/v1/foreigners` | Gestion de extranjeros |
| `/v1/inmigration-files` | Gestion de expedientes |
| `/v1/checklist/{id}` | Checklist del expediente |
| `/v1/pdf-templates` | Plantillas PDF con mapeo |
| `/v1/requirement-templates` | Plantillas de requisitos |

## Arquitectura

### Estructura del proyecto

```
rrhh-extranjeria-app/
├── app/
│   ├── Console/Commands/     # Comandos Artisan (excel:import, excel:analyze)
│   ├── DTOs/                 # Data Transfer Objects
│   ├── Enums/                # Enumeraciones (LegalForm, Status, ApplicationType...)
│   ├── Exceptions/           # Excepciones personalizadas
│   ├── Filters/              # Query filters por entidad
│   ├── Helpers/              # Helpers (CampaignHelper)
│   ├── Http/
│   │   ├── Controllers/      # Controladores
│   │   └── Requests/         # Form Requests
│   ├── Models/               # Modelos Eloquent
│   ├── Providers/            # Service Providers
│   ├── Repositories/         # Patron Repository (Contracts + Eloquent)
│   └── Services/             # Logica de negocio
├── database/
│   ├── migrations/           # Migraciones
│   └── seeders/              # Seeders (geograficos + demo)
├── resources/
│   ├── pdf/                  # Plantillas PDF AcroForms
│   ├── scss/                 # Estilos SCSS
│   └── views/
│       ├── documents/        # Plantillas Blade para PDFs
│       ├── layouts/          # Layouts principales
│       └── livewire/         # Componentes Volt
└── routes/
    └── web.php               # Rutas de la aplicacion
```

### Patrones implementados

- **Repository Pattern** - Abstraccion de acceso a datos
- **DTO Pattern** - Transferencia de datos entre capas
- **Service Layer** - Logica de negocio centralizada
- **Filters** - Query filters reutilizables

### Sistema de generacion de documentos

```
DocumentGenerationService
    │
    ├── Busca plantilla PDF predeterminada
    │
    ├── SI existe plantilla con mapeo:
    │   └── PdfTemplateFillerService → pdftk fill_form → PDF rellenado
    │
    └── SI NO existe:
        └── PdfGeneratorService → DomPDF → PDF desde Blade
```

## Desarrollo

### Servidor de desarrollo

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite (hot-reload)
npm run dev
```

### Comandos utiles

```bash
# Migraciones
php artisan migrate                              # Ejecutar migraciones
php artisan migrate:fresh --seed                 # Reset completo con seeders

# Seeders
php artisan db:seed                              # Todos los seeders
php artisan db:seed --class=DemoDataSeeder       # Solo datos demo

# Importacion Excel
php artisan excel:import                         # Importar datos historicos
php artisan excel:analyze                        # Analizar estructura Excel

# Cache
php artisan config:clear                         # Limpiar config
php artisan cache:clear                          # Limpiar cache
php artisan view:clear                           # Limpiar vistas

# Codigo
./vendor/bin/pint                                # Formatear codigo (Laravel Pint)
php artisan test                                 # Ejecutar tests
```

### Convenciones

- **Componentes Livewire Volt** - Clase PHP + Blade en el mismo archivo
- **Nombres en espanol** - Campos de BD y formularios usan espanol
- **Route Model Binding** - `mount(Employer $employer)` en componentes

### Variables de entorno para desarrollo

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
PDFTK_PATH=<ruta-a-pdftk>
```

## Documentacion

| Archivo | Descripcion |
|---------|-------------|
| `doc/CLAUDE_CONTEXT.md` | Contexto tecnico detallado para desarrollo |
| `doc/schema.sql` | Schema de base de datos exportado |
| `doc/UML-desing.mwb` | Diagrama UML (MySQL Workbench) |
| `doc/UML-desing.png` | Imagen del diagrama de BD |

## Contribuir

1. Crear rama desde `dev`: `git checkout -b feature/nueva-funcionalidad`
2. Desarrollar y testear cambios
3. Formatear codigo: `./vendor/bin/pint`
4. Commit con mensaje descriptivo
5. Push y crear Pull Request hacia `dev`

### Ramas

| Rama | Proposito |
|------|-----------|
| `main` | Produccion estable |
| `dev` | Desarrollo activo |
| `feature/*` | Nuevas funcionalidades |
| `fix/*` | Correcciones de bugs |

---

Desarrollado con Laravel 11 + Livewire Volt
