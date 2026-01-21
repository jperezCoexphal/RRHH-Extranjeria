<?php

namespace App\Models;

use App\Enums\ApplicationType;
use App\Enums\PdfTemplateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PdfTemplate extends Model
{
    use SoftDeletes;

    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

    // FILLABLE
    protected $fillable = [
        'name',
        'description',
        'original_filename',
        'storage_path',
        'document_type',
        'application_type',
        'is_default',
        'is_active',
        'field_mapping',
        'extracted_fields',
        'total_fields',
        'created_by',
    ];

    // CASTS
    protected $casts = [
        'document_type' => PdfTemplateType::class,
        'application_type' => ApplicationType::class,
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'field_mapping' => 'array',
        'extracted_fields' => 'array',
        'total_fields' => 'integer',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Usuario que creó la plantilla
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // SCOPES
    /*-----------------------------------------------------------------------------------*/

    /**
     * Filtra plantillas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtra plantillas predeterminadas
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Filtra por tipo de documento
     */
    public function scopeForDocumentType($query, PdfTemplateType $type)
    {
        return $query->where('document_type', $type->value);
    }

    /**
     * Filtra por tipo de solicitud
     */
    public function scopeForApplicationType($query, ApplicationType $applicationType)
    {
        return $query->where(function ($q) use ($applicationType) {
            $q->where('application_type', $applicationType->value)
              ->orWhereNull('application_type');
        });
    }

    /**
     * Obtiene la plantilla predeterminada para un tipo de documento y solicitud
     */
    public function scopeDefaultFor($query, PdfTemplateType $documentType, ?ApplicationType $applicationType = null)
    {
        $query->active()
              ->forDocumentType($documentType)
              ->where('is_default', true);

        if ($applicationType) {
            $query->forApplicationType($applicationType);
        }

        return $query->orderByRaw('CASE WHEN application_type IS NOT NULL THEN 0 ELSE 1 END');
    }

    // ACCESSORS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Retorna la URL completa del archivo PDF
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->storage_path) {
            return null;
        }

        return Storage::disk('pdf_templates')->url($this->storage_path);
    }

    /**
     * Retorna la ruta absoluta del archivo PDF
     */
    public function getFilePathAttribute(): ?string
    {
        if (!$this->storage_path) {
            return null;
        }

        return Storage::disk('pdf_templates')->path($this->storage_path);
    }

    /**
     * Retorna el tamaño del archivo en formato legible
     */
    public function getFileSizeAttribute(): ?string
    {
        if (!$this->storage_path || !Storage::disk('pdf_templates')->exists($this->storage_path)) {
            return null;
        }

        $bytes = Storage::disk('pdf_templates')->size($this->storage_path);

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return round($bytes / 1024, 2) . ' KB';
    }

    /**
     * Verifica si el archivo PDF existe
     */
    public function getFileExistsAttribute(): bool
    {
        return $this->storage_path && Storage::disk('pdf_templates')->exists($this->storage_path);
    }

    /**
     * Retorna el número de campos mapeados
     */
    public function getMappedFieldsCountAttribute(): int
    {
        if (!$this->field_mapping) {
            return 0;
        }

        return count(array_filter($this->field_mapping, fn($mapping) => !empty($mapping['field'])));
    }

    /**
     * Retorna el porcentaje de campos mapeados
     */
    public function getMappingProgressAttribute(): int
    {
        if ($this->total_fields === 0) {
            return 0;
        }

        return (int) round(($this->mapped_fields_count / $this->total_fields) * 100);
    }

    // METHODS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Verifica si la plantilla tiene un mapeo completo
     */
    public function hasCompleteMapping(): bool
    {
        return $this->mapping_progress === 100;
    }

    /**
     * Obtiene los campos que aún no han sido mapeados
     */
    public function getUnmappedFields(): array
    {
        if (!$this->extracted_fields || !$this->field_mapping) {
            return $this->extracted_fields ?? [];
        }

        $mappedFieldNames = array_keys(array_filter(
            $this->field_mapping,
            fn($mapping) => !empty($mapping['field'])
        ));

        return array_filter(
            $this->extracted_fields,
            fn($field) => !in_array($field['name'], $mappedFieldNames)
        );
    }

    /**
     * Establece esta plantilla como predeterminada, quitando el flag a las demás
     */
    public function setAsDefault(): bool
    {
        // Quitar el flag de las demás plantillas del mismo tipo
        static::where('document_type', $this->document_type)
            ->where('id', '!=', $this->id)
            ->when($this->application_type, function ($query) {
                $query->where(function ($q) {
                    $q->where('application_type', $this->application_type)
                      ->orWhereNull('application_type');
                });
            })
            ->update(['is_default' => false]);

        // Establecer esta como predeterminada
        $this->is_default = true;
        return $this->save();
    }
}
