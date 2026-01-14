<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'postal_code',
        'street_name',
        'number',
        'floor_door',
        'country_id',
        'province_id',
        'municipality_id',
    ];

        // TIMESTAMPS
    public $timestamps = false;

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Relación polimórfica con la entidad propietaria
     * Puede ser Employer, Foreigner, etc.
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    // ACCESSORS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Retorna la dirección formateada como string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_name,
            $this->number ? "nº {$this->number}" : null,
            $this->floor_door,
            $this->postal_code,
            $this->municipality?->municipality_name,
            $this->province?->province_name,
        ]);

        return implode(', ', $parts);
    }
}
