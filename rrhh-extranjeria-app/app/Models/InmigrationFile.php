<?php

namespace App\Models;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\WorkingDayType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class InmigrationFile extends Model
{
    use SoftDeletes;

    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // TABLE
    protected $table = 'inmigration_files';

        // FILLABLE
    protected $fillable = [
        'campaign',
        'file_code',
        'file_title',
        'application_type',
        'status',
        'job_title',
        'start_date',
        'end_date',
        'salary',
        'working_day_type',
        'working_hours',
        'probation_period',
        'editor_id',
        'employer_id',
        'foreigner_id',
    ];

        // CASTS
    protected $casts = [
        'application_type' => ApplicationType::class,
        'status' => ImmigrationFileStatus::class,
        'working_day_type' => WorkingDayType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
        'working_hours' => 'float',
        'probation_period' => 'integer',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Usuario editor/gestor del expediente
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    /**
     * Empleador asociado al expediente
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }

    /**
     * Trabajador extranjero del expediente
     */
    public function foreigner(): BelongsTo
    {
        return $this->belongsTo(Foreigner::class, 'foreigner_id');
    }

    /**
     * Requisitos del expediente (checklist)
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(FileRequirement::class, 'inmigration_file_id');
    }

    /**
     * Dirección asociada al expediente (lugar de trabajo)
     */
    public function workAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    // ACCESSORS & MUTATORS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Verifica si todos los requisitos obligatorios están completados
     */
    protected function allMandatoryRequirementsCompleted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requirements()
                ->where('is_mandatory', true)
                ->where('is_completed', false)
                ->doesntExist(),
        );
    }

    /**
     * Cuenta los requisitos pendientes
     */
    protected function pendingRequirementsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requirements()
                ->where('is_completed', false)
                ->count(),
        );
    }

    /**
     * Verifica si el expediente es editable según su estado
     */
    protected function isEditable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->isEditable(),
        );
    }

    // SCOPES
    /*-----------------------------------------------------------------------------------*/

    /**
     * Filtra por campaña
     */
    public function scopeByCampaign($query, string $campaign)
    {
        return $query->where('campaign', $campaign);
    }

    /**
     * Filtra por estado
     */
    public function scopeByStatus($query, ImmigrationFileStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filtra expedientes con requisitos vencidos
     */
    public function scopeWithOverdueRequirements($query)
    {
        return $query->whereHas('requirements', function ($q) {
            $q->where('is_completed', false)
              ->whereNotNull('due_date')
              ->where('due_date', '<', now());
        });
    }
}
