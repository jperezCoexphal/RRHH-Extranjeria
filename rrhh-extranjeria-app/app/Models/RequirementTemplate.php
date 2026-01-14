<?php

namespace App\Models;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\TargetEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequirementTemplate extends Model
{
    use SoftDeletes;

    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'name',
        'description',
        'target_entity',
        'application_type',
        'trigger_status',
        'days_to_expire',
        'is_mandatory',
    ];

        // CASTS
    protected $casts = [
        'target_entity' => TargetEntity::class,
        'application_type' => ApplicationType::class,
        'trigger_status' => ImmigrationFileStatus::class,
        'days_to_expire' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Instancias de requisitos generados desde esta plantilla
     */
    public function fileRequirements(): HasMany
    {
        return $this->hasMany(FileRequirement::class, 'requirement_template_id');
    }

    // SCOPES
    /*-----------------------------------------------------------------------------------*/

    /**
     * Filtra plantillas por tipo de solicitud
     */
    public function scopeForApplicationType($query, ApplicationType $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->where('application_type', $type)
              ->orWhereNull('application_type');
        });
    }

    /**
     * Filtra plantillas que se activan con un estado específico
     */
    public function scopeTriggeredByStatus($query, ImmigrationFileStatus $status)
    {
        return $query->where(function ($q) use ($status) {
            $q->where('trigger_status', $status)
              ->orWhereNull('trigger_status');
        });
    }

    /**
     * Filtra solo plantillas obligatorias
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Filtra por entidad objetivo
     */
    public function scopeForEntity($query, TargetEntity $entity)
    {
        return $query->where('target_entity', $entity);
    }
}
