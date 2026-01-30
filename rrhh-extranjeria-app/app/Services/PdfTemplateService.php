<?php

namespace App\Services;

use App\DTOs\PdfFieldDTO;
use App\DTOs\PdfTemplateDTO;
use App\Enums\ApplicationType;
use App\Enums\Cnae;
use App\Enums\Gender;
use App\Enums\LegalForm;
use App\Enums\MaritalStatus;
use App\Enums\PdfTemplateType;
use App\Enums\WorkingDayType;
use App\Exceptions\PdfExtractionException;
use App\Models\PdfTemplate;
use App\Repositories\Contracts\PdfTemplateRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio para gestionar plantillas PDF
 */
class PdfTemplateService
{
    public function __construct(
        protected PdfTemplateRepository $repository,
        protected PdfFieldExtractorService $fieldExtractor,
    ) {}

    /**
     * Crea una nueva plantilla PDF
     *
     * @throws PdfExtractionException
     */
    public function create(PdfTemplateDTO $dto): PdfTemplate
    {
        return DB::transaction(function () use ($dto) {
            // Almacenar el archivo si se proporciona
            $storagePath = null;
            $originalFilename = null;
            $extractedFields = [];
            $totalFields = 0;

            if ($dto->file) {
                $storagePath = $this->storeFile($dto->file, $dto->documentType);
                $originalFilename = $dto->file->getClientOriginalName();

                // Extraer campos del PDF
                $filePath = Storage::disk('pdf_templates')->path($storagePath);
                $extractedFields = $this->fieldExtractor->extractFields($filePath);
                $totalFields = count($extractedFields);
            } elseif ($dto->storagePath) {
                // Archivo del repositorio (ya existe en el disco)
                $storagePath = $dto->storagePath;
                $originalFilename = $dto->originalFilename ?? $dto->storagePath;

                $filePath = Storage::disk('pdf_templates')->path($storagePath);
                if (file_exists($filePath)) {
                    $extractedFields = $this->fieldExtractor->extractFields($filePath);
                    $totalFields = count($extractedFields);
                }
            }

            // Crear la plantilla
            $template = $this->repository->create([
                'name' => $dto->name,
                'description' => $dto->description,
                'original_filename' => $originalFilename ?? $dto->originalFilename,
                'storage_path' => $storagePath ?? $dto->storagePath,
                'document_type' => $dto->documentType->value,
                'application_type' => $dto->applicationType?->value,
                'is_default' => $dto->isDefault,
                'is_active' => $dto->isActive,
                'field_mapping' => $dto->fieldMapping,
                'extracted_fields' => array_map(fn(PdfFieldDTO $f) => $f->toArray(), $extractedFields),
                'total_fields' => $totalFields,
                'created_by' => $dto->createdBy,
            ]);

            // Si es predeterminada, actualizar las demás
            if ($dto->isDefault) {
                $template->setAsDefault();
            }

            return $template;
        });
    }

    /**
     * Actualiza una plantilla PDF existente
     */
    public function update(int $id, PdfTemplateDTO $dto): bool
    {
        return DB::transaction(function () use ($id, $dto) {
            $template = $this->repository->findById($id);

            if (!$template) {
                return false;
            }

            $data = [
                'name' => $dto->name,
                'description' => $dto->description,
                'document_type' => $dto->documentType->value,
                'application_type' => $dto->applicationType?->value,
                'is_active' => $dto->isActive,
            ];

            // Si se proporciona un nuevo archivo
            if ($dto->file) {
                // Eliminar el archivo anterior si existe
                if ($template->storage_path) {
                    Storage::disk('pdf_templates')->delete($template->storage_path);
                }

                // Almacenar el nuevo archivo
                $data['storage_path'] = $this->storeFile($dto->file, $dto->documentType);
                $data['original_filename'] = $dto->file->getClientOriginalName();

                // Extraer campos del nuevo PDF
                $filePath = Storage::disk('pdf_templates')->path($data['storage_path']);
                $extractedFields = $this->fieldExtractor->extractFields($filePath);
                $data['extracted_fields'] = array_map(fn(PdfFieldDTO $f) => $f->toArray(), $extractedFields);
                $data['total_fields'] = count($extractedFields);

                // Limpiar el mapeo si cambió el PDF
                $data['field_mapping'] = null;
            }

            $result = $this->repository->update($id, $data);

            // Actualizar predeterminada si es necesario
            if ($dto->isDefault && $result) {
                $template->refresh();
                $template->setAsDefault();
            }

            return $result;
        });
    }

    /**
     * Actualiza el mapeo de campos de una plantilla
     */
    public function updateFieldMapping(int $id, array $fieldMapping): bool
    {
        return $this->repository->updateFieldMapping($id, $fieldMapping);
    }

    /**
     * Elimina una plantilla PDF
     */
    public function delete(int $id): bool
    {
        $template = $this->repository->findById($id);

        if (!$template) {
            return false;
        }

        // No eliminar el archivo físico en soft delete
        // Se eliminará cuando se haga force delete

        return $this->repository->delete($id);
    }

    /**
     * Restaura una plantilla eliminada
     */
    public function restore(int $id): bool
    {
        $template = PdfTemplate::withTrashed()->find($id);

        if (!$template) {
            return false;
        }

        return $template->restore();
    }

    /**
     * Elimina permanentemente una plantilla
     */
    public function forceDelete(int $id): bool
    {
        $template = PdfTemplate::withTrashed()->find($id);

        if (!$template) {
            return false;
        }

        // Eliminar el archivo físico
        if ($template->storage_path) {
            Storage::disk('pdf_templates')->delete($template->storage_path);
        }

        return $template->forceDelete();
    }

    /**
     * Re-extrae los campos de un PDF existente
     */
    public function reExtractFields(int $id): ?array
    {
        $template = $this->repository->findById($id);

        if (!$template || !$template->file_exists) {
            return null;
        }

        try {
            $extractedFields = $this->fieldExtractor->extractFields($template->file_path);

            $this->repository->update($id, [
                'extracted_fields' => array_map(fn(PdfFieldDTO $f) => $f->toArray(), $extractedFields),
                'total_fields' => count($extractedFields),
            ]);

            return array_map(fn(PdfFieldDTO $f) => $f->toArray(), $extractedFields);
        } catch (PdfExtractionException $e) {
            Log::error('Error re-extracting PDF fields', [
                'template_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtiene la plantilla predeterminada para un tipo de documento
     */
    public function getDefaultTemplate(
        PdfTemplateType $documentType,
        ?ApplicationType $applicationType = null
    ): ?PdfTemplate {
        return $this->repository->getDefaultTemplate($documentType, $applicationType);
    }

    /**
     * Establece una plantilla como predeterminada
     */
    public function setAsDefault(int $id): bool
    {
        return $this->repository->setAsDefault($id);
    }

    /**
     * Obtiene los datos disponibles para mapeo de campos
     */
    public function getAvailableDataSources(): array
    {
        return [
            'worker' => [
                'label' => 'Trabajador',
                'fields' => [
                    'nombre_completo' => 'Nombre completo',
                    'nombre' => 'Nombre',
                    'apellidos' => 'Apellidos completos',
                    'primer_apellido' => 'Primer apellido',
                    'segundo_apellido' => 'Segundo apellido',
                    'pasaporte' => 'Número de pasaporte',
                    'nie' => 'NIE completo',
                    'nie_letra_inicial' => 'NIE - Letra inicial',
                    'nie_numero' => 'NIE - Número (sin letras)',
                    'nie_letra_final' => 'NIE - Letra final',
                    'niss' => 'NISS',
                    'sexo' => [
                        'label' => 'Sexo',
                        'options' => array_column(Gender::cases(), 'value'),
                    ],
                    'fecha_nacimiento' => 'Fecha de nacimiento (dd/mm/yyyy)',
                    'fecha_nacimiento_dia' => 'Fecha nacimiento - Día',
                    'fecha_nacimiento_mes' => 'Fecha nacimiento - Mes',
                    'fecha_nacimiento_anio' => 'Fecha nacimiento - Año',
                    'estado_civil' => [
                        'label' => 'Estado civil',
                        'options' => array_column(MaritalStatus::cases(), 'value'),
                    ],
                    'nacionalidad' => 'Nacionalidad',
                    'pais_nacimiento' => 'País de nacimiento',
                    'lugar_nacimiento' => 'Lugar de nacimiento',
                    'nombre_padre' => 'Nombre del padre',
                    'nombre_madre' => 'Nombre de la madre',
                    'trabajador_telefono' => 'Teléfono',
                    'trabajador_email' => 'Email',
                    'tutor_nombre' => 'Nombre del tutor',
                    'tutor_documento' => 'Documento del tutor',
                    'tutor_titulo' => 'Título del tutor',
                    'trabajador_direccion' => 'Dirección completa',
                    'trabajador_direccion_calle' => 'Dirección - Calle',
                    'trabajador_direccion_numero' => 'Dirección - Número',
                    'trabajador_direccion_piso_puerta' => 'Dirección - Piso/Puerta',
                    'trabajador_direccion_codigo_postal' => 'Dirección - Código postal',
                    'trabajador_direccion_municipio' => 'Dirección - Municipio',
                    'trabajador_direccion_provincia' => 'Dirección - Provincia',
                ],
            ],
            'employer' => [
                'label' => 'Empleador',
                'fields' => [
                    'razon_social' => 'Razón social',
                    'nombre_comercial' => 'Nombre comercial',
                    'nif' => 'NIF/CIF',
                    'ccc' => 'Código Cuenta Cotización',
                    'cnae' => [
                        'label' => 'CNAE (código)',
                        'options' => array_column(Cnae::cases(), 'value'),
                    ],
                    'cnae_actividad' => 'CNAE (actividad/descripción)',
                    'forma_juridica' => [
                        'label' => 'Forma jurídica',
                        'options' => array_column(LegalForm::cases(), 'value'),
                    ],
                    'empleador_email' => 'Email',
                    'empleador_telefono' => 'Teléfono',
                    'empleador_direccion' => 'Dirección completa',
                    'empleador_direccion_calle' => 'Dirección - Calle',
                    'empleador_direccion_numero' => 'Dirección - Número',
                    'empleador_direccion_piso_puerta' => 'Dirección - Piso/Puerta',
                    'empleador_direccion_codigo_postal' => 'Dirección - Código postal',
                    'empleador_direccion_municipio' => 'Dirección - Municipio',
                    'empleador_direccion_provincia' => 'Dirección - Provincia',
                    'representante_nombre' => 'Nombre del representante',
                    'representante_documento' => 'Documento del representante',
                    'representante_cargo' => 'Cargo del representante',
                ],
            ],
            'job' => [
                'label' => 'Expediente/Trabajo',
                'fields' => [
                    'expediente_codigo' => 'Código de expediente',
                    'expediente_titulo' => 'Título del expediente',
                    'campaña' => 'Campaña',
                    'tipo_solicitud' => [
                        'label' => 'Tipo de solicitud',
                        'options' => array_column(ApplicationType::cases(), 'value'),
                    ],
                    'puesto_trabajo' => 'Puesto de trabajo',
                    'fecha_inicio' => 'Fecha de inicio (dd/mm/yyyy)',
                    'fecha_inicio_dia' => 'Fecha inicio - Día',
                    'fecha_inicio_mes' => 'Fecha inicio - Mes',
                    'fecha_inicio_anio' => 'Fecha inicio - Año',
                    'fecha_fin' => 'Fecha de fin (dd/mm/yyyy)',
                    'fecha_fin_dia' => 'Fecha fin - Día',
                    'fecha_fin_mes' => 'Fecha fin - Mes',
                    'fecha_fin_anio' => 'Fecha fin - Año',
                    'salario' => 'Salario (formateado con EUR)',
                    'salario_numero' => 'Salario (solo número)',
                    'tipo_jornada' => [
                        'label' => 'Tipo de jornada',
                        'options' => array_map(fn(WorkingDayType $t) => $t->label(), WorkingDayType::cases()),
                    ],
                    'horas_semanales' => 'Horas semanales',
                    'periodo_prueba' => 'Período de prueba (con "días")',
                    'periodo_prueba_dias' => 'Período de prueba (solo número)',
                    'direccion_trabajo' => 'Dirección del trabajo completa',
                    'direccion_trabajo_calle' => 'Dirección trabajo - Calle',
                    'direccion_trabajo_numero' => 'Dirección trabajo - Número',
                    'direccion_trabajo_piso_puerta' => 'Dirección trabajo - Piso/Puerta',
                    'direccion_trabajo_codigo_postal' => 'Dirección trabajo - Código postal',
                    'direccion_trabajo_municipio' => 'Dirección trabajo - Municipio',
                    'direccion_trabajo_provincia' => 'Dirección trabajo - Provincia',
                ],
            ],
            'representative' => [
                'label' => 'Representante Legal',
                'fields' => [
                    'gestor_nombre' => 'Nombre del gestor',
                    'gestor_email' => 'Email del gestor',
                    'fecha_generacion' => 'Fecha de generación',
                    'hora_generacion' => 'Hora de generación',
                ],
            ],
        ];
    }

    /**
     * Almacena el archivo PDF subido
     * Guarda con nombre original en resources/pdf para que aparezca en Repositorio
     */
    protected function storeFile(UploadedFile $file, PdfTemplateType $type): string
    {
        $originalName = $file->getClientOriginalName();

        // Si ya existe un archivo con ese nombre, añadir sufijo único
        if (Storage::disk('pdf_templates')->exists($originalName)) {
            $name = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $originalName = $name . '_' . Str::random(6) . '.' . $extension;
        }

        return $file->storeAs('', $originalName, 'pdf_templates');
    }

    /**
     * Duplica una plantilla existente
     */
    public function duplicate(int $id): ?PdfTemplate
    {
        $original = $this->repository->findById($id);

        if (!$original) {
            return null;
        }

        // Copiar el archivo si existe
        $newStoragePath = null;
        if ($original->storage_path && Storage::disk('pdf_templates')->exists($original->storage_path)) {
            $extension = pathinfo($original->storage_path, PATHINFO_EXTENSION);
            $directory = pathinfo($original->storage_path, PATHINFO_DIRNAME);
            $newStoragePath = $directory . '/' . Str::uuid() . '.' . $extension;

            Storage::disk('pdf_templates')->copy($original->storage_path, $newStoragePath);
        }

        return $this->repository->create([
            'name' => $original->name . ' (Copia)',
            'description' => $original->description,
            'original_filename' => $original->original_filename,
            'storage_path' => $newStoragePath,
            'document_type' => $original->document_type->value,
            'application_type' => $original->application_type?->value,
            'is_default' => false, // Nunca duplicar como predeterminada
            'is_active' => $original->is_active,
            'field_mapping' => $original->field_mapping,
            'extracted_fields' => $original->extracted_fields,
            'total_fields' => $original->total_fields,
            'created_by' => auth()->id(),
        ]);
    }
}
