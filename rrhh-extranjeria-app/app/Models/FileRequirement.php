<?php

namespace App\Models;

use App\Enums\TargetEntity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRequirement extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'name',
        'description',
        'target_entity',
        'observation',
        'due_date',
        'is_completed',
        'is_mandatory',
        'completed_at',
        'notified_at',
        'inmigration_file_id',
        'requirement_template_id',
    ];

        // CASTS
    protected $casts = [
        'target_entity' => TargetEntity::class,
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'is_mandatory' => 'boolean',
        'completed_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Expediente al que pertenece este requisito
     */
    public function inmigrationFile(): BelongsTo
    {
        return $this->belongsTo(InmigrationFile::class, 'inmigration_file_id');
    }

    /**
     * Plantilla de la que se generó este requisito
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(RequirementTemplate::class, 'requirement_template_id');
    }

    // ACCESSORS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Indica si el requisito está vencido
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => ! $this->is_completed
                && $this->due_date !== null
                && $this->due_date->isPast(),
        );
    }

    /**
     * Calcula los días restantes hasta el vencimiento
     */
    protected function daysUntilDue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->due_date?->diffInDays(now(), absolute: false),
        );
    }

    // MUTATORS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Marca el requisito como completado
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Marca el requisito como notificado
     */
    public function markAsNotified(): bool
    {
        return $this->update([
            'notified_at' => now(),
        ]);
    }

    // SCOPES
    /*-----------------------------------------------------------------------------------*/

    /**
     * Filtra requisitos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Filtra requisitos vencidos
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    /**
     * Filtra requisitos obligatorios
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
